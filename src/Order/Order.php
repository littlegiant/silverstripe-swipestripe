<?php

namespace SwipeStripe\Order;

use Money\Money;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SwipeStripe\Order\OrderItem\OrderItem;
use SwipeStripe\Price\DBPrice;
use SwipeStripe\Purchasable\PurchasableAddOn;
use SwipeStripe\Purchasable\PurchasableInterface;
use SwipeStripe\SupportedCurrencies\SupportedCurrenciesInterface;

/**
 * Class Order
 * @package SwipeStripe\Order
 * @property bool $IsCart
 * @property bool $CartLocked
 * @property HasManyList|Payment[] Payments()
 * @method HasManyList|OrderItem[] OrderItems()
 * @method ManyManyList|OrderAddOn[] OrderAddOns()
 */
class Order extends DataObject
{
    const SESSION_CART_ID = self::class . '.ActiveCartID';

    /**
     * @var string
     */
    private static $table_name = 'SwipeStripe_Order';

    /**
     * @var array
     */
    private static $db = [
        'IsCart'     => DBBoolean::class,
        'CartLocked' => DBBoolean::class,
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'OrderItems' => OrderItem::class,
        'Payments' => Payment::class,
    ];

    /**
     * @var array
     */
    private static $many_many = [
        'OrderAddOns' => OrderAddOn::class,
    ];

    /**
     * @var array
     */
    private static $dependencies = [
        'request'             => '%$' . HTTPRequest::class,
        'supportedCurrencies' => '%$' . SupportedCurrenciesInterface::class,
    ];

    /**
     * @var HTTPRequest
     */
    public $request;

    /**
     * @var SupportedCurrenciesInterface
     */
    public $supportedCurrencies;

    /**
     * @return static
     */
    public function getActiveCart(): self
    {
        $session = $this->request->getSession();
        $cartId = intval($session->get(static::SESSION_CART_ID));

        if ($cartId > 0) {
            $cartObj = self::get_by_id($cartId);

            if ($cartObj !== null && $cartObj->IsCart) {
                return $cartObj;
            }
        }

        $cartObj = static::create();
        $cartObj->IsCart = true;
        $cartObj->write();

        $session->set(static::SESSION_CART_ID, $cartObj->ID)
            ->save($this->request);
        return $cartObj;
    }

    /**
     *
     */
    public function clearActiveCart(): void
    {
        $this->request->getSession()->clear(static::SESSION_CART_ID);
    }

    /**
     * @return DBPrice
     */
    public function getTotal(): DBPrice
    {
        $subTotal = $this->getSubTotal()->getMoney();
        $runningTotal = $subTotal;

        /** @var OrderAddOn $addOn */
        foreach ($this->OrderAddOns()->sort('Priority') as $addOn) {
            $addOnAmount = $addOn->getAmount($this, $subTotal, $runningTotal);
            $runningTotal = $runningTotal->add($addOnAmount);
        }

        return DBPrice::create_field(DBPrice::class, $runningTotal);
    }

    /**
     * @return DBPrice
     */
    public function getSubTotal(): DBPrice
    {
        $money = new Money(0, $this->supportedCurrencies->getDefaultCurrency());

        foreach ($this->OrderItems() as $item) {
            $itemAmount = $item->getPrice()->multiply($item->getQuantity());

            /*
             * If money is initial zero, we use item amount as base - this avoids assuming $itemAmount is in
             * default currency. $money(0)->add($itemAmount) would throw for non-default currency even if all items have
             * same currency.
             */
            $money = $money->isZero()
                ? $itemAmount
                : $money->add($itemAmount);
        }

        return DBPrice::create_field(DBPrice::class, $money);
    }

    /**
     * @param PurchasableInterface $item
     * @param int $quantity
     */
    public function setPurchasableQuantity(PurchasableInterface $item, int $quantity = 1): void
    {
        $orderItem = $this->getOrderItem($item);

        if ($quantity <= 0) {
            if ($orderItem->isInDB()) {
                $orderItem->delete();
            }

            return;
        }

        if ($orderItem->getQuantity() !== $quantity) {
            $orderItem->Quantity = $quantity;
            $orderItem->write();
        }
    }

    /**
     * @param PurchasableInterface $item
     * @param bool $createIfMissing
     * @return null|OrderItem
     */
    protected function getOrderItem(PurchasableInterface $item, bool $createIfMissing = true): ?OrderItem
    {
        $match = $this->OrderItems()->filter([
            OrderItem::PURCHASABLE_CLASS => $item->ClassName,
            OrderItem::PURCHASABLE_ID    => $item->ID,
        ])->first();

        if ($match !== null || !$createIfMissing) {
            return $match;
        }

        $orderItem = OrderItem::create();
        $orderItem->setPurchasable($item);
        $orderItem->Quantity = 0;
        $orderItem->OrderID = $this->ID;

        return $orderItem;
    }

    /**
     * @param OrderAddOn $addOn
     */
    public function attachOrderAddOn(OrderAddOn $addOn): void
    {
        $this->OrderAddOns()->add($addOn);
    }

    /**
     * @param OrderAddOn $addOn
     */
    public function detachOrderAddOn(OrderAddOn $addOn): void
    {
        $this->OrderAddOns()->remove($addOn);
    }

    /**
     * @param PurchasableInterface $item
     * @param PurchasableAddOn $addOn
     * @throws \Exception
     */
    public function attachPurchasableAddOn(PurchasableInterface $item, PurchasableAddOn $addOn): void
    {
        $orderItem = $this->getOrderItem($item, false);

        if ($orderItem !== null) {
            $orderItem->PurchasableAddOns()->add($item);
        }
    }

    /**
     * @param PurchasableInterface $item
     * @param PurchasableAddOn $addOn
     */
    public function detachPurchasableAddOn(PurchasableInterface $item, PurchasableAddOn $addOn): void
    {
        $orderItem = $this->getOrderItem($item, false);

        if ($orderItem !== null) {
            $orderItem->PurchasableAddOns()->remove($item);
        }
    }
}

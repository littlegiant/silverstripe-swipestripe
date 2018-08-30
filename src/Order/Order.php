<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use Money\Money;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\Omnipay\Service\ServiceResponse;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SwipeStripe\Customer\Customer;
use SwipeStripe\Order\OrderItem\OrderItem;
use SwipeStripe\Order\OrderItem\OrderItemAddOn;
use SwipeStripe\Price\DBPrice;
use SwipeStripe\Purchasable\PurchasableInterface;

/**
 * Class Order
 * @package SwipeStripe\Order
 * @property bool $IsCart
 * @property bool $CartLocked
 * @property string $GuestToken
 * @property int $CustomerID
 * @method null|Customer Customer()
 * @method HasManyList|OrderItem[] OrderItems()
 * @method HasManyList|OrderAddOn[] OrderAddOns()
 */
class Order extends DataObject
{
    use Payable;

    const GUEST_TOKEN_BYTES = 16;

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
        'GuestToken' => DBVarchar::class,
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Customer' => Customer::class,
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'OrderAddOns' => OrderAddOn::class,
        'OrderItems'  => OrderItem::class,
    ];

    /**
     * @inheritDoc
     */
    public function populateDefaults()
    {
        parent::populateDefaults();
        $this->GuestToken = bin2hex(random_bytes(static::GUEST_TOKEN_BYTES));
        return $this;
    }

    /**
     * @param bool $applyOrderAddOns
     * @param bool $applyOrderItemAddOns
     * @return DBPrice
     */
    public function Total(bool $applyOrderAddOns = true, bool $applyOrderItemAddOns = true): DBPrice
    {
        $subTotal = $this->SubTotal($applyOrderItemAddOns)->getMoney();
        $runningTotal = $subTotal;

        if ($applyOrderAddOns) {
            /** @var OrderAddOn $addOn */
            foreach ($this->OrderAddOns() as $addOn) {
                $runningTotal = $runningTotal->add($addOn->getAmount()->getMoney());
            }
        }

        return DBPrice::create_field(DBPrice::INJECTOR_SPEC, $runningTotal);
    }

    /**
     * @param bool $applyItemAddOns
     * @return DBPrice
     */
    public function SubTotal(bool $applyItemAddOns = true): DBPrice
    {
        $money = new Money(0, $this->supportedCurrencies->getDefaultCurrency());

        foreach ($this->OrderItems() as $item) {
            $itemAmount = $applyItemAddOns
                ? $item->getTotal()->getMoney()
                : $item->getSubTotal()->getMoney();

            /*
             * If money is initial zero, we use item amount as base - this avoids assuming $itemAmount is in
             * default currency. $money(0)->add($itemAmount) would throw for non-default currency even if all items have
             * same currency.
             */
            $money = $money->isZero()
                ? $itemAmount
                : $money->add($itemAmount);
        }

        return DBPrice::create_field(DBPrice::INJECTOR_SPEC, $money);
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
    public function getOrderItem(PurchasableInterface $item, bool $createIfMissing = true): ?OrderItem
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
     * @param PurchasableInterface $item
     * @param OrderItemAddOn $addOn
     */
    public function attachPurchasableAddOn(PurchasableInterface $item, OrderItemAddOn $addOn): void
    {
        $orderItem = $this->getOrderItem($item, false);

        if ($orderItem !== null) {
            $orderItem->OrderItemAddOns()->add($addOn);
        }
    }

    /**
     * @param PurchasableInterface $item
     * @param OrderItemAddOn $addOn
     */
    public function detachPurchasableAddOn(PurchasableInterface $item, OrderItemAddOn $addOn): void
    {
        $orderItem = $this->getOrderItem($item, false);

        if ($orderItem !== null) {
            $orderItem->OrderItemAddOns()->remove($addOn);
        }
    }

    /**
     * @param null|Member $member
     * @param null|string $guestToken
     * @return bool
     */
    public function canViewOrderPage(?Member $member = null, ?string $guestToken = null): bool
    {
        if ($this->IsCart) {
            // No one should be able to view carts as an order
            return false;
        }

        $member = $member ?? Security::getCurrentUser();

        // Allow valid guest token if the customer is a guest
        return ($guestToken === $this->GuestToken && $this->Customer()->IsGuest()) ||
            // Allow if logged in and member owns the customer object
            ($member !== null && !$this->Customer()->IsGuest() && intval($this->Customer()->MemberID) === intval($member->ID)) ||
            // Allow admins
            Permission::check('ADMIN', 'any', $member);
    }

    /**
     * @param Payment $payment
     * @param ServiceResponse $response
     */
    public function paymentCaptured(Payment $payment, ServiceResponse $response): void
    {
        // TODO
    }
}

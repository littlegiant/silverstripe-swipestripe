<?php

namespace SwipeStripe\Order\OrderItem;

use Omnipay\Common\ItemInterface;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\HasManyList;
use SwipeStripe\Order\Order;
use SwipeStripe\Price\DBPrice;
use SwipeStripe\Purchasable\PurchasableInterface;

/**
 * Class OrderItem
 * @package SwipeStripe\Order\OrderItem
 * @property string $Name
 * @property string $Description
 * @property DBPrice $Price
 * @property int $Quantity
 * @property int $OrderID
 * @property int PurchasableID
 * @property string PurchasableClass
 * @method Order|null Order()
 * @method DataObject|PurchasableInterface Purchasable()
 * @method HasManyList|OrderItemAddOn[] OrderItemAddOns()
 */
class OrderItem extends DataObject implements ItemInterface
{
    const PURCHASABLE_CLASS = 'PurchasableClass';
    const PURCHASABLE_ID = 'PurchasableID';

    /**
     * @var string
     */
    private static $table_name = 'SwipeStripe_Order_OrderItem';

    /**
     * @var array
     */
    private static $db = [
        'Quantity' => DBInt::class,
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Order'       => Order::class,
        'Purchasable' => DataObject::class,
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'OrderItemAddOns' => OrderItemAddOn::class,
    ];

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->Purchasable() ? $this->Purchasable()->getTitle() : '';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->Purchasable() ? $this->Purchasable()->getDescription() : '';
    }

    /**
     * @param bool $applyAddOns
     * @return DBPrice
     */
    public function SubTotal(bool $applyAddOns = true): DBPrice
    {
        $money = $this->getPrice()->getMoney()->multiply($this->getQuantity());

        if ($applyAddOns) {
            foreach ($this->OrderItemAddOns() as $addOn) {
                $money = $money->add($addOn->getAmount());
            }
        }

        return DBPrice::create_field(DBPrice::class, $money);
    }

    /**
     * @return DBPrice
     */
    public function getPrice(): DBPrice
    {
        return $this->Purchasable()->getPrice();
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return intval($this->getField('Quantity'));
    }

    /**
     * @param PurchasableInterface $purchasable
     * @return $this
     */
    public function setPurchasable(PurchasableInterface $purchasable): self
    {
        $this->PurchasableClass = $purchasable->ClassName;
        $this->PurchasableID = $purchasable->ID;

        return $this;
    }
}

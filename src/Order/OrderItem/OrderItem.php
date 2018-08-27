<?php

namespace SwipeStripe\Order\OrderItem;

use Money\Money;
use Omnipay\Common\ItemInterface;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\ManyManyThroughList;
use SilverStripe\ORM\SS_List;
use SwipeStripe\Order\Order;
use SwipeStripe\Purchasable\PurchasableAddOnInterface;
use SwipeStripe\Purchasable\PurchasableInterface;

/**
 * Class OrderItem
 * @package SwipeStripe\Order\OrderItem
 * @property int $Quantity
 * @property int $OrderID
 * @property int PurchasableID
 * @property string PurchasableClass
 * @method Order|null Order()
 * @method DataObject|PurchasableInterface Purchasable()
 * @method ManyManyThroughList|PurchasableAddOnInterface[] PurchasableAddOns()
 */
class OrderItem extends DataObject implements ItemInterface
{
    /**
     * @var string
     */
    private static $table_name = 'SwipeStripe_OrderItem';

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
    private static $many_many = [
        'PurchasableAddOns' => [
            'through' => OrderItemPurchasableAddOnMapping::class,
            'from'    => OrderItemPurchasableAddOnMapping::ORDER_ITEM,
            'to'      => OrderItemPurchasableAddOnMapping::PURCHASABLE_ADD_ON,
        ],
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
        return $this->Purchasable()->getTitle();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->Purchasable()->getDescription();
    }

    /**
     * @return Money
     */
    public function getPrice(): Money
    {
        $item = $this->Purchasable();
        $quantity = $this->getQuantity();
        $basePrice = $this->Purchasable()->getPrice()->getMoney();
        $runningPrice = $basePrice;

        foreach ($this->SortedPurchasableAddOns() as $addOn) {
            $addOnAmount = $addOn->getAmount($item, $quantity, $basePrice, $runningPrice);
            $runningPrice = $runningPrice->add($addOnAmount);
        }

        return $runningPrice;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return intval($this->getField('Quantity'));
    }

    /**
     * @return SS_List|PurchasableAddOnInterface[]
     */
    public function SortedPurchasableAddOns(): SS_List
    {
        $addOns = $this->PurchasableAddOns()->toArray();
        usort($addOns, PurchasableAddOnInterface::COMPARATOR_FUNCTION);

        return ArrayList::create($addOns);
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

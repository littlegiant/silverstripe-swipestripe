<?php
declare(strict_types=1);

namespace SwipeStripe\Order\OrderItem;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\Order\Order;
use SwipeStripe\Price\DBPrice;
use SwipeStripe\Purchasable\PurchasableInterface;

/**
 * Class OrderItem
 * @package SwipeStripe\Order\OrderItem
 * @property string $Description
 * @property DBPrice $Price
 * @property int $Quantity
 * @property int $OrderID
 * @property int PurchasableID
 * @property DBPrice $SubTotal
 * @property DBPrice $Total
 * @property string $PurchasableClass
 * @property int $PurchasableLockedVersion
 * @method Order|null Order()
 * @method HasManyList|OrderItemAddOn[] OrderItemAddOns()
 */
class OrderItem extends DataObject
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
        'Quantity'                 => DBInt::class,
        'PurchasableLockedVersion' => DBInt::class,
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
     * @var array
     */
    private static $summary_fields = [
        'Title'    => 'Title',
        'Quantity' => 'Quantity',
    ];

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->Purchasable() ? $this->Purchasable()->getTitle() : '';
    }

    /**
     * @return DataObject|PurchasableInterface
     */
    public function Purchasable(): PurchasableInterface
    {
        return !$this->IsMutable() && !empty($this->PurchasableLockedVersion)
            ? Versioned::get_version($this->PurchasableClass, $this->PurchasableID, $this->PurchasableLockedVersion)
            /** @see OrderItem::$has_one */
            : $this->getComponent('Purchasable');
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->Purchasable() ? $this->Purchasable()->getDescription() : '';
    }

    /**
     * Subtotal with add-ons.
     * @return DBPrice
     */
    public function getTotal(): DBPrice
    {
        $money = $this->getSubTotal()->getMoney();

        foreach ($this->OrderItemAddOns() as $addOn) {
            $money = $money->add($addOn->getAmount()->getMoney());
        }

        return DBPrice::create_field(DBPrice::INJECTOR_SPEC, $money);
    }

    /**
     * Unit price x quantity.
     * @return DBPrice
     */
    public function getSubTotal(): DBPrice
    {
        $money = $this->getPrice()->getMoney()->multiply($this->getQuantity());
        return DBPrice::create_field(DBPrice::INJECTOR_SPEC, $money);
    }

    /**
     * Unit price.
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
        if (!$this->IsMutable()) {
            throw new \BadMethodCallException("Cannot set Purchasable on locked OrderItem {$this->ID}.");
        }

        $this->PurchasableClass = $purchasable->ClassName;
        $this->PurchasableID = $purchasable->ID;

        return $this;
    }

    /**
     * @return bool
     */
    public function IsMutable(): bool
    {
        return $this->Order()->IsMutable();
    }

    /**
     * @param int $quantity
     * @param bool $writeImmediately
     * @return OrderItem
     */
    public function setQuantity(int $quantity, bool $writeImmediately = true): self
    {
        if (!$this->IsMutable()) {
            throw new \BadMethodCallException("Cannot set quantity on locked OrderItem {$this->ID}.");
        }

        if ($quantity > 0 && $this->getQuantity() !== $quantity || !$this->isInDB()) {
            $this->setField('Quantity', $quantity);

            if ($writeImmediately) {
                $this->write();
            }
        } elseif ($quantity <= 0) {
            $this->setField('Quantity', 0);

            if ($this->isInDB()) {
                $this->delete();
            }
        }

        return $this;
    }
}

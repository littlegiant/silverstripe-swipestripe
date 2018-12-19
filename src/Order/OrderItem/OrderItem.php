<?php
declare(strict_types=1);

namespace SwipeStripe\Order\OrderItem;

use Money\Money;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\Relation;
use SilverStripe\ORM\UnsavedRelationList;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\CMSHelper;
use SwipeStripe\Forms\Fields\HasOneButtonField;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\OrderLockedException;
use SwipeStripe\Order\PurchasableInterface;
use SwipeStripe\Price\DBPrice;
use SwipeStripe\Price\PriceField;

/**
 * Class OrderItem
 * @package SwipeStripe\Order\OrderItem
 * @property string $Description
 * @property DBPrice $BasePrice
 * @property int $Quantity
 * @property int $OrderID
 * @property string $PurchasableClass
 * @property int $PurchasableID
 * @property-read DBPrice $SubTotal
 * @property-read DBPrice $Total
 * @method Order Order()
 */
class OrderItem extends DataObject
{
    use CMSHelper;

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
        'Quantity' => 'Int',
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
    private static $extensions = [
        Versioned::class => Versioned::class . '.versioned',
    ];

    /**
     * @var array
     */
    private static $cascade_duplicates = [
        'OrderItemAddOns',
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Title'       => 'Title',
        'Quantity'    => 'Quantity',
        'Total.Value' => 'Amount',
    ];

    /**
     * @var array
     */
    private static $searchable_fields = [
        'Purchasable.Title',
        'Quantity',
    ];

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->Purchasable() ? $this->Purchasable()->getTitle() : '';
    }

    /**
     * @return null|DataObject|PurchasableInterface
     */
    public function Purchasable(): ?PurchasableInterface
    {
        $this->setSourceQueryParams($this->Order()->getVersionedQueryParams());
        return $this->getComponent('Purchasable');
    }

    /**
     * @return HasManyList|UnsavedRelationList|OrderItemAddOn[]
     */
    public function OrderItemAddOns(): Relation
    {
        $this->setSourceQueryParams($this->Order()->getVersionedQueryParams());
        return $this->getComponents('OrderItemAddOns');
    }

    /**
     * @return string|DBHTMLText
     */
    public function getDescription()
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

        if ($this->getQuantity() > 0) {
            foreach ($this->OrderItemAddOns() as $addOn) {
                if ($addOn->isActive()) {
                    $money = $money->add($addOn->Amount->getMoney());
                }
            }
        }

        if ($money->isNegative()) {
            $money = new Money(0, $money->getCurrency());
        }

        return DBPrice::create_field(DBPrice::INJECTOR_SPEC, $money);
    }

    /**
     * Unit price x quantity.
     * @return DBPrice
     */
    public function getSubTotal(): DBPrice
    {
        $money = $this->getBasePrice()->getMoney()->multiply($this->getQuantity());
        return DBPrice::create_field(DBPrice::INJECTOR_SPEC, $money);
    }

    /**
     * Unit price.
     * @return DBPrice
     */
    public function getBasePrice(): DBPrice
    {
        return $this->Purchasable() ? $this->Purchasable()->getBasePrice() : DBPrice::create();
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
            throw new OrderLockedException($this);
        }

        $this->setComponent('Purchasable', $purchasable);
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
            throw new OrderLockedException($this);
        }

        $this->setField('Quantity', max($quantity, 0));

        if ($writeImmediately && !$this->isInDB() || $this->isChanged('Quantity', static::CHANGE_VALUE)) {
            $this->write();
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function canView($member = null)
    {
        return $this->Order()->canView($member);
    }

    /**
     * @inheritDoc
     */
    public function canEdit($member = null)
    {
        return $this->IsMutable() && $this->Order()->canEdit($member);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName([
                'OrderID',
            ]);

            $fields->insertBefore('Quantity', ReadonlyField::create('Title'));
            $fields->insertAfter('Title', ReadonlyField::create('Description'));
            $fields->insertAfter('Description', PriceField::create('Price'));
            $fields->insertBefore('Quantity', HasOneButtonField::create($this, 'Purchasable'));

            $this->addViewButtonToGridFields($fields);
        });

        return parent::getCMSFields();
    }
}

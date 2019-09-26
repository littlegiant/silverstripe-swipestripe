<?php
declare(strict_types=1);

namespace SwipeStripe\Order\OrderItem;

use Exception;
use Money\Money;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\Relation;
use SilverStripe\ORM\UnsavedRelationList;
use SilverStripe\ORM\ValidationException;
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
 * @property string       $Description
 * @property DBPrice      $BasePrice
 * @property int          $Quantity
 * @property int          $OrderID
 * @property string       $PurchasableClass
 * @property int          $PurchasableID
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
        'Quantity'    => 'Int',
        'Title'       => 'Varchar',
        'Description' => 'Text',
        'BasePrice'   => 'Price',
        'Total'       => 'Price',
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
     * @return mixed|string
     * @throws Exception
     */
    public function getTitle()
    {
        $title = $this->getField('Title') ?: $this->generateTitle();
        $this->setField('Title', $title);
        return $title;
    }

    /**
     * Generate title from purchasable record
     *
     * @return string
     * @throws Exception
     */
    protected function generateTitle(): string
    {
        // Get uncached title from purchasable record
        $purchasable = $this->Purchasable();
        if ($purchasable && $purchasable->exists()) {
            return $purchasable->getTitle();
        }

        // No title
        return '';
    }

    /**
     * @return null|DataObject|PurchasableInterface
     * @throws Exception
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
     * @throws Exception
     */
    public function getDescription()
    {
        $description = $this->getField('Description') ?: $this->generateDescription();
        $this->setField('Description', $description);
        return $description;
    }

    /**
     * Generate description from purchasable record
     *
     * @return DBHTMLText|string
     * @throws Exception
     */
    protected function generateDescription()
    {
        // Get uncached title from purchasable record
        $purchasable = $this->Purchasable();
        if ($purchasable && $purchasable->exists()) {
            return $purchasable->getDescription();
        }

        // No description
        return '';
    }

    /**
     * Subtotal with add-ons.
     * @return DBPrice
     * @throws Exception
     */
    public function getTotal(): DBPrice
    {
        /** @var DBPrice $total */
        $total = $this->getField('Total');
        if (!$total->exists()) {
            $total->setValue($this->generateTotal());
        }
        return $total;
    }

    /**
     * Generate total
     *
     * @return Money
     * @throws Exception
     */
    protected function generateTotal(): Money
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
        return $money;
    }

    /**
     * Unit price x quantity.
     *
     * @return DBPrice
     * @throws Exception
     */
    public function getSubTotal(): DBPrice
    {
        $money = $this->getBasePrice()->getMoney()->multiply($this->getQuantity());
        return DBPrice::create_field(DBPrice::INJECTOR_SPEC, $money);
    }

    /**
     * Unit price.
     * @return DBPrice
     * @throws Exception
     */
    public function getBasePrice(): DBPrice
    {
        /** @var DBPrice $basePrice */
        $basePrice = $this->getField('BasePrice');
        if (!$basePrice->exists()) {
            $price = $this->generateBasePrice();
            if ($price) {
                $basePrice->setValue($price);
            }
        }
        return $basePrice;
    }

    /**
     * Generate base price from purchasable record
     *
     * @return DBPrice
     * @throws Exception
     */
    protected function generateBasePrice()
    {
        // Get base price from purchasable record
        $purchasable = $this->Purchasable();
        if ($purchasable && $purchasable->exists()) {
            return $purchasable->getBasePrice();
        }
        return null;
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
     * @param int  $quantity
     * @param bool $writeImmediately
     * @return OrderItem
     * @throws ValidationException
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

    /**
     * @throws Exception
     */
    protected function onBeforeWrite()
    {
        // Invalidate cached columns
        $this->setField('Title', null);
        $this->setField('Description', null);
        $this->setField('BasePriceCurrency', null);
        $this->setField('BasePriceAmount', null);
        $this->setField('TotalCurrency', null);
        $this->setField('TotalAmount', null);

        // Refresh cached columns
        $this->getTitle();
        $this->getDescription();
        $this->getBasePrice();
        $this->getTotal();

        parent::onBeforeWrite();
    }
}

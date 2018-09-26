<?php
declare(strict_types=1);

namespace SwipeStripe\Order\OrderItem;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\Constants\AddOnPriority;
use SwipeStripe\Price\DBPrice;
use SwipeStripe\Price\PriceField;

/**
 * Add on applied to on order item on a purchase. The add-on is applied once to the item's subtotal (unit price x quantity),
 * not to the unit price (i.e. add-on is not applied $quantity times).
 * @package SwipeStripe\Order\OrderItem
 * @property bool $ApplyPerUnit
 * @property string $Type The type of add-on this is.
 * @property string $Title
 * @property int $Priority
 * @property DBPrice $BaseAmount
 * @property DBPrice $Amount
 * @property int $OrderItemID
 * @method null|OrderItem OrderItem()
 * @mixin Versioned
 */
class OrderItemAddOn extends DataObject
{
    /**
     * @var string
     */
    private static $table_name = 'SwipeStripe_Order_OrderItemAddOn';

    /**
     * @var array
     */
    private static $db = [
        'Type'         => DBVarchar::class,
        'Priority'     => DBInt::class,
        'Title'        => DBVarchar::class,
        'ApplyPerUnit' => DBBoolean::class,
        'BaseAmount'   => DBPrice::class,
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'OrderItem' => OrderItem::class,
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
    private static $defaults = [
        'Priority' => AddOnPriority::NORMAL,
    ];

    /**
     * @var array
     */
    private static $searchable_fields = [
        'Title',
        'ApplyPerUnit',
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Title'             => 'Title',
        'Amount.Value'      => 'Amount',
        'ApplyPerUnit.Nice' => 'Apply Per Unit',
    ];

    /**
     * @var string
     */
    private static $default_sort = 'Priority ASC';

    /**
     * @inheritDoc
     */
    public function getAmount(): DBPrice
    {
        $baseAmount = $this->BaseAmount;

        return $this->ApplyPerUnit
            ? DBPrice::create_field(DBPrice::INJECTOR_SPEC, $baseAmount->getMoney()->multiply($this->OrderItem()->getQuantity()))
            : $baseAmount;
    }

    /**
     * @inheritdoc
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName([
                'Type',
                'Priority',
                'OrderItemID',
            ]);

            $fields->insertAfter('BaseAmount', PriceField::create('AppliedAmount')->setValue($this->Amount));
        });

        return parent::getCMSFields();
    }

    /**
     * @inheritDoc
     */
    public function canView($member = null)
    {
        return $this->OrderItem()->canView($member);
    }

    /**
     * @inheritDoc
     */
    public function canEdit($member = null)
    {
        return $this->OrderItem()->canEdit($member);
    }
}

<?php
declare(strict_types=1);

namespace SwipeStripe\Order\OrderItem;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\Order\AddOnPriority;
use SwipeStripe\Price\DBPrice;

/**
 * Add on applied to on order item on a purchase. The add-on is applied once to the item's subtotal (unit price x quantity),
 * not to the unit price (i.e. add-on is not applied $quantity times).
 * @package SwipeStripe\Order\OrderItem
 * @property string $Title
 * @property int $Priority
 * @property DBPrice $Amount
 * @property int $OrderItemID
 * @method OrderItem OrderItem()
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
        'Priority' => 'Int',
        'Title'    => 'Varchar',
        'Amount'   => 'Price',
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
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Title'             => 'Title',
        'Amount.Value'      => 'Amount',
    ];

    /**
     * @var string
     */
    private static $default_sort = 'Priority ASC';

    /**
     * @inheritdoc
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName([
                'Priority',
                'OrderItemID',
            ]);
        });

        return parent::getCMSFields();
    }

    /**
     * @inheritDoc
     */
    public function canView($member = null)
    {
        return $this->isActive() && $this->OrderItem()->canView($member);
    }

    /**
     * @inheritDoc
     */
    public function canEdit($member = null)
    {
        return $this->isActive() && $this->OrderItem()->canEdit($member);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        $active = true;
        $this->extend('isActive', $active);
        return $active;
    }
}

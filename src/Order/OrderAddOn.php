<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\Price\DBPrice;

/**
 * Class OrderAddOn
 * @package SwipeStripe\Order
 * @property string $Title
 * @property int $Priority
 * @property DBPrice $Amount
 * @property int $OrderID
 * @method Order Order()
 * @mixin Versioned
 */
class OrderAddOn extends DataObject
{
    /**
     * @var string
     */
    private static $table_name = 'SwipeStripe_OrderAddOn';

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
        'Order' => Order::class,
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
        'Title'        => 'Title',
        'Amount.Value' => 'Amount',
    ];

    /**
     * @var string
     */
    private static $default_sort = 'Priority ASC';

    /**
     * @inheritDoc
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName([
                'Priority',
                'OrderID',
            ]);
        });

        return parent::getCMSFields();
    }

    /**
     * @inheritDoc
     */
    public function canView($member = null)
    {
        return $this->isActive() && $this->Order()->canView($member);
    }

    /**
     * @inheritDoc
     */
    public function canEdit($member = null)
    {
        return $this->isActive() && $this->Order()->canEdit($member);
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

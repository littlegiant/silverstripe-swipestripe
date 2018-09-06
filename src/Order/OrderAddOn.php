<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\Constants\AddOnPriority;
use SwipeStripe\Price\DBPrice;

/**
 * Class OrderAddOn
 * @package SwipeStripe\Order
 * @property string $Type The type of add-on this is.
 * @property string $Title
 * @property int $Priority
 * @property DBPrice $BaseAmount
 * @property DBPrice $Amount
 * @property int $OrderID
 * @method null|Order Order()
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
        'Type'       => DBVarchar::class,
        'Priority'   => DBInt::class,
        'Title'      => DBVarchar::class,
        'BaseAmount' => DBPrice::class,
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
    private static $summary_fields = [
        'Title'       => 'Title',
        'Amount.Nice' => 'Amount',
    ];

    /**
     * @var string
     */
    private static $default_sort = 'Priority ASC';

    /**
     * @return DBPrice
     */
    public function getAmount(): DBPrice
    {
        return $this->BaseAmount;
    }
}

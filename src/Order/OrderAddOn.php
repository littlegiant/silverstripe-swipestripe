<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use SilverStripe\ORM\DataObject;
use SwipeStripe\AddOn;

/**
 * Class OrderAddOn
 * @package SwipeStripe\Order
 * @property int $OrderID
 * @method null|Order Order()
 */
class OrderAddOn extends DataObject
{
    use AddOn;

    const PRIORITY_EARLY = -1;
    const PRIORITY_NORMAL = 0;
    const PRIORITY_LATE = 1;

    /**
     * @var string
     */
    private static $table_name = 'SwipeStripe_OrderAddOn';

    /**
     * @var array
     */
    private static $has_one = [
        'Order' => Order::class,
    ];
}

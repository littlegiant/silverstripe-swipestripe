<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use SilverStripe\ORM\DataObject;

/**
 * Class OrderAddOn
 * @package SwipeStripe\Order
 * @property int $OrderID
 * @method null|Order Order()
 */
class OrderAddOn extends DataObject
{
    use AddOn;

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

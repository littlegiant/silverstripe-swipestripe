<?php

namespace SwipeStripe\Order;

use SilverStripe\ORM\DataObject;

/**
 * Class OrderOrderAddOnMapping
 * @package SwipeStripe\Order
 */
class OrderOrderAddOnMapping extends DataObject
{
    const ORDER = 'Order';
    const ORDER_ADD_ONS = 'OrderAddOns';

    /**
     * @var string
     */
    private static $table = 'SwipeStripe_Order_OrderAddOns';

    /**
     * @var array
     */
    private static $has_one = [
        self::ORDER         => Order::class,
        self::ORDER_ADD_ONS => DataObject::class,
    ];
}

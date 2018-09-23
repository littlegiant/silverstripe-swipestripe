<?php
declare(strict_types=1);

namespace SwipeStripe\Customer;

use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Member;
use SwipeStripe\Order\Order;

/**
 * Class MemberExtension
 * @package SwipeStripe\Customer
 * @property Member $owner
 * @method HasManyList|Order[] Orders()
 */
class MemberExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $has_many = [
        'Orders' => Order::class,
    ];
}

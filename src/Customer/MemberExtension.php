<?php
declare(strict_types=1);

namespace SwipeStripe\Customer;

use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Member;
use SwipeStripe\Order\Order;
use SwipeStripe\ORM\FieldType\DBAddress;

/**
 * Class MemberExtension
 * @package SwipeStripe\Customer
 * @property Member $owner
 * @property DBAddress $DefaultBillingAddress
 * @method HasManyList|Order[] Orders()
 */
class MemberExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'DefaultBillingAddress' => DBAddress::class,
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'Orders' => Order::class,
    ];
}

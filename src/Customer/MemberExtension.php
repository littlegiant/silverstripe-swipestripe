<?php
declare(strict_types=1);

namespace SwipeStripe\Customer;

use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Member;
use SwipeStripe\Order\Order;

/**
 * Class MemberExtension
 * @package SwipeStripe\Customer
 * @property Member $owner
 * @method HasManyList|Customer[] Customers()
 */
class MemberExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $has_many = [
        'Customers' => Customer::class,
    ];

    /**
     * @return DataList|Order[]
     */
    public function Orders(): DataList
    {
        $customerIds = $this->owner->Customers()->column('ID');
        return Order::get()->filter('CustomerID', $customerIds);
    }
}

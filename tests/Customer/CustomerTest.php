<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Customer;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Member;
use SwipeStripe\Order\Order;
use SwipeStripe\Tests\Fixtures;

/**
 * Class CustomerTest
 * @package SwipeStripe\Tests\Customer
 */
class CustomerTest extends SapphireTest
{
    /**
     * @var array
     */
    protected static $fixture_file = [
        Fixtures::CUSTOMERS,
    ];

    /**
     * @var bool
     */
    protected $usesDatabase = true;

    /**
     * @throws ValidationException
     */
    public function testMemberOrders()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'customer');

        $order = Order::create();
        $order->MemberID = $member->ID;
        $order->write();

        $order2 = Order::create();
        $order2->MemberID = $member->ID;
        $order2->write();

        $order3 = Order::create();
        $order3->MemberID = $member->ID;
        $order3->write();

        $order4ID = Order::create()->write();

        $memberOrderIDs = $member->Orders()->column('ID');
        $this->assertEquals([$order->ID, $order2->ID, $order3->ID], $memberOrderIDs);
        $this->assertNotContains($order4ID, $memberOrderIDs);
    }
}

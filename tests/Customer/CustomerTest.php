<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Customer;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ValidationException;
use SwipeStripe\Customer\Customer;
use SwipeStripe\Order\Order;

/**
 * Class CustomerTest
 * @package SwipeStripe\Tests\Customer
 */
class CustomerTest extends SapphireTest
{
    /**
     * @var bool
     */
    protected $usesDatabase = true;

    /**
     * @throws ValidationException
     */
    public function testIsGuest()
    {
        $customer = Customer::create();
        $this->assertTrue($customer->IsGuest());

        $member = $this->createMemberWithPermission('');
        $customer->MemberID = $member->ID;
        $customer->write();
        $this->assertFalse($customer->IsGuest());
        $this->assertEquals($member->ID, $customer->Member()->ID);
        $this->assertEquals($member->ClassName, $customer->Member()->ClassName);
    }

    /**
     * @throws ValidationException
     */
    public function testCustomerEmailValidation()
    {
        $customer = Customer::create();
        $customer->CustomerEmail = 'abcd';

        $this->expectException(ValidationException::class);
        $customer->write();
    }

    /**
     * @throws ValidationException
     */
    public function testEmail()
    {
        $customer = Customer::create();
        $customer->write();
        $this->assertNull($customer->Email);

        $customer->CustomerEmail = 'guest@example.com';
        $customer->write();
        $this->assertSame('guest@example.com', $customer->Email);

        $member = $this->createMemberWithPermission('');
        $member->Email = 'account@example.com';
        $member->write();

        $customer->MemberID = $member->ID;
        $customer->write();

        $this->assertSame('account@example.com', $customer->Email);
    }

    /**
     * @throws ValidationException
     */
    public function testMemberOrders()
    {
        $member = $this->createMemberWithPermission('');
        $member->Email = 'account@example.com';
        $member->write();

        $customer = Customer::create();
        $customer->MemberID = $member->ID;
        $customer->write();

        $customer2 = Customer::create();
        $customer2->MemberID = $member->ID;
        $customer2->write();

        $order = Order::create();
        $order->CustomerID = $customer->ID;
        $order->write();

        $order2 = Order::create();
        $order2->CustomerID = $customer2->ID;
        $order2->write();

        $order3 = Order::create();
        $order3->CustomerID = $customer2->ID;
        $order3->write();

        $order4ID = Order::create()->ID;

        $memberOrderIDs = $member->Orders()->column('ID');
        $this->assertEquals([$order->ID, $order2->ID, $order3->ID], $memberOrderIDs);
        $this->assertNotContains($order4ID, $memberOrderIDs);
    }

    /**
     * @throws ValidationException
     */
    public function testOrders()
    {
        $customer = Customer::create();
        $customer->write();

        $order1 = Order::create();
        $order1->CustomerID = $customer->ID;
        $order1->write();

        $order2 = Order::create();
        $order2->CustomerID = $customer->ID;
        $order2->write();

        $order3ID = Order::create()->write();

        $customerOrderIDs = $customer->Orders()->column('ID');
        $this->assertEquals([$order1->ID, $order2->ID], $customerOrderIDs);
        $this->assertNotContains($order3ID, $customerOrderIDs);
    }
}

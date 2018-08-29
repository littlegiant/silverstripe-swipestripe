<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Customer;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ValidationException;
use SwipeStripe\Customer\Customer;

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
     *
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
     *
     */
    public function testCustomerEmailValidation()
    {
        $customer = Customer::create();
        $customer->CustomerEmail = 'abcd';

        $this->expectException(ValidationException::class);
        $customer->write();
    }

    /**
     *
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
}

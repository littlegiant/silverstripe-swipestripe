<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Pages;

use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Security\Member;
use SwipeStripe\Customer\Customer;
use SwipeStripe\Order\Order;
use SwipeStripe\Pages\ViewOrderPage;
use SwipeStripe\Tests\Fixtures;
use SwipeStripe\Tests\PublishesFixtures;

/**
 * Class ViewOrderPageTest
 * @package SwipeStripe\Tests\Pages
 */
class ViewOrderPageTest extends FunctionalTest
{
    use PublishesFixtures;

    /**
     * @var bool
     */
    protected $usesDatabase = true;

    /**
     * @var array
     */
    protected static $fixture_file = [
        Fixtures::BASE_COMMERCE_PAGES,
        Fixtures::CUSTOMERS,
    ];

    /**
     * @var ViewOrderPage
     */
    private $viewOrderPage;

    /**
     * @var Member
     */
    private $adminMember;

    /**
     * @var Member
     */
    private $customerMember;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * No one should be able to view order for cart
     */
    public function testCantViewCartAsOrder()
    {
        $order = Order::create();
        $order->IsCart = true;
        $order->write();

        $orderUrlWithoutToken = $this->viewOrderPage->Link("{$order->ID}");
        $orderUrlWithToken = $this->viewOrderPage->Link("{$order->ID}/{$order->GuestToken}");

        // Guest
        $this->assertSame(404, $this->get($orderUrlWithoutToken)->getStatusCode());
        $this->assertSame(404, $this->get($orderUrlWithToken)->getStatusCode());

        // Admin
        $this->logInAs($this->adminMember);
        $this->assertSame(404, $this->get($orderUrlWithoutToken)->getStatusCode());
        $this->assertSame(404, $this->get($orderUrlWithToken)->getStatusCode());

        // Customer
        $this->logInAs($this->customerMember);
        $this->assertSame(404, $this->get($orderUrlWithoutToken)->getStatusCode());
        $this->assertSame(404, $this->get($orderUrlWithToken)->getStatusCode());

        // Customer, owns order
        $order->CustomerID = $this->customer->ID;
        $order->write();
        $this->assertSame(404, $this->get($orderUrlWithoutToken)->getStatusCode());
        $this->assertSame(404, $this->get($orderUrlWithToken)->getStatusCode());
    }

    /**
     *
     */
    public function testCanViewOrderAsGuest()
    {
        $order = Order::create();
        $order->IsCart = false;
        $order->write();

        $orderUrlWithoutToken = $this->viewOrderPage->Link("{$order->ID}");
        $orderUrlWithToken = $this->viewOrderPage->Link("{$order->ID}/{$order->GuestToken}");

        // Can view guest order with token
        $this->assertSame(404, $this->get($orderUrlWithoutToken)->getStatusCode());
        $this->assertSame(200, $this->get($orderUrlWithToken)->getStatusCode());

        $order->CustomerID = $this->customer->ID;
        $order->write();

        // Can't view account order with or without token
        $this->assertSame(404, $this->get($orderUrlWithoutToken)->getStatusCode());
        $this->assertSame(404, $this->get($orderUrlWithToken)->getStatusCode());
    }

    /**
     * Customer can view their own order.
     */
    public function testCanViewOrderAsCustomer()
    {
        $order = Order::create();
        $order->IsCart = false;
        $order->CustomerID = $this->customer->ID;
        $order->write();

        $orderUrlWithoutToken = $this->viewOrderPage->Link("{$order->ID}");
        $orderUrlWithToken = $this->viewOrderPage->Link("{$order->ID}/{$order->GuestToken}");

        $this->logInAs($this->customerMember);
        $this->assertSame(200, $this->get($orderUrlWithoutToken)->getStatusCode());
        $this->assertSame(200, $this->get($orderUrlWithToken)->getStatusCode());
    }

    /**
     * Admin can view regardless of ownership.
     */
    public function testCanViewOrderAsAdmin()
    {
        $order = Order::create();
        $order->IsCart = false;
        $order->write();

        $orderUrlWithoutToken = $this->viewOrderPage->Link("{$order->ID}");
        $orderUrlWithToken = $this->viewOrderPage->Link("{$order->ID}/{$order->GuestToken}");

        $this->logInAs($this->adminMember);
        $this->assertSame(200, $this->get($orderUrlWithoutToken)->getStatusCode());
        $this->assertSame(200, $this->get($orderUrlWithToken)->getStatusCode());

        $order->CustomerID = $this->customer->ID;
        $order->write();

        $this->assertSame(200, $this->get($orderUrlWithoutToken)->getStatusCode());
        $this->assertSame(200, $this->get($orderUrlWithToken)->getStatusCode());
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->registerPublishingBlueprint(ViewOrderPage::class);

        parent::setUp();

        $this->viewOrderPage = $this->objFromFixture(ViewOrderPage::class, 'view-order');

        $this->adminMember = $this->createMemberWithPermission('ADMIN');
        $this->customer = $this->objFromFixture(Customer::class, 'account');
        $this->customerMember = $this->customer->Member();
    }
}

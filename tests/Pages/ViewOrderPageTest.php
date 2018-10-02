<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Pages;

use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Security\Member;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\ViewOrderPage;
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
    }
}

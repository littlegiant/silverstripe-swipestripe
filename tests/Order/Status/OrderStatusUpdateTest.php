<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Order\Status;

use SilverStripe\Dev\SapphireTest;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\Status\OrderStatus;
use SwipeStripe\Order\Status\OrderStatusUpdate;
use SwipeStripe\Order\ViewOrderPage;
use SwipeStripe\Tests\Fixtures;
use SwipeStripe\Tests\PublishesFixtures;

/**
 * Class OrderStatusUpdateTest
 * @package SwipeStripe\Tests\Order\Status
 */
class OrderStatusUpdateTest extends SapphireTest
{
    use PublishesFixtures;

    const CUSTOMER_EMAIL = 'customer@example.com';

    /**
     * @var array
     */
    protected static $fixture_file = [
        Fixtures::BASE_COMMERCE_PAGES,
    ];

    /**
     * @var bool
     */
    protected $usesDatabase = true;

    /**
     * @var Order
     */
    protected $order;

    /**
     *
     */
    public function testUpdateChangesOrderStatus()
    {
        $this->assertSame(OrderStatus::PENDING, $this->order->Status);

        $update = $this->createOrderUpdate($this->order, OrderStatus::COMPLETED);
        $update->write();

        $this->assertSame(OrderStatus::COMPLETED, $update->Order()->Status);
    }

    /**
     * @param Order $order
     * @param string $status
     * @return OrderStatusUpdate
     */
    protected function createOrderUpdate(Order $order, string $status = OrderStatus::CONFIRMED): OrderStatusUpdate
    {
        $update = OrderStatusUpdate::create();
        $update->Status = $status;
        $update->OrderID = $order->ID;
        return $update;
    }

    /**
     *
     */
    public function testOrderUpdateSendsNotification()
    {
        $update = $this->createOrderUpdate($this->order, OrderStatus::COMPLETED);
        $update->CustomerVisible = true;
        $update->NotifyCustomer = true;
        $update->write();

        $this->assertEmailSent(static::CUSTOMER_EMAIL);
    }

    /**
     *
     */
    public function testNonNotifyingUpdateDoesntSendEmail()
    {
        $update = $this->createOrderUpdate($this->order);
        $update->CustomerVisible = true;
        $update->NotifyCustomer = false;
        $update->write();

        $this->assertNull($this->findEmail(static::CUSTOMER_EMAIL));
    }

    /**
     *
     */
    public function testCustomerInvisibleUpdateDoesntSendEmail()
    {
        $update = $this->createOrderUpdate($this->order);
        $update->CustomerVisible = false;
        $update->NotifyCustomer = true;
        $update->write();

        $this->assertFalse($update->NotifyCustomer);
        $this->assertNull($this->findEmail(static::CUSTOMER_EMAIL));
    }

    /**
     * Should preserve history about whether original update sent a notification to the customer.
     */
    public function testCustomerVisibilityChangeDoesntAffectNotifiedState()
    {
        $update = $this->createOrderUpdate($this->order);
        $update->CustomerVisible = true;
        $update->NotifyCustomer = true;
        $update->write();

        $this->assertEmailSent(static::CUSTOMER_EMAIL);
        $this->assertTrue($update->CustomerVisible);
        $this->assertTrue($update->NotifyCustomer);

        $update->CustomerVisible = false;
        $update->write();
        $this->assertFalse($update->CustomerVisible);
        $this->assertTrue($update->NotifyCustomer);
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->registerPublishingBlueprint(ViewOrderPage::class);

        parent::setUp();

        $order = Order::create();
        $order->IsCart = false;
        $order->CustomerEmail = static::CUSTOMER_EMAIL;

        $order->Lock();
        $order->write();

        $this->order = $order;
    }
}

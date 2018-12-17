<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Order\OrderItem;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\OrderItem\OrderItem;
use SwipeStripe\Order\OrderItem\OrderItemAddOn;
use SwipeStripe\Price\SupportedCurrencies\SupportedCurrenciesInterface;
use SwipeStripe\Tests\DataObjects\AddOnInactiveExtension;
use SwipeStripe\Tests\DataObjects\TestProduct;
use SwipeStripe\Tests\Fixtures;
use SwipeStripe\Tests\Price\SupportedCurrencies\NeedsSupportedCurrencies;
use SwipeStripe\Tests\WaitsMockTime;

/**
 * Class OrderItemTest
 * @package SwipeStripe\Tests\Order\OrderItem
 */
class OrderItemTest extends SapphireTest
{
    use NeedsSupportedCurrencies;
    use WaitsMockTime;

    /**
     * @var array
     */
    protected static $extra_dataobjects = [
        TestProduct::class,
    ];

    /**
     * @var array
     */
    protected static $fixture_file = [
        Fixtures::PRODUCTS,
    ];

    /**
     * @var bool
     */
    protected $usesDatabase = true;

    /**
     * @var TestProduct
     */
    protected $product;

    /**
     * @var SupportedCurrenciesInterface
     */
    protected $supportedCurrencies;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::setupSupportedCurrencies();
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->product = $this->objFromFixture(TestProduct::class, 'product');
        $this->supportedCurrencies = Injector::inst()->get(SupportedCurrenciesInterface::class);
    }

    /**
     *
     * @throws \Exception
     */
    public function testPurchasableForLockedItem()
    {
        $order = Order::singleton()->createCart();
        $product = $this->product;
        $productOriginalPrice = $product->Price->getMoney();
        $productNewPrice = $productOriginalPrice->multiply(3);

        $order->addItem($product, 1);
        $orderItem = $order->getOrderItem($product, false);
        $this->assertNotNull($orderItem);

        $order->Lock();
        $this->mockWait();

        $product->Price->setValue($productNewPrice);
        $product->write();

        // Test again, with new instance of the OrderItem
        // We have to refetch when the Order changes, because changes will not update to the relation
        // This is because silverstripe uses many php objects to represent the same row in the database
        // Only our local instance of $order and existing references to it will be updated
        $orderItem = $order->getOrderItem($product, false);
        $this->assertTrue($orderItem->Purchasable()->getBasePrice()->getMoney()->equals($productOriginalPrice));
        $this->assertTrue($orderItem->getBasePrice()->getMoney()->equals($productOriginalPrice));

        // Test fetching OrderItem directly, rather than through relation
        /** @var OrderItem $orderItem */
        $orderItem = OrderItem::get()->byID($orderItem->ID);
        $this->assertTrue($orderItem->Purchasable()->getBasePrice()->getMoney()->equals($productOriginalPrice));
        $this->assertTrue($orderItem->getBasePrice()->getMoney()->equals($productOriginalPrice));

        $order->Unlock();

        // Test again, with new instance of the OrderItem
        $orderItem = $order->getOrderItem($product, false);
        $this->assertTrue($orderItem->Purchasable()->getBasePrice()->getMoney()->equals($productNewPrice));
        $this->assertTrue($orderItem->getBasePrice()->getMoney()->equals($productNewPrice));

        // Test fetching OrderItem directly, rather than through relation
        /** @var OrderItem $orderItem */
        $orderItem = OrderItem::get()->byID($orderItem->ID);
        $this->assertTrue($orderItem->Purchasable()->getBasePrice()->getMoney()->equals($productNewPrice));
        $this->assertTrue($orderItem->getBasePrice()->getMoney()->equals($productNewPrice));
    }

    /**
     *
     */
    public function testTotalWithNegativeAddOns()
    {
        $order = Order::singleton()->createCart();
        $order->addItem($this->product);
        $orderItem = $order->getOrderItem($this->product);
        $this->assertTrue($orderItem->Total->getMoney()->isPositive());

        $productPrice = $this->product->getBasePrice()->getMoney();
        $addOn = OrderItemAddOn::create();
        $addOn->Amount->setValue($productPrice->negative()->multiply(3));
        $addOn->OrderItemID = $orderItem->ID;
        $addOn->write();

        $this->assertTrue($orderItem->Total->getMoney()->isZero());
    }

    /**
     *
     */
    public function testTotalWithInactiveAddons()
    {
        $order = Order::singleton()->createCart();
        $order->addItem($this->product);
        $orderItem = $order->getOrderItem($this->product);
        $this->assertTrue($orderItem->Total->getMoney()->isPositive());

        OrderItemAddOn::add_extension(AddOnInactiveExtension::class);
        $productPrice = $this->product->getBasePrice()->getMoney();
        $addOn = OrderItemAddOn::create();
        $addOn->Amount->setValue($productPrice->negative()->multiply(3));
        $addOn->OrderItemID = $orderItem->ID;
        $addOn->write();

        $this->assertTrue($orderItem->Total->getMoney()->equals($productPrice));
    }
}

<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Order\OrderItem;

use Money\Money;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\OrderItem\OrderItem;
use SwipeStripe\Price\DBPrice;
use SwipeStripe\Price\SupportedCurrencies\SupportedCurrenciesInterface;
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
        $product =  $this->product;
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
        $this->assertTrue($orderItem->Purchasable()->getPrice()->getMoney()->equals($productOriginalPrice));
        $this->assertTrue($orderItem->getPrice()->getMoney()->equals($productOriginalPrice));

        $order->Unlock();

        // Test again, with new instance of the OrderItem
        $orderItem = $order->getOrderItem($product, false);
        $this->assertTrue($orderItem->Purchasable()->getPrice()->getMoney()->equals($productNewPrice));
        $this->assertTrue($orderItem->getPrice()->getMoney()->equals($productNewPrice));
    }
}

<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Order;

use SilverStripe\Dev\SapphireTest;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\OrderItem\OrderItem;
use SwipeStripe\Tests\DataObjects\TestProduct;
use SwipeStripe\Tests\Price\NeedsSupportedCurrencies;

/**
 * Class CartTest
 * @package SwipeStripe\Tests\Order
 */
class CartTest extends SapphireTest
{
    use NeedsSupportedCurrencies;

    /**
     * @var array
     */
    protected static $extra_dataobjects = [
        TestProduct::class,
    ];

    /**
     * @var TestProduct
     */
    protected static $product;

    /**
     * @var bool
     */
    protected $usesDatabase = true;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::setupSupportedCurrencies();

        static::$product = TestProduct::create();
        static::$product->write();
    }

    /**
     * @inheritDoc
     * @throws \SilverStripe\ORM\ValidationException
     */
    protected function setUp()
    {
        parent::setUp();

        $this->order = Order::create();
        $this->order->IsCart = true;
        $this->order->write();
    }

    /**
     *
     */
    public function testAddItem()
    {
        $order = $this->order;

        $this->assertTrue($order->Empty());
        $this->assertTrue($order->IsMutable());

        $order->addItem(static::$product);
        $this->assertCount(1, $order->OrderItems());
        $this->assertSame(1, $order->getOrderItem(static::$product)->getQuantity());

        $order->addItem(static::$product);
        $this->assertCount(1, $order->OrderItems());
        $this->assertSame(2, $order->getOrderItem(static::$product)->getQuantity());

        $order->addItem(static::$product, 3);
        $this->assertCount(1, $order->OrderItems());
        $this->assertSame(5, $order->getOrderItem(static::$product)->getQuantity());
    }

    /**
     * @throws \Exception
     */
    public function testAddItemToLockedCart()
    {
        $order = $this->order;

        $this->assertTrue($order->Empty());
        $this->assertTrue($order->IsMutable());

        $order->addItem(static::$product);
        $this->assertCount(1, $order->OrderItems());
        $this->assertSame(1, $order->getOrderItem(static::$product)->getQuantity());

        $order->Lock();
        $this->assertFalse($order->IsMutable());

        try {
            $order->addItem(static::$product);
            $this->fail('Add item on locked order should throw.');
        } catch (\BadMethodCallException $e) {
            // Assert quantity remains the same
            $this->assertCount(1, $order->OrderItems());
            $this->assertSame(1, $order->getOrderItem(static::$product)->getQuantity());
        }
    }

    /**
     *
     */
    public function testRemoveItem()
    {
        $order = $this->order;

        $this->assertTrue($order->Empty());
        $this->assertTrue($order->IsMutable());

        $order->addItem(static::$product);
        $this->assertCount(1, $order->OrderItems());
        $this->assertSame(1, $order->getOrderItem(static::$product)->getQuantity());

        $order->removeItem(static::$product);
        $this->assertTrue($order->Empty());

        $this->assertNull($order->getOrderItem(static::$product, false));
        $orderItem = $order->getOrderItem(static::$product);
        $this->assertFalse($orderItem->exists());
        $this->assertSame(0, $orderItem->getQuantity());
    }

    /**
     * @throws \Exception
     */
    public function testRemoveItemFromLockedCart()
    {
        $order = $this->order;

        $this->assertTrue($order->Empty());
        $this->assertTrue($order->IsMutable());

        $order->addItem(static::$product, 3);
        $this->assertCount(1, $order->OrderItems());
        $this->assertSame(3, $order->getOrderItem(static::$product)->getQuantity());

        $order->Lock();
        $this->assertFalse($order->IsMutable());

        try {
            $order->removeItem(static::$product);
            $this->fail('Remove item on locked order should throw.');
        } catch (\BadMethodCallException $e) {
            $this->assertCount(1, $order->OrderItems());

            $orderItem = $order->getOrderItem(static::$product);
            $this->assertTrue($orderItem->exists());
            $this->assertSame(3, $orderItem->getQuantity());
        }
    }

    /**
     * @throws \Exception
     */
    public function testUnlock()
    {
        $order = $this->order;

        $this->assertTrue($order->Empty());
        $this->assertTrue($order->IsMutable());

        $order->addItem(static::$product, 3);
        $this->assertCount(1, $order->OrderItems());
        $this->assertSame(3, $order->getOrderItem(static::$product)->getQuantity());

        $order->Lock();

        try {
            $order->addItem(static::$product);
            $this->fail('Add item on locked order should throw.');
        } catch (\BadMethodCallException $e) {
            // Assert quantity remains the same
            $this->assertCount(1, $order->OrderItems());
            $this->assertSame(3, $order->getOrderItem(static::$product)->getQuantity());
        }

        $order->Unlock();

        $order->addItem(static::$product);
        $this->assertCount(1, $order->OrderItems());
        $this->assertSame(4, $order->getOrderItem(static::$product)->getQuantity());
    }

    /**
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function testHash()
    {
        $order = $this->order;
        $originalHash = $order->getHash();

        /** @var Order $sameOrder */
        $sameOrder = Order::get()->byID($order->ID);
        $differentOrder = Order::create();
        $order->write();

        // Different empty orders have different hashes
        $this->assertSame($originalHash, $sameOrder->getHash());
        $this->assertNotEquals($originalHash, $differentOrder->getHash());

        // Test that changing from cart to order doesn't affect the hash
        $order->IsCart = false;
        $order->write();
        $this->assertSame($originalHash, $order->getHash());

        // Restore modifications
        $order->IsCart = true;
        $order->write();

        // Adding item changes hash
        $order->addItem(static::$product);
        $this->assertNotEquals($originalHash, $order->getHash());

        // Changing quantity changes hash
        $quantity1Hash = $order->getHash();
        $order->addItem(static::$product);
        $this->assertNotEquals($quantity1Hash, $order->getHash());

        // Revert to quantity 1 restores hash
        $order->setItemQuantity(static::$product, 1);
        $this->assertSame($quantity1Hash, $order->getHash());

        // Revert to empty restores original empty hash
        $order->removeItem(static::$product);
        $this->assertSame($originalHash, $order->getHash());

        // Locking doesn't change hash
        $order->Lock();
        $this->assertSame($originalHash, $order->getHash());
    }

    public function testEmpty()
    {
       $order = $this->order;

       $this->assertTrue($order->Empty());

       $order->setItemQuantity(static::$product, 0);
       $this->assertCount(1, $order->OrderItems());
       $this->assertInstanceOf(OrderItem::class, $order->getOrderItem(static::$product, false));
       $this->assertSame(0, $order->getOrderItem(static::$product)->getQuantity());
       $this->assertTrue($order->Empty());

       $order->addItem(static::$product);
       $this->assertFalse($order->Empty());

       $order->removeItem(static::$product);
       $this->assertTrue($order->Empty());
    }
}

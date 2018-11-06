<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Order\OrderItem;

use SilverStripe\Dev\SapphireTest;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\OrderItem\OrderItem;
use SwipeStripe\Order\OrderItem\OrderItemQuantityField;
use SwipeStripe\Order\OrderLockedException;
use SwipeStripe\Tests\DataObjects\TestProduct;
use SwipeStripe\Tests\Fixtures;
use SwipeStripe\Tests\WaitsMockTime;

/**
 * Class OrderItemQuantityFieldTest
 * @package SwipeStripe\Tests\Order\OrderItem
 */
class OrderItemQuantityFieldTest extends SapphireTest
{
    use WaitsMockTime;

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
     * @var Order
     */
    protected $cart;

    /**
     *
     */
    public function testValueSetFromOrderItem()
    {
        $quantity = 5;

        $orderItem = OrderItem::create();
        $orderItem->OrderID = $this->cart->ID;
        $orderItem->setPurchasable($this->product)
            ->setQuantity($quantity, false)
            ->write();

        $quantityField = OrderItemQuantityField::create('qty')->setOrderItem($orderItem);
        $this->assertSame($quantity, $quantityField->dataValue());
    }

    /**
     *
     */
    public function testSaveIntoOrderItem()
    {
        $quantityOld = 5;
        $quantityNew = 3;

        $orderItem = OrderItem::create();
        $orderItem->OrderID = $this->cart->ID;
        $orderItem->setPurchasable($this->product)
            ->setQuantity($quantityOld, false)
            ->write();

        $quantityField = OrderItemQuantityField::create('qty')->setOrderItem($orderItem);
        $this->assertSame($quantityOld, $quantityField->dataValue());
        $this->assertSame($quantityOld, $orderItem->getQuantity());

        $quantityField->setSubmittedValue($quantityNew);
        $this->assertSame($quantityNew, $quantityField->dataValue());
        $this->assertSame($quantityOld, $orderItem->getQuantity());

        $quantityField->saveInto($orderItem);
        $this->assertSame($quantityNew, $orderItem->getQuantity());
    }

    /**
     *
     */
    public function testSaveIntoOrder()
    {
        $quantityOld = 5;
        $quantityNew = 3;

        $orderItem = OrderItem::create();
        $orderItem->OrderID = $this->cart->ID;
        $orderItem->setPurchasable($this->product)
            ->setQuantity($quantityOld, false)
            ->write();

        $quantityField = OrderItemQuantityField::create('qty')->setOrderItem($orderItem);
        $this->assertSame($quantityOld, $quantityField->dataValue());
        $this->assertSame($quantityOld, $orderItem->getQuantity());

        $quantityField->setSubmittedValue($quantityNew);
        $this->assertSame($quantityNew, $quantityField->dataValue());
        $this->assertSame($quantityOld, $orderItem->getQuantity());

        $quantityField->saveInto($this->cart);
        $this->assertSame($quantityNew, $orderItem->getQuantity());
    }

    /**
     *
     */
    public function testSaveIntoDifferentOrder()
    {
        $quantityOld = 5;
        $quantityNew = 3;

        $orderItem = OrderItem::create();
        $orderItem->OrderID = $this->cart->ID;
        $orderItem->setPurchasable($this->product)
            ->setQuantity($quantityOld, false)
            ->write();

        $quantityField = OrderItemQuantityField::create('qty')->setOrderItem($orderItem);
        $this->assertSame($quantityOld, $quantityField->dataValue());
        $this->assertSame($quantityOld, $orderItem->getQuantity());

        $quantityField->setSubmittedValue($quantityNew);
        $this->assertSame($quantityNew, $quantityField->dataValue());
        $this->assertSame($quantityOld, $orderItem->getQuantity());

        $this->expectException(\InvalidArgumentException::class);
        $quantityField->saveInto(Order::singleton()->createCart());
    }

    /**
     * @throws \Exception
     */
    public function testReadOnlyForImmutableOrderItem()
    {
        $quantityOld = 5;
        $quantityNew = 3;

        $orderItem = OrderItem::create();
        $orderItem->OrderID = $this->cart->ID;
        $orderItem->setPurchasable($this->product)
            ->setQuantity($quantityOld, false);
        $orderItem->write();

        $this->cart->Lock();
        $this->mockWait();

        $this->assertFalse($orderItem->IsMutable());
        $quantityField = OrderItemQuantityField::create('qty')->setOrderItem($orderItem);
        $this->assertSame($quantityOld, $quantityField->dataValue());
        $this->assertSame($quantityOld, $orderItem->getQuantity());
        $this->assertTrue($quantityField->isReadonly());

        $quantityField->setSubmittedValue($quantityNew);
        $this->assertSame($quantityNew, $quantityField->dataValue());
        $this->assertSame($quantityOld, $orderItem->getQuantity());

        $this->expectException(OrderLockedException::class);
        $quantityField->saveInto($orderItem);
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->product = $this->objFromFixture(TestProduct::class, 'product');
        $this->cart = Order::singleton()->createCart();
    }
}

<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Order;

use Money\Currency;
use Money\Money;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Omnipay\Model\Payment;
use SwipeStripe\Order\Cart\ViewCartPage;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\OrderAddOn;
use SwipeStripe\Order\OrderItem\OrderItemAddOn;
use SwipeStripe\Order\PaymentExtension;
use SwipeStripe\Order\PaymentStatus;
use SwipeStripe\Order\ViewOrderPage;
use SwipeStripe\Price\SupportedCurrencies\SupportedCurrenciesInterface;
use SwipeStripe\Tests\DataObjects\TestProduct;
use SwipeStripe\Tests\Fixtures;
use SwipeStripe\Tests\Price\SupportedCurrencies\NeedsSupportedCurrencies;
use SwipeStripe\Tests\PublishesFixtures;

/**
 * Class OrderTest
 * @package SwipeStripe\Tests\Order
 */
class OrderTest extends SapphireTest
{
    use NeedsSupportedCurrencies;
    use PublishesFixtures;

    /**
     * @var array
     */
    protected static $fixture_file = [
        Fixtures::BASE_COMMERCE_PAGES,
        Fixtures::PRODUCTS,
    ];

    /**
     * @var array
     */
    protected static $extra_dataobjects = [
        TestProduct::class,
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
     * @var TestProduct
     */
    protected $product;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        Config::modify()->set(Payment::class, 'allowed_gateways', ['Dummy']);
    }

    /**
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function testOrderAddOnTotal()
    {
        $order = $this->order;
        $product = $this->product;

        $addOn = OrderAddOn::create();
        $addOn->OrderID = $order->ID;
        $addOn->BaseAmount->setValue(new Money(10, $this->currency));
        $addOn->write();

        // Not applied for empty cart
        $this->assertTrue($order->Total(false)->getMoney()->equals(new Money(0, $this->currency)));
        $this->assertTrue($order->Total()->getMoney()->isZero());

        // Not applied for cart with quantity 0
        $order->setItemQuantity($product, 0);
        $this->assertTrue($order->Total(false)->getMoney()->equals(new Money(0, $this->currency)));
        $this->assertTrue($order->Total()->getMoney()->isZero());

        // Applied for item in cart
        $order->setItemQuantity($product, 1);
        $this->assertTrue($order->Total(false)->getMoney()->equals($product->getPrice()->getMoney()));
        $this->assertTrue($order->Total()->getMoney()->equals(
            $product->getPrice()->getMoney()->add($addOn->Amount->getMoney())
        ));
    }

    /**
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function testUnpaidTotal()
    {
        $order = $this->order;
        $product = $this->product;
        $order->addItem($product);

        $this->assertTrue($order->Total()->getMoney()->equals($product->getPrice()->getMoney()));
        $this->assertTrue($order->UnpaidTotal()->getMoney()->equals($order->Total()->getMoney()));

        $fullTotalMoney = $order->Total()->getMoney();
        $halfTotalMoney = $fullTotalMoney->divide(2);

        /** @var SupportedCurrenciesInterface $supportedCurrencies */
        $supportedCurrencies = Injector::inst()->get(SupportedCurrenciesInterface::class);
        /** @var Payment|PaymentExtension $payment */
        $payment = Payment::create()->init('Dummy',
            $supportedCurrencies->formatDecimal($halfTotalMoney), $halfTotalMoney->getCurrency()->getCode());
        $payment->Status = PaymentStatus::CAPTURED;
        $payment->OrderID = $order->ID;
        $payment->write();

        $this->assertTrue($order->Total()->getMoney()->equals($fullTotalMoney));
        $this->assertTrue($order->TotalPaid()->getMoney()->equals($halfTotalMoney));
        $this->assertTrue($order->UnpaidTotal()->getMoney()->equals($halfTotalMoney));

        /** @var Payment|PaymentExtension $payment2 */
        $payment2 = Payment::create()->init('Dummy',
            $supportedCurrencies->formatDecimal($halfTotalMoney), $halfTotalMoney->getCurrency()->getCode());
        $payment2->Status = PaymentStatus::CAPTURED;
        $payment2->OrderID = $order->ID;
        $payment2->write();

        $this->assertTrue($order->Total()->getMoney()->equals($fullTotalMoney));
        $this->assertTrue($order->TotalPaid()->getMoney()->equals($fullTotalMoney));
        $this->assertTrue($order->UnpaidTotal()->getMoney()->isZero());
    }

    /**
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function testOrderItemAddOnTotal()
    {
        $order = $this->order;
        $product = $this->product;

        $fullPrice = $product->getPrice()->getMoney();
        $halfPrice = $fullPrice->divide(2);

        $addOn = OrderItemAddOn::create();
        $addOn->BaseAmount->setValue($halfPrice->multiply(-1));
        $addOn->write();
        $order->setItemQuantity($product, 0);
        $order->attachPurchasableAddOn($product, $addOn);

        // Add-on applied once, quantity is 0
        $this->assertCount(1, $order->OrderItems());
        $this->assertEquals(0, $order->OrderItems()->sum('Quantity'));
        $this->assertTrue($order->Total()->getMoney()->isZero());

        // Add-on applied once, quantity is 1
        $order->addItem($product);
        $this->assertEquals(1, $order->OrderItems()->sum('Quantity'));
        $this->assertTrue($halfPrice->equals($order->Total()->getMoney()));

        // Add-on applied once, quantity is 2
        $order->addItem($product);
        $this->assertEquals(2, $order->OrderItems()->sum('Quantity'));
        $this->assertTrue($order->Total()->getMoney()->equals($fullPrice->add($halfPrice)));

        // Add-on applied per unit, quantity is 2
        $addOn->ApplyPerUnit = true;
        $addOn->write();
        $this->assertEquals(2, $order->OrderItems()->sum('Quantity'));
        $this->assertTrue($order->Total()->getMoney()->equals($fullPrice));

        // Add-on applied per unit, quantity is 1
        $order->setItemQuantity($product, 1);
        $this->assertEquals(1, $order->OrderItems()->sum('Quantity'));
        $this->assertTrue($order->Total()->getMoney()->equals($halfPrice));

        // Add-on applied per unit, quantity is 0
        $order->setItemQuantity($product, 0);
        $this->assertEquals(0, $order->OrderItems()->sum('Quantity'));
        $this->assertTrue($order->Total()->getMoney()->isZero());
    }

    /**
     *
     */
    public function testLink()
    {
        $order = $this->order;
        /** @var ViewCartPage $cartPage */
        $cartPage = $this->objFromFixture(ViewCartPage::class, 'view-cart');
        /** @var ViewOrderPage $orderPage */
        $orderPage = $this->objFromFixture(ViewOrderPage::class, 'view-order');

        $this->assertEquals($cartPage->Link(), $order->Link());

        $order->IsCart = false;
        $order->write();

        $this->assertStringStartsWith($orderPage->Link(), $order->Link());
        $this->assertContains($order->GuestToken, $order->Link());
    }

    /**
     *
     */
    public function testDetachPurchasableAddOn()
    {
       $order = $this->order;
       $product = $this->product;
       $order->addItem($product);

       $this->assertTrue($order->Total()->getMoney()->equals($product->getPrice()->getMoney()));

       $halfPriceAddOn = OrderItemAddOn::create();
       $halfPriceAddOn->BaseAmount->setValue($product->getPrice()->getMoney()->multiply(-0.5));
       $halfPriceAddOn->write();

       $order->attachPurchasableAddOn($product, $halfPriceAddOn);
       $this->assertTrue($order->Total()->getMoney()->equals($product->getPrice()->getMoney()->multiply(0.5)));

       $order->detachPurchasableAddOn($product, $halfPriceAddOn);
       $this->assertTrue($order->Total()->getMoney()->equals($product->getPrice()->getMoney()));
    }

    /**
     * @throws \Exception
     */
    public function testVersionLocking()
    {
        $order = $this->order;
        $product = $this->product;
        $productOriginalPrice = $product->getPrice()->getMoney();

        $order->addItem($product);
        $this->assertTrue($order->Total()->getMoney()->equals($productOriginalPrice));

        // Lock and adjust price
        $order->Lock();
        $product->Price->setValue($productOriginalPrice->multiply(3));
        $product->write();
        $this->assertTrue($order->Total()->getMoney()->equals($productOriginalPrice));

        // Unlock and verify new price is given
        $order->Unlock();
        $this->assertTrue($order->Total()->getMoney()->equals($productOriginalPrice->multiply(3)));
    }

    /**
     * @throws \SilverStripe\ORM\ValidationException
     */
    protected function setUp()
    {
        $this->registerPublishingBlueprint(ViewCartPage::class);
        $this->registerPublishingBlueprint(ViewOrderPage::class);

        parent::setUp();

        $this->currency = new Currency('NZD');
        $this->setupSupportedCurrencies();

        $this->order = Order::singleton()->createCart();
        $this->product = $this->objFromFixture(TestProduct::class, 'product');
    }
}

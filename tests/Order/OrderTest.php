<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Order;

use Money\Currency;
use Money\Money;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Omnipay\Model\Payment;
use SwipeStripe\Constants\PaymentStatus;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\OrderAddOn;
use SwipeStripe\Order\OrderItem\OrderItemAddOn;
use SwipeStripe\SupportedCurrencies\SupportedCurrenciesInterface;
use SwipeStripe\Tests\DataObjects\TestProduct;
use SwipeStripe\Tests\Fixtures;
use SwipeStripe\Tests\Price\NeedsSupportedCurrencies;

/**
 * Class OrderTest
 * @package SwipeStripe\Tests\Order
 */
class OrderTest extends SapphireTest
{
    use NeedsSupportedCurrencies;

    /**
     * @var array
     */
    protected static $fixture_file = [
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

        $addOn = OrderAddOn::create();
        $addOn->OrderID = $order->ID;
        $addOn->BaseAmount->setValue(new Money(10, $this->currency));
        $addOn->write();

        // Not applied for empty cart
        $this->assertTrue($order->Total(false)->getMoney()->equals(new Money(0, $this->currency)));
        $this->assertTrue($order->Total()->getMoney()->isZero());

        /** @var TestProduct $product */
        $product = $this->objFromFixture(TestProduct::class, 'product');

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

        /** @var TestProduct $product */
        $product = $this->objFromFixture(TestProduct::class, 'product');
        $order->addItem($product);

        $this->assertTrue($order->Total()->getMoney()->equals($product->getPrice()->getMoney()));
        $this->assertTrue($order->UnpaidTotal()->getMoney()->equals($order->Total()->getMoney()));

        $fullTotalMoney = $order->Total()->getMoney();
        $halfTotalMoney = $fullTotalMoney->divide(2);

        /** @var SupportedCurrenciesInterface $supportedCurrencies */
        $supportedCurrencies = Injector::inst()->get(SupportedCurrenciesInterface::class);
        $payment = Payment::create()->init('Dummy',
            $supportedCurrencies->formatDecimal($halfTotalMoney), $halfTotalMoney->getCurrency()->getCode());
        $payment->Status = PaymentStatus::CAPTURED;
        $payment->OrderID = $order->ID;
        $payment->write();

        $this->assertTrue($order->Total()->getMoney()->equals($fullTotalMoney));
        $this->assertTrue($order->TotalPaid()->getMoney()->equals($halfTotalMoney));
        $this->assertTrue($order->UnpaidTotal()->getMoney()->equals($halfTotalMoney));

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
        /** @var TestProduct $product */
        $product = $this->objFromFixture(TestProduct::class, 'product');

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
     * @throws \SilverStripe\ORM\ValidationException
     */
    protected function setUp()
    {
        parent::setUp();

        $this->currency = new Currency('NZD');
        $this->setupSupportedCurrencies();

        $this->order = Order::create();
        $this->order->IsCart = true;
        $this->order->write();
    }
}

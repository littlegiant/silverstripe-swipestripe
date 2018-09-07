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
use SwipeStripe\SupportedCurrencies\SupportedCurrenciesInterface;
use SwipeStripe\Tests\DataObjects\TestProduct;
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

        $this->assertTrue($order->Total(false)->getMoney()->equals(new Money(0, $this->currency)));
        $this->assertTrue($order->Total()->getMoney()->equals(new Money(10, $this->currency)));
    }

    /**
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function testUnpaidTotal()
    {
        $order = $this->order;

        $product = TestProduct::create();
        $product->write();
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

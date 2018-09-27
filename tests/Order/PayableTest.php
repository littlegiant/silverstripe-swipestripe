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
use SwipeStripe\Price\SupportedCurrencies\SupportedCurrenciesInterface;
use SwipeStripe\Tests\Price\SupportedCurrencies\NeedsSupportedCurrencies;

/**
 * Class PayableTest
 * @package SwipeStripe\Tests\Order
 */
class PayableTest extends SapphireTest
{
    use NeedsSupportedCurrencies;

    /**
     * @var bool
     */
    protected $usesDatabase = true;

    /**
     * @var SupportedCurrenciesInterface
     */
    protected $supportedCurrencies;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::setupSupportedCurrencies();
        Config::modify()->set(Payment::class, 'allowed_gateways', ['Dummy']);
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->supportedCurrencies = Injector::inst()->get(SupportedCurrenciesInterface::class);
        $this->currency = $this->supportedCurrencies->getDefaultCurrency();
    }

    /**
     * @param Order $order
     * @param Money $amount
     * @param string $status
     * @return int Payment ID
     * @throws \SilverStripe\ORM\ValidationException
     */
    private function addPaymentWithStatus(Order $order, Money $amount, string $status): int
    {
        $payment = Payment::create()->init('Dummy',
            $this->supportedCurrencies->formatDecimal($amount),
            $amount->getCurrency()->getCode());
        $payment->Status = $status;
        $payment->OrderID = $order->ID;

        return $payment->write();
    }

    /**
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function testTotalPaid()
    {
        $order = Order::create();
        $order->write();

        $this->assertCount(0, $order->Payments());

        $capturedAmount = new Money(1000, $this->currency);
        $authorizedAmount = new Money(200, $this->currency);
        $pendingAmount = new Money(1500, $this->currency);
        $refundAmount = new Money(500, $this->currency);
        $voidAmount = new Money(750, $this->currency);

        $this->addPaymentWithStatus($order, $capturedAmount->divide(2), PaymentStatus::CAPTURED);
        $this->addPaymentWithStatus($order, $capturedAmount->divide(2), PaymentStatus::CAPTURED);
        $this->addPaymentWithStatus($order, $authorizedAmount, PaymentStatus::AUTHORIZED);
        $this->addPaymentWithStatus($order, $pendingAmount->divide(2), PaymentStatus::PENDING_PURCHASE);
        $this->addPaymentWithStatus($order, $pendingAmount->divide(2), PaymentStatus::PENDING_CAPTURE);
        $this->addPaymentWithStatus($order, $refundAmount, PaymentStatus::REFUNDED);
        $this->addPaymentWithStatus($order, $voidAmount, PaymentStatus::VOID);

        $this->assertCount(7, $order->Payments());
        $this->assertTrue($order->TotalPaid()->getMoney()->equals(
            $capturedAmount
        ));
    }

    /**
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function testTotalPaidOrAuthorized()
    {
        $order = Order::create();
        $order->write();

        $this->assertCount(0, $order->Payments());

        $capturedAmount = new Money(1000, $this->currency);
        $authorizedAmount = new Money(200, $this->currency);
        $pendingAmount = new Money(1500, $this->currency);
        $refundAmount = new Money(500, $this->currency);
        $voidAmount = new Money(750, $this->currency);

        $this->addPaymentWithStatus($order, $capturedAmount->divide(2), PaymentStatus::CAPTURED);
        $this->addPaymentWithStatus($order, $capturedAmount->divide(2), PaymentStatus::CAPTURED);
        $this->addPaymentWithStatus($order, $authorizedAmount, PaymentStatus::AUTHORIZED);
        $this->addPaymentWithStatus($order, $pendingAmount->divide(2), PaymentStatus::PENDING_PURCHASE);
        $this->addPaymentWithStatus($order, $pendingAmount->divide(2), PaymentStatus::PENDING_CAPTURE);
        $this->addPaymentWithStatus($order, $refundAmount, PaymentStatus::REFUNDED);
        $this->addPaymentWithStatus($order, $voidAmount, PaymentStatus::VOID);

        $this->assertCount(7, $order->Payments());
        $this->assertTrue($order->TotalPaidOrAuthorized()->getMoney()->equals(
            $capturedAmount->add($authorizedAmount)
        ));
    }

    /**
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function testHasPendingPayments()
    {
        $order = Order::create();
        $order->write();

        $this->assertCount(0, $order->Payments());
        $this->assertFalse($order->HasPendingPayments());

        $capturedAmount = new Money(1000, $this->currency);
        $authorizedAmount = new Money(200, $this->currency);
        $pendingAmount = new Money(1500, $this->currency);
        $refundAmount = new Money(500, $this->currency);
        $voidAmount = new Money(750, $this->currency);

        $this->addPaymentWithStatus($order, $capturedAmount->divide(2), PaymentStatus::CAPTURED);
        $this->addPaymentWithStatus($order, $capturedAmount->divide(2), PaymentStatus::CAPTURED);
        $this->addPaymentWithStatus($order, $authorizedAmount, PaymentStatus::AUTHORIZED);
        $pending1 = $this->addPaymentWithStatus($order, $pendingAmount->divide(2), PaymentStatus::PENDING_PURCHASE);
        $pending2 = $this->addPaymentWithStatus($order, $pendingAmount->divide(2), PaymentStatus::PENDING_CAPTURE);
        $this->addPaymentWithStatus($order, $refundAmount, PaymentStatus::REFUNDED);
        $this->addPaymentWithStatus($order, $voidAmount, PaymentStatus::VOID);

        $this->assertCount(7, $order->Payments());
        $this->assertTrue($order->HasPendingPayments());

        $order->Payments()->removeMany([$pending1, $pending2]);
        $this->assertCount(5, $order->Payments());
        $this->assertFalse($order->HasPendingPayments());
    }
}

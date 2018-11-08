<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Order;

use Money\Money;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Omnipay\Model\Payment;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\PaymentExtension;
use SwipeStripe\Price\SupportedCurrencies\SupportedCurrenciesInterface;

trait AddsPayments
{
    /**
     * @param Order $order
     * @param Money $amount
     * @param string $status
     * @return Payment|PaymentExtension
     */
    protected function addPaymentWithStatus(Order $order, Money $amount, string $status): Payment
    {
        /** @var SupportedCurrenciesInterface $supportedCurrencies */
        $supportedCurrencies = Injector::inst()->get(SupportedCurrenciesInterface::class);

        /** @var Payment|PaymentExtension $payment */
        $payment = Payment::create()->init('Dummy',
            $supportedCurrencies->formatDecimal($amount),
            $amount->getCurrency()->getCode());
        $payment->Status = $status;
        $payment->OrderID = $order->ID;
        $payment->write();

        return $payment;
    }
}

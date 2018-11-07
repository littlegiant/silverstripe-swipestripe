<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use Money\Currency;
use Money\Money;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\HasManyList;
use SwipeStripe\Price\DBPrice;

/**
 * MoneyPHP alternative of silverstripe-omnipay's Payable extension.
 * @package SwipeStripe\Order
 * @property Order $owner
 * @see \SilverStripe\Omnipay\Extensions\Payable
 * @method HasManyList|Payment[] Payments()
 */
class Payable extends DataExtension
{
    /**
     * @var array
     */
    private static $has_many = [
        'Payments' => Payment::class,
    ];

    /**
     * Get the total captured amount
     * @see \SilverStripe\Omnipay\Extensions\Payable::TotalPaid()
     * @return DBPrice
     */
    public function TotalPaid(): DBPrice
    {
        $paidMoney = new Money(0, $this->owner->supportedCurrencies->getDefaultCurrency());

        if ($this->owner->exists()) {
            /** @var DataList|Payment[] $payments */
            $payments = $this->owner->Payments()->filter('Status', PaymentStatus::CAPTURED);

            foreach ($payments as $payment) {
                $paymentMoney = $this->owner->supportedCurrencies->parseDecimal(new Currency($payment->getCurrency()),
                    $payment->getAmount());

                $paidMoney = $paidMoney->isZero()
                    ? $paymentMoney
                    : $paidMoney->add($paymentMoney);
            }
        }

        $this->owner->extend('updateTotalPaid', $paidMoney);
        return DBPrice::create_field(DBPrice::INJECTOR_SPEC, $paidMoney);
    }

    /**
     * Get the total captured or authorized amount, excluding Manual payments.
     * @see \SilverStripe\Omnipay\Extensions\Payable::TotalPaidOrAuthorized()
     * @return DBPrice
     */
    public function TotalPaidOrAuthorized(): DBPrice
    {
        $paidMoney = new Money(0, $this->owner->supportedCurrencies->getDefaultCurrency());

        if ($this->owner->exists()) {
            /** @var DataList|Payment[] $payments */
            $payments = $this->owner->Payments()->filter([
                'Status' => [
                    PaymentStatus::CAPTURED,
                    PaymentStatus::AUTHORIZED,
                ],
            ]);

            foreach ($payments as $payment) {
                // Captured and non-manual authorized payments count towards the total
                if ($payment->Status !== PaymentStatus::AUTHORIZED || !GatewayInfo::isManual($payment->Gateway)) {
                    $paymentMoney = $this->owner->supportedCurrencies->parseDecimal(new Currency($payment->getCurrency()),
                        $payment->getAmount());

                    $paidMoney = $paidMoney->isZero()
                        ? $paymentMoney
                        : $paidMoney->add($paymentMoney);
                }
            }
        }

        $this->owner->extend('updateTotalPaidOrAuthorized', $paidMoney);
        return DBPrice::create_field(DBPrice::INJECTOR_SPEC, $paidMoney);
    }

    /**
     * Whether or not the model has payments that are in a pending state.
     * Can be used to show a waiting screen to the user or similar.
     * @see \SilverStripe\Omnipay\Extensions\Payable::HasPendingPayments()
     * @return bool
     */
    public function HasPendingPayments(): bool
    {
        $hasPending = $this->owner->Payments()->filter('Status', [
            PaymentStatus::PENDING_AUTHORIZATION,
            PaymentStatus::PENDING_PURCHASE,
            PaymentStatus::PENDING_CAPTURE,
            PaymentStatus::PENDING_REFUND,
            PaymentStatus::PENDING_VOID,
        ])->exists();

        $this->owner->extend('updateHasPendingPayments', $hasPending);
        return $hasPending;
    }
}

<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use Money\Currency;
use Money\Money;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\ORM\HasManyList;
use SwipeStripe\Constants\PaymentStatus;
use SwipeStripe\Price\DBPrice;
use SwipeStripe\SupportedCurrencies\SupportedCurrenciesInterface;

/**
 * MoneyPHP alternative of silverstripe-omnipay's Payable extension.
 * @package SwipeStripe\Order
 * @see \SilverStripe\Omnipay\Extensions\Payable
 * @method HasManyList|Payment[] Payments()
 */
trait Payable
{
    /**
     * @internal
     * @aliasConfig $has_many
     * @var array
     */
    private static $__swipestripe_payable_has_many = [
        'Payments' => Payment::class,
    ];

    /**
     * @internal
     * @aliasConfig $dependencies
     * @var array
     */
    private static $__swipestripe_payable_dependencies = [
        'supportedCurrencies' => '%$' . SupportedCurrenciesInterface::class,
    ];

    /**
     * @var SupportedCurrenciesInterface
     */
    public $supportedCurrencies;

    /**
     * Get the total captured amount
     * @see \SilverStripe\Omnipay\Extensions\Payable::TotalPaid()
     * @return DBPrice
     */
    public function TotalPaid(): DBPrice
    {
        $paidMoney = new Money(0, $this->supportedCurrencies->getDefaultCurrency());

        if ($this->exists()) {
            foreach ($this->Payments() as $payment) {
                if ($payment->Status === PaymentStatus::CAPTURED) {
                    $paymentMoney = $this->supportedCurrencies->parseDecimal(new Currency($payment->getCurrency()),
                        $payment->getAmount());

                    $paidMoney = $paidMoney->isZero()
                        ? $paymentMoney
                        : $paidMoney->add($paymentMoney);
                }
            }
        }

        return DBPrice::create_field(DBPrice::class, $paidMoney);
    }

    /**
     * Get the total captured or authorized amount, excluding Manual payments.
     * @see \SilverStripe\Omnipay\Extensions\Payable::TotalPaidOrAuthorized()
     * @return DBPrice
     */
    public function TotalPaidOrAuthorized(): DBPrice
    {
        $paidMoney = new Money(0, $this->supportedCurrencies->getDefaultCurrency());

        if ($this->exists()) {
            foreach ($this->Payments() as $payment) {
                // Captured and non-manual authorized payments count towards the total
                if ($payment->Status === PaymentStatus::CAPTURED ||
                    ($payment->Status === PaymentStatus::AUTHORIZED && !GatewayInfo::isManual($payment->Gateway))) {
                    $paymentMoney = $this->supportedCurrencies->parseDecimal(new Currency($payment->getCurrency()),
                        $payment->getAmount());

                    $paidMoney = $paidMoney->isZero()
                        ? $paymentMoney
                        : $paidMoney->add($paymentMoney);
                }
            }
        }

        return DBPrice::create_field(DBPrice::class, $paidMoney);
    }

    /**
     * Whether or not the model has payments that are in a pending state.
     * Can be used to show a waiting screen to the user or similar.
     * @see \SilverStripe\Omnipay\Extensions\Payable::HasPendingPayments()
     * @return bool
     */
    public function HasPendingPayments(): bool
    {
        return $this->Payments()->filter('Status', [
            PaymentStatus::PENDING_AUTHORIZATION,
            PaymentStatus::PENDING_PURCHASE,
            PaymentStatus::PENDING_CAPTURE,
            PaymentStatus::PENDING_REFUND,
            PaymentStatus::PENDING_VOID,
        ])->exists();
    }
}

<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Checkout;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FormRequestHandler;
use SilverStripe\Omnipay\Exception\InvalidConfigurationException;
use SilverStripe\Omnipay\Exception\InvalidStateException;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\Omnipay\Service\ServiceFactory;
use SwipeStripe\Order\PaymentExtension;
use SwipeStripe\Price\SupportedCurrencies\SupportedCurrenciesInterface;

/**
 * Class CheckoutFormRequestHandler
 * @package SwipeStripe\Order\Checkout
 * @property-read ServiceFactory $paymentServiceFactory
 * @property-read SupportedCurrenciesInterface $supportedCurrencies
 * @property CheckoutForm $form
 */
class CheckoutFormRequestHandler extends FormRequestHandler
{
    /**
     * @var array
     */
    private static $dependencies = [
        'paymentServiceFactory' => '%$' . ServiceFactory::class,
        'supportedCurrencies'   => '%$' . SupportedCurrenciesInterface::class,
    ];

    /**
     * @param array $data
     * @param CheckoutForm $form
     * @return HTTPResponse
     * @throws InvalidConfigurationException
     * @throws InvalidStateException
     * @throws \Exception
     */
    public function ConfirmCheckout(array $data, CheckoutForm $form): HTTPResponse
    {
        $cart = $form->getCart();
        $cart->Lock();
        $form->saveInto($cart);

        $this->extend('beforeInitPayment', $data);
        $cart->write();

        /** @var Payment|PaymentExtension $payment */
        $payment = Payment::create();
        $payment->OrderID = $cart->ID;

        $paymentMethod = $data[CheckoutForm::PAYMENT_METHOD_FIELD];
        $dueMoney = $cart->UnpaidTotal()->getMoney();

        $payment->init($paymentMethod, $this->supportedCurrencies->formatDecimal($dueMoney),
            $dueMoney->getCurrency()->getCode())
            ->setSuccessUrl($form->getSuccessUrl($payment))
            ->setFailureUrl($form->getFailureUrl($payment));
        $payment->write();

        $response = $this->paymentServiceFactory
            ->getService($payment, ServiceFactory::INTENT_PURCHASE)
            ->initiate($data);

        $this->extend('afterInitPayment', $data, $payment, $response);
        return $response->redirectOrRespond();
    }

    /**
     * @inheritDoc
     */
    public function redirectBack()
    {
        $response = parent::redirectBack();
        // Strip query string (e.g. previous payment failure)
        $cleanedUrl = strtok($response->getHeader('Location'), '?');

        return $this->redirect($cleanedUrl);
    }
}

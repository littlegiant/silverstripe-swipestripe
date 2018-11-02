<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Checkout;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FormRequestHandler;
use SilverStripe\Omnipay\Exception\InvalidConfigurationException;
use SilverStripe\Omnipay\Exception\InvalidStateException;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\Omnipay\Service\ServiceFactory;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SwipeStripe\HasActiveCart;
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
    use HasActiveCart;

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
     */
    public function ConfirmCheckout(array $data, CheckoutForm $form): HTTPResponse
    {
        if (!$form->getCart()->IsMutable()) {
            // If the cart was locked due to trying to pay, then checkout was clicked again
            // This stops being able to create multiple active checkouts on one order
            $original = $form->getCart();
            $clone = $original->duplicate();

            if ($original->ID === $this->ActiveCart->ID) {
                $this->setActiveCart($clone);
            }

            $form->setCart($clone);
        }
        $this->extend('beforeConfirmCheckout', $form, $data);

        $cart = $form->getCart();
        $cart->Lock();
        $form->saveInto($cart);

        $this->extend('beforeInitPayment', $form, $data);
        $cart->write();

        /** @var Payment|PaymentExtension $payment */
        $payment = Payment::create();
        $payment->OrderID = $cart->ID;

        $dueMoney = $cart->UnpaidTotal()->getMoney();
        $this->extend('updateDueMoney', $form, $data, $dueMoney);

        $payment->init(
            $this->getPaymentMethod($form, $data),
            $this->supportedCurrencies->formatDecimal($dueMoney),
            $dueMoney->getCurrency()->getCode()
        )->setSuccessUrl($form->getSuccessUrl($payment))
            ->setFailureUrl($form->getFailureUrl($payment))
            ->write();

        $response = $this->paymentServiceFactory
            ->getService($payment, ServiceFactory::INTENT_PURCHASE)
            ->initiate(array_merge($data, $cart->toPaymentData()));

        $this->extend('afterInitPayment', $form, $data, $payment, $response);
        return $response->redirectOrRespond();
    }

    /**
     * @param CheckoutForm $form
     * @param array $data
     * @return string
     * @throws InvalidConfigurationException
     */
    protected function getPaymentMethod(CheckoutForm $form, array $data): string
    {
        $gateways = $form->getAvailablePaymentMethods();
        if (count($gateways) === 1) {
            return key($gateways);
        }

        $paymentMethod = $data[CheckoutForm::PAYMENT_METHOD_FIELD];
        if (isset($gateways[$paymentMethod])) {
            return $paymentMethod;
        } else {
            throw ValidationException::create(
                ValidationResult::create()->addFieldError(CheckoutForm::PAYMENT_METHOD_FIELD,
                    _t(CheckoutForm::class . '.UNSUPPORTED_PAYMENT_METHOD',
                        'The payment method you have selected is not supported.'))
            );
        }
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

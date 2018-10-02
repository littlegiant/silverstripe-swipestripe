<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Checkout;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\TextField;
use SilverStripe\Omnipay\GatewayFieldsFactory;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Omnipay\Model\Message\CompletePurchaseError;
use SilverStripe\Omnipay\Model\Message\PaymentMessage;
use SilverStripe\Omnipay\Model\Message\PurchaseError;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\Omnipay\Service\ServiceFactory;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\OrderConfirmationPage;
use SwipeStripe\Order\PaymentExtension;
use SwipeStripe\Order\PaymentStatus;
use SwipeStripe\Price\SupportedCurrencies\SupportedCurrenciesInterface;

/**
 * Class CheckoutForm
 * @package SwipeStripe\Order\Checkout
 * @property Payment|null $PaymentError
 */
class CheckoutForm extends Form
{
    const ORDER_HASH_FIELD = 'OrderContents';
    const PAYMENT_METHOD_FIELD = 'PaymentMethod';
    const PAYMENT_ID_QUERY_PARAM = 'payment';

    const CHECKOUT_GUEST = 'Guest';
    const CHECKOUT_CREATE_ACCOUNT = 'Account';

    /**
     * @var array
     */
    private static $dependencies = [
        'paymentServiceFactory' => '%$' . ServiceFactory::class,
        'supportedCurrencies'   => '%$' . SupportedCurrenciesInterface::class,
    ];

    /**
     * @var ServiceFactory
     */
    public $paymentServiceFactory;

    /**
     * @var SupportedCurrenciesInterface
     */
    public $supportedCurrencies;

    /**
     * @var Order
     */
    protected $cart;

    /**
     * @var null|string|false
     */
    protected $paymentError = null;

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function __construct(Order $cart, ?RequestHandler $controller = null, ?string $name = null)
    {
        $this->cart = $cart;
        $cart->Unlock();

        parent::__construct(
            $controller,
            $name ?? static::DEFAULT_NAME,
            $this->buildFields(),
            $this->buildActions(),
            CheckoutFormValidator::create()
        );

        if (!$this->getSessionData()) {
            $this->extend('beforeLoadDataFromCart');
            $this->loadDataFrom($cart);
        }
    }

    /**
     * @return Order
     */
    public function getCart(): Order
    {
        return $this->cart;
    }

    /**
     * @return null|string
     */
    public function getPaymentError(): ?string
    {
        if ($this->paymentError !== null) {
            // paymentError = false means cached result of "no error"
            return $this->paymentError ?: null;
        }

        $paymentIdentifier = $this->getRequest()->getVar(static::PAYMENT_ID_QUERY_PARAM);

        if (empty($paymentIdentifier)) {
            // No payment query param
            $this->paymentError = false;
            return null;
        }

        /** @var Payment|null $payment */
        $payment = $this->cart->Payments()->find('Identifier', $paymentIdentifier);
        $defaultMessage = _t(self::class . '.PAYMENT_ERROR',
            'There was an error processing your payment. Please try again.');

        if ($payment === null) {
            // No payment with that identifier for this order, can't show error
            $errorMessage = false;
        } elseif ($payment->Status === PaymentStatus::VOID) {
            // Void status is returned for cancelled or declined card
            $errorMessage = $defaultMessage;
        } else {
            /** @var PaymentMessage|null $message */
            $message = $payment->Messages()->last();
            $errorMessage = $message instanceof PurchaseError || $message instanceof CompletePurchaseError
                ? $message->Message
                : $defaultMessage;
        }

        $this->paymentError = $errorMessage;
        return $errorMessage ?: null;
    }

    /**
     * @param array $data
     * @return HTTPResponse
     * @throws \SilverStripe\Omnipay\Exception\InvalidConfigurationException
     * @throws \SilverStripe\Omnipay\Exception\InvalidStateException
     * @throws \Exception
     */
    public function ConfirmCheckout(array $data): HTTPResponse
    {
        $this->cart->Lock();
        $this->extend('beforeInitPayment', $data);

        $this->saveInto($this->cart);
        $this->cart->write();

        /** @var Payment|PaymentExtension $payment */
        $payment = Payment::create();
        $payment->OrderID = $this->cart->ID;

        $paymentMethod = $data[static::PAYMENT_METHOD_FIELD];
        $dueMoney = $this->cart->UnpaidTotal()->getMoney();
        $payment->init($paymentMethod, $this->supportedCurrencies->formatDecimal($dueMoney),
            $dueMoney->getCurrency()->getCode())
            ->setSuccessUrl($this->getSuccessUrl())
            ->setFailureUrl($this->getFailureUrl($payment));

        if ($payment->isChanged(null, Payment::CHANGE_VALUE)) {
            $payment->write();
        }

        $response = $this->paymentServiceFactory
            ->getService($payment, ServiceFactory::INTENT_PURCHASE)
            ->initiate($data);

        $this->extend('afterInitPayment', $data, $payment, $response);
        return $response->redirectOrRespond();
    }

    /**
     * @return string
     */
    protected function getSuccessUrl(): string
    {
        /** @var OrderConfirmationPage $orderConfirmationPage */
        $orderConfirmationPage = OrderConfirmationPage::get_one(OrderConfirmationPage::class);
        return $orderConfirmationPage->LinkForOrder($this->cart);
    }

    /**
     * @param Payment $payment
     * @return string
     * @throws \SilverStripe\ORM\ValidationException
     */
    protected function getFailureUrl(Payment $payment): string
    {
        if (!$payment->Identifier) {
            // Force identifier to be generated
            /** @see Payment::onBeforeWrite() */
            $payment->write();
        }

        return Controller::join_links(
            $this->getController()->Link(),
            sprintf('?%1$s=%2$s', static::PAYMENT_ID_QUERY_PARAM, $payment->Identifier)
        );
    }

    /**
     * @inheritdoc
     * @throws \SilverStripe\Omnipay\Exception\InvalidConfigurationException
     */
    protected function buildFields(): FieldList
    {
        $fields = FieldList::create([
            TextField::create('CustomerName', 'Name'),
            EmailField::create('CustomerEmail', 'Email'),
            $this->cart->BillingAddress->scaffoldFormField('Billing Address'),
        ]);

        $gateways = GatewayInfo::getSupportedGateways();
        $gatewayField = count($gateways) > 1
            ? OptionsetField::create(static::PAYMENT_METHOD_FIELD,
                _t(self::class . '.PAYMENT_METHOD', 'Select your payment method'), $gateways)
            : HiddenField::create(static::PAYMENT_METHOD_FIELD, null, key($gateways));

        $fields->add($gatewayField);
        $fields->merge($this->buildGatewayFields($gateways));
        $fields->add(HiddenField::create(static::ORDER_HASH_FIELD, null, $this->cart->getHash()));

        $this->extend('updateFields', $fields);
        return $fields;
    }

    /**
     * @param array $gateways Map of gateway internal name to display name.
     * @return FieldList
     */
    protected function buildGatewayFields(array $gateways): FieldList
    {
        $fields = FieldList::create();

        foreach ($gateways as $gateway => $displayName) {
            $fieldFactory = GatewayFieldsFactory::create($gateway);
            $fields->merge($fieldFactory->getFields());
        }

        return $fields;
    }

    /**
     * @inheritDoc
     */
    protected function buildActions(): FieldList
    {
        return FieldList::create(
            FormAction::create('ConfirmCheckout', _t(self::class . '.CONFIRM_CHECKOUT', 'Confirm Checkout'))
        );
    }

    /**
     * @inheritDoc
     */
    protected function buildRequestHandler()
    {
        return CheckoutFormRequestHandler::create($this);
    }
}

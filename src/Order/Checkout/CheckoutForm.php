<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Checkout;

use SilverStripe\Control\Controller;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\TextField;
use SilverStripe\Omnipay\Exception\InvalidConfigurationException;
use SilverStripe\Omnipay\GatewayFieldsFactory;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Omnipay\Model\Message\CompletePurchaseError;
use SilverStripe\Omnipay\Model\Message\PaymentMessage;
use SilverStripe\Omnipay\Model\Message\PurchaseError;
use SilverStripe\Omnipay\Model\Payment;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\OrderConfirmationPage;
use SwipeStripe\Order\PaymentStatus;

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

        $validator = CheckoutFormValidator::create();
        if (count($this->getAvailablePaymentMethods()) > 1) {
            $validator->addRequiredField(static::PAYMENT_METHOD_FIELD);
        }

        parent::__construct(
            $controller,
            $name ?? static::DEFAULT_NAME,
            $this->buildFields(),
            $this->buildActions(),
            $validator
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
        $payment = $this->getCart()->Payments()->find('Identifier', $paymentIdentifier);
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
     * @param Payment $payment
     * @return string
     */
    public function getSuccessUrl(Payment $payment): string
    {
        /** @var OrderConfirmationPage $orderConfirmationPage */
        $orderConfirmationPage = OrderConfirmationPage::get_one(OrderConfirmationPage::class);
        return $orderConfirmationPage->LinkForOrder($this->getCart());
    }

    /**
     * @param Payment $payment
     * @return string
     */
    public function getFailureUrl(Payment $payment): string
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
     * @throws InvalidConfigurationException
     */
    protected function buildFields(): FieldList
    {
        $fields = FieldList::create([
            TextField::create('CustomerName', 'Name'),
            EmailField::create('CustomerEmail', 'Email'),
            $this->getCart()->BillingAddress->scaffoldFormField('Billing Address'),
        ]);

        $gateways = $this->getAvailablePaymentMethods();
        if (count($gateways) > 1) {
            $fields->add(OptionsetField::create(static::PAYMENT_METHOD_FIELD,
                _t(self::class . '.PAYMENT_METHOD', 'Select your payment method'), $gateways));
        }

        $fields->merge($this->buildGatewayFields($gateways));
        $fields->add(HiddenField::create(static::ORDER_HASH_FIELD, null, $this->getCart()->getHash()));

        $this->extend('updateFields', $fields);

        return $fields;
    }

    /**
     * @return array
     * @throws InvalidConfigurationException
     */
    public function getAvailablePaymentMethods(): array
    {
        return GatewayInfo::getSupportedGateways();
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
     * @param Order $cart
     * @return CheckoutForm
     */
    public function setCart(Order $cart): self
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function buildRequestHandler()
    {
        return CheckoutFormRequestHandler::create($this);
    }
}

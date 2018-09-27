<?php
declare(strict_types=1);

namespace SwipeStripe\Forms;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\SingleSelectField;
use SilverStripe\Forms\TextField;
use SilverStripe\Omnipay\GatewayFieldsFactory;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Omnipay\Model\Message\CompletePurchaseError;
use SilverStripe\Omnipay\Model\Message\PaymentMessage;
use SilverStripe\Omnipay\Model\Message\PurchaseError;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\Omnipay\Service\ServiceFactory;
use SilverStripe\Security\Security;
use SwipeStripe\Constants\PaymentStatus;
use SwipeStripe\Order\Order;
use SwipeStripe\Pages\OrderConfirmationPage;
use SwipeStripe\Price\SupportedCurrencies\SupportedCurrenciesInterface;

/**
 * Class CheckoutForm
 * @package SwipeStripe\Forms
 * @property Payment|null $PaymentError
 */
class CheckoutForm extends BaseForm
{
    const ORDER_HASH_FIELD = 'OrderContents';
    const PAYMENT_METHOD_FIELD = 'PaymentMethod';
    const PAYMENT_ID_QUERY_PARAM = 'payment';

    /**
     * @var array
     */
    private static $dependencies = [
        'supportedCurrencies' => '%$' . SupportedCurrenciesInterface::class,
    ];

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

        parent::__construct($controller, $name, RequiredFields::create([
            static::PAYMENT_METHOD_FIELD,
            'CustomerName',
            'CustomerEmail',
            'BillingAddressStreet',
            'BillingAddressCity',
            'BillingAddressPostCode',
            'BillingAddressCountry',
        ]));

        if (!$this->getSessionData()) {
            $this->loadDataFrom($cart);
        }
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
        $defaultMessage = _t(self::class . '.PAYMENT_ERROR', 'There was an error processing your payment. Please try again.');

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
     * @inheritDoc
     * @throws \SilverStripe\Omnipay\Exception\InvalidConfigurationException
     */
    public function validationResult()
    {
        $result = parent::validationResult();

        if ($this->cart->Empty()) {
            $result->addError(_t(self::class . '.CART_EMPTY', 'It looks like your cart is empty. Please add some items before attempting to checkout.'));
        }

        if ($this->Fields()->dataFieldByName(static::ORDER_HASH_FIELD)->dataValue() !== $this->cart->Hash) {
            $result->addError(_t(self::class . '.ORDER_HASH_CHANGED',
                'It looks like your cart has changed since you last loaded the checkout page. Please refresh, re-check your cart and try again.'));
        }

        $paymentMethodField = $this->Fields()->dataFieldByName(static::PAYMENT_METHOD_FIELD);
        if (!$paymentMethodField instanceof SingleSelectField) {
            // Validate gateway if it's not a single select (hidden for single payment method) - single select does its own validation
            $gateways = GatewayInfo::getSupportedGateways(false);
            $selectedGateway = $paymentMethodField->dataValue();

            if (!isset($gateways[$selectedGateway])) {
                $result->addError(_t(self::class . '.INVALID_PAYMENT_METHOD', 'The requested payment method is not available.'));
            }
        }

        return $result;
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

        $this->saveInto($this->cart);
        $this->cart->MemberID = Security::getCurrentUser() ? Security::getCurrentUser()->ID : 0;
        $this->cart->write();

        $payment = Payment::create();
        $payment->OrderID = $this->cart->ID;

        $paymentMethod = $data[static::PAYMENT_METHOD_FIELD];
        $dueMoney = $this->cart->UnpaidTotal()->getMoney();
        $payment->init($paymentMethod, $this->supportedCurrencies->formatDecimal($dueMoney), $dueMoney->getCurrency()->getCode())
            ->setSuccessUrl($this->getSuccessUrl())
            ->setFailureUrl($this->getFailureUrl($payment));

        if ($payment->isChanged(null, Payment::CHANGE_VALUE)) {
            $payment->write();
        }

        $response = ServiceFactory::singleton()
            ->getService($payment, ServiceFactory::INTENT_PURCHASE)
            ->initiate($data);

        return $response->redirectOrRespond();
    }

    /**
     * @return string
     */
    protected function getSuccessUrl(): string
    {
        /** @var OrderConfirmationPage $orderConfirmationPage */
        $orderConfirmationPage = OrderConfirmationPage::get_one(OrderConfirmationPage::class);
        return $orderConfirmationPage->LinkForOrder($this->cart, true);
    }

    /**
     * @param Payment $payment
     * @return string
     */
    protected function getFailureUrl(Payment $payment): string
    {
        if (!$payment->isInDB()) {
            $payment->write();
        }

        return $this->getController()->Link() . '?' . http_build_query([
                static::PAYMENT_ID_QUERY_PARAM => $payment->Identifier,
            ]);
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
            ? OptionsetField::create(static::PAYMENT_METHOD_FIELD, _t(self::class . '.PAYMENT_METHOD', 'Select your payment method'), $gateways)
            : HiddenField::create(static::PAYMENT_METHOD_FIELD, null, key($gateways));

        $fields->add($gatewayField);
        $fields->merge($this->buildGatewayFields($gateways));
        $fields->add(HiddenField::create(static::ORDER_HASH_FIELD, null, $this->cart->getHash()));

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
}

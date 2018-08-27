<?php

namespace SwipeStripe\Price;

use InvalidArgumentException;
use Money\Currencies;
use Money\Currencies\CurrencyList;
use Money\Currency;
use Money\Money;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\MoneyField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectInterface;
use SwipeStripe\SupportedCurrencies\SupportedCurrenciesInterface;

/**
 * A field for storing money values into the database via DBPrice, while minimising potential for float errors.
 * @package SwipeStripe\Price
 * @see DBPrice
 * @see MoneyField
 */
class PriceField extends FormField
{
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
     * @var string
     */
    protected $schemaDataType = 'MoneyField';

    /**
     * Limit the currencies
     * @var Currencies|null
     */
    protected $allowedCurrencies = null;

    /**
     * @var AmountField
     */
    protected $amountField = null;

    /**
     * @var FormField
     */
    protected $currencyField = null;

    /**
     * @see Injector::inject()
     */
    public function injected(): void
    {
        $this->amountField = AmountField::create(
            "{$this->getName()}[Amount]",
            _t(MoneyField::class . '.FIELDLABELAMOUNT', 'Amount')
        );
        $this->buildCurrencyField();
    }

    /**
     * Builds a new currency field based on the allowed currencies configured
     *
     * @return FormField
     */
    protected function buildCurrencyField()
    {
        $fieldName = "{$this->getName()}[Currency]";
        $fieldValue = $this->getActiveCurrency()->getCode();

        // Validate allowed currencies
        $allowedCurrencies = $this->getAllowedCurrenciesMap();
        if (count($allowedCurrencies) === 1) {
            // Hidden field for single currency
            $field = ReadonlyField::create($fieldName);
            reset($allowedCurrencies);
            $fieldValue = key($allowedCurrencies);
        } elseif (!empty($allowedCurrencies)) {
            // Dropdown field for multiple currencies
            $field = DropdownField::create($fieldName)
                ->setSource($allowedCurrencies);
        } else {
            // Free-text entry for currency value
            $field = TextField::create($fieldName);
        }

        $field->setTitle(_t(MoneyField::class . '.FIELDLABELCURRENCY', 'Currency'))
            ->setValue($fieldValue)
            ->setReadonly($this->isReadonly())
            ->setDisabled($this->isDisabled());

        $this->currencyField = $field;
        return $field;
    }

    /**
     * @return Currency
     */
    protected function getActiveCurrency(): Currency
    {
        $code = $this->getCurrencyField()
            ? $this->getCurrencyField()->dataValue()
            : null;

        return $code
            ? new Currency($code)
            : $this->supportedCurrencies->getDefaultCurrency();
    }

    /**
     * Gets field for the currency selector
     * @return FormField|null
     */
    public function getCurrencyField(): ?FormField
    {
        return $this->currencyField;
    }

    /**
     * @return array
     */
    protected function getAllowedCurrenciesMap(): array
    {
        $allowedCurrencies = $this->getAllowedCurrencies();
        $defaultCurrency = $this->supportedCurrencies->getDefaultCurrency();
        $currencyMap = [];

        if ($allowedCurrencies->contains($defaultCurrency)) {
            $currencyMap[$defaultCurrency->getCode()] = $defaultCurrency->getCode();
        }

        /** @var Currency $currency */
        foreach ($allowedCurrencies as $currency) {
            if (!$currency->equals($defaultCurrency)) {
                $currencyMap[$currency->getCode()] = $currency->getCode();
            }
        }

        return $currencyMap;
    }

    /**
     * @return Currencies
     */
    public function getAllowedCurrencies(): Currencies
    {
        return $this->allowedCurrencies ?? $this->supportedCurrencies;
    }

    /**
     * Set list of allowed currencies.
     * @param string[]|Currencies|null $currencies Currencies instance, null, or array of currency code strings. Array
     * currencies must be of form CODE => SUBUNIT or must be in SupportedCurrencies.
     * @return $this
     */
    public function setAllowedCurrencies($currencies): self
    {
        if (empty($currencies)) {
            $currencies = null;
        } elseif (is_array($currencies)) {
            $subUnitMap = [];

            foreach ($currencies as $key => $value) {
                if (is_int($key) && is_string($value)) {
                    // Array of codes
                    $subUnitMap[$value] = $this->supportedCurrencies->subunitFor(new Currency($value));
                } elseif (is_string($key) && is_int($value)) {
                    // Map of code => subunit
                    $subUnitMap[$key] = $value;
                }
            }

            $currencies = new CurrencyList($subUnitMap);
        } elseif (!$currencies instanceof Currencies) {
            throw new \InvalidArgumentException('Currencies must be null, array or instance of Currencies interface.');
        }

        $this->allowedCurrencies = $currencies;

        // Rebuild currency field
        $this->buildCurrencyField();

        return $this;
    }

    /**
     *
     */
    public function __clone()
    {
        $this->amountField = clone $this->amountField;
        $this->currencyField = clone $this->currencyField;
    }

    /**
     * @inheritdoc
     */
    public function setSubmittedValue($value, $data = null)
    {
        if (empty($value)) {
            $this->value = null;
            $this->setCurrency(null)
                ->setAmount(null);
            return $this;
        } elseif (!is_array($value)) {
            throw new InvalidArgumentException('Value is not submitted array');
        }

        if (!isset($value['Currency']) && $this->getCurrencyField() instanceof ReadonlyField) {
            // Read-only field for single choice
            $value['Currency'] = $this->getCurrencyField()->Value();
        }

        $this->setCurrency($value['Currency'], $value, true)
            ->setAmount($value['Amount'], $value, true);

        $this->value = $this->dataValue();
        return $this;
    }

    /**
     * @param string|int|null $amount
     * @param array|DataObject|null $data
     * @param bool $submitted
     * @return $this
     */
    public function setAmount($amount, $data = null, bool $submitted = false): self
    {
        if ($submitted) {
            $this->getAmountField()->setSubmittedValue($amount, $data);
        } else {
            $this->getAmountField()->setValue($amount, $data);
        }

        return $this;
    }

    /**
     * Gets field for the amount input
     * @return AmountField|null
     */
    public function getAmountField(): ?AmountField
    {
        return $this->amountField;
    }

    /**
     * @param Currency|string|null $codeOrCurrency
     * @param array|DataObject|null $data
     * @param bool $submitted
     * @return $this
     */
    public function setCurrency($codeOrCurrency, $data = null, bool $submitted = false): self
    {
        if ($codeOrCurrency === null) {
            if ($submitted) {
                $this->getCurrencyField()->setSubmittedValue(null, $data);
            } else {
                $this->getCurrencyField()->setValue(null, $data);
            }

            $this->getAmountField()->setScale(0);
            return $this;
        }

        if (is_string($codeOrCurrency)) {
            $codeOrCurrency = new Currency($codeOrCurrency);
        }

        $this->getAmountField()->setScale($this->getAllowedCurrencies()->subunitFor($codeOrCurrency));

        if ($submitted) {
            $this->getCurrencyField()->setSubmittedValue($codeOrCurrency->getCode(), $data);
        } else {
            $this->getCurrencyField()->setValue($codeOrCurrency->getCode(), $data);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function dataValue()
    {
        return $this->getDBPrice()->Nice();
    }

    /**
     * Get value as DBPrice object useful for formatting the number
     * @return DBPrice
     */
    protected function getDBPrice(): DBPrice
    {
        return DBPrice::create_field(DBPrice::INJECTOR_SPEC, $this->getMoney())
            ->setLocale($this->getLocale());
    }

    /**
     * @return Money
     */
    public function getMoney(): Money
    {
        return $this->supportedCurrencies->parseDecimal(
            $this->getActiveCurrency(),
            $this->getAmountField()->dataValue() ?? 0
        );
    }

    /**
     * Get locale to format this currency in.
     * Defaults to current locale.
     * @return string
     */
    public function getLocale(): string
    {
        return $this->getAmountField()->getLocale();
    }

    /**
     * @inheritdoc
     * @param array|string|DBPrice|Money|null $value
     */
    public function setValue($value, $data = null)
    {
        if (empty($value)) {
            $this->value = null;
            $this->setCurrency(null)
                ->setAmount(null);
            return $this;
        }

        // Convert string to array
        // E.g. `44.00 NZD`
        if (is_string($value) &&
            preg_match('/^(?<amount>[\\d\\.]+)( (?<currency>\w{3}))?$/i', $value, $matches)
        ) {
            $currency = isset($matches['currency']) ? strtoupper($matches['currency']) : null;
            $value = [
                'Currency' => $currency,
                'Amount'   => $matches['amount'],
            ];
        } elseif ($value instanceof DBPrice || $value instanceof Money) {
            $money = $value instanceof DBPrice
                ? $value->getMoney()
                : $value;

            $value = [
                'Currency' => $money !== null ? $money->getCurrency()->getCode() : null,
                'Amount'   => $money !== null ? $this->supportedCurrencies->formatDecimal($money) : null,
            ];
        } elseif (!is_array($value)) {
            throw new InvalidArgumentException("Invalid currency format");
        }

        $this->setCurrency($value['Currency'], $value)
            ->setAmount($value['Amount'], $value);

        $this->value = $this->dataValue();
        return $this;
    }

    /**
     * @inheritdoc
     * @param DataObjectInterface|DataObject $dataObject
     */
    public function saveInto(DataObjectInterface $dataObject)
    {
        $fieldName = $this->getName();
        if ($dataObject->hasMethod("set$fieldName")) {
            $dataObject->$fieldName = $this->getDBPrice();
        } else {
            $currencyField = "{$fieldName}Currency";
            $amountField = "{$fieldName}Amount";
            $money = $this->getMoney();

            $dataObject->$currencyField = $money->getCurrency()->getCode();
            $dataObject->$amountField = $money->getAmount();
        }
    }

    /**
     * @inheritdoc
     */
    public function performReadonlyTransformation()
    {
        $clone = clone $this;
        $clone->setReadonly(true);
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function setReadonly($bool)
    {
        parent::setReadonly($bool);

        $this->amountField->setReadonly($bool);
        $this->currencyField->setReadonly($bool);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setDisabled($bool)
    {
        parent::setDisabled($bool);

        $this->amountField->setDisabled($bool);
        $this->currencyField->setDisabled($bool);

        return $this;
    }

    /**
     * Assign locale to format this currency in
     * @param null|string $locale
     * @return $this
     */
    public function setLocale(?string $locale): self
    {
        $this->amountField->setLocale($locale);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validate($validator)
    {
        $currencies = $this->getAllowedCurrencies();
        $currency = $this->currencyField->dataValue();
        if ($currency && !$currencies->contains(new Currency($currency))) {
            $validator->validationError($this->getName(),
                _t(MoneyField::class . '.INVALID_CURRENCY', 'Currency {currency} is not in the list of allowed currencies',
                    ['currency' => $currency]));
            return false;
        }

        return $this->amountField->validate($validator) && $this->currencyField->validate($validator);
    }

    /**
     * @inheritdoc
     */
    public function setForm($form)
    {
        $this->currencyField->setForm($form);
        $this->amountField->setForm($form);
        return parent::setForm($form);
    }
}

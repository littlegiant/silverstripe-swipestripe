<?php
declare(strict_types=1);

namespace SwipeStripe\Price;

use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\FieldType\DBComposite;
use SilverStripe\ORM\FieldType\DBMoney;
use SwipeStripe\Price\SupportedCurrencies\SupportedCurrenciesInterface;

/**
 * A monetary value database object that's currency aware, and explicitly designed to use MoneyPHP to avoid potential
 * floating point errors.
 * @package SwipeStripe\Price
 * @see PriceField
 * @see DBMoney
 * @property string $Currency
 * @property string $Amount
 * @property-read SupportedCurrenciesInterface $supportedCurrencies
 */
class DBPrice extends DBComposite
{
    const INJECTOR_SPEC = 'Price';

    /**
     * @var array
     */
    private static $composite_db = [
        'Currency' => 'Varchar(3)',
        'Amount'   => 'Varchar', // Money library uses integer strings to support values > PHP_INT_MAX
    ];

    /**
     * @var array
     */
    private static $dependencies = [
        'supportedCurrencies' => '%$' . SupportedCurrenciesInterface::class,
    ];

    /**
     * @var null|string
     */
    protected $locale = null;

    /**
     * Get nicely formatted currency (based on current locale).
     * @return null|string
     */
    public function Nice(): ?string
    {
        $money = $this->getMoney();

        $formatter = new IntlMoneyFormatter($this->getNumberFormatter(), $this->supportedCurrencies);
        return $formatter->format($money);
    }

    /**
     * @return Money
     */
    public function getMoney(): Money
    {
        return $this->exists()
            ? new Money($this->getField('Amount'), new Currency($this->getField('Currency')))
            : new Money(0, $this->supportedCurrencies->getDefaultCurrency());
    }

    /**
     * @return boolean
     */
    public function exists()
    {
        return !empty($this->getField('Currency')) && is_numeric($this->getField('Amount'));
    }

    /**
     * Get currency formatter
     * @return \NumberFormatter
     */
    public function getNumberFormatter(): \NumberFormatter
    {
        $locale = $this->getLocale();
        $money = $this->getMoney();

        if ($money) {
            $locale .= '@currency=' . $money->getCurrency()->getCode();
        }

        return \NumberFormatter::create($locale, \NumberFormatter::CURRENCY);
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale ?? i18n::get_locale();
    }

    /**
     * @param null|string $locale
     * @return $this
     */
    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Standard '0.00 CUR' format (non-localised)
     * @return string
     */
    public function getValue()
    {
        $money = $this->getMoney();
        return $this->supportedCurrencies->formatDecimal($money) . ' ' . $money->getCurrency()->getCode();
    }

    /**
     * @inheritDoc
     */
    public function setValue($value, $record = null, $markChanged = true)
    {
        if ($value instanceof Money) {
            $value = [
                'Currency' => $value->getCurrency()->getCode(),
                'Amount'   => $value->getAmount(),
            ];
        }

        return parent::setValue($value, $record, $markChanged);
    }

    /**
     * Determine if this has a non-zero amount
     * @return bool
     */
    public function hasAmount(): bool
    {
        return !$this->getMoney()->isZero();
    }

    /**
     * @inheritdoc
     */
    public function scaffoldFormField($title = null, $params = null)
    {
        return PriceField::create($this->getName(), $title)
            ->setLocale($this->getLocale());
    }
}

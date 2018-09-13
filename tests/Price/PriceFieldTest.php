<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Price;

use Money\Currency;
use Money\Money;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\SingleSelectField;
use SilverStripe\ORM\ValidationException;
use SwipeStripe\Price\DBPrice;
use SwipeStripe\Price\PriceField;
use SwipeStripe\SupportedCurrencies\SupportedCurrenciesInterface;
use SwipeStripe\Tests\DataObjects\TestProduct;
use SwipeStripe\Tests\TestValidator;

/**
 * Class PriceFieldTest
 * @package SwipeStripe\Tests\Price
 */
class PriceFieldTest extends SapphireTest
{
    use NeedsSupportedCurrencies;

    /**
     * @var array
     */
    protected static $extra_dataobjects = [
        TestProduct::class,
    ];

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::setupSupportedCurrencies();
    }

    /**
     *
     */
    public function testCurrencyField()
    {
        $priceField = PriceField::create('Price');

        // Currency field with one choice should be read-only
        $priceField->setAllowedCurrencies(['AUD']);
        $this->assertInstanceOf(ReadonlyField::class, $priceField->getCurrencyField());

        // With multiple choices, it should be some kind of single-select
        $priceField->setAllowedCurrencies(['NZD', 'USD']);
        $this->assertInstanceOf(SingleSelectField::class, $priceField->getCurrencyField());

        // When allowed currencies is null, it should default to injected supported currencies
        $priceField->setAllowedCurrencies(null);
        $this->assertSame(Injector::inst()->get(SupportedCurrenciesInterface::class), $priceField->getAllowedCurrencies());
    }

    /**
     *
     */
    public function testAmountToSubUnit()
    {
        $nzd = new Currency('NZD');
        $twoNzd = new Money(200, $nzd);
        $priceField = PriceField::create('Price');

        // 2.00 converted to 200 cents
        $this->submitValue($priceField, $nzd, '2.00')
            ->assertTrue($priceField->getMoney()->equals($twoNzd));

        // 2 converted to 200 cents
        $this->submitValue($priceField, $nzd, '2.')
            ->assertTrue($priceField->getMoney()->equals($twoNzd));

        // 0.02 converted to 2 cents
        $this->submitValue($priceField, $nzd, '0.02')
            ->assertTrue($priceField->getMoney()->equals(new Money(2, $nzd)));

        // 0.2 converted to 20 cents
        $this->submitValue($priceField, $nzd, '0.2')
            ->assertTrue($priceField->getMoney()->equals(new Money(20, $nzd)));

        // 2.002 rounded down to 2.00
        $this->submitValue($priceField, $nzd, '2.002')
            ->assertTrue($priceField->getMoney()->equals($twoNzd));
    }

    /**
     * @param PriceField $field
     * @param Currency $currency
     * @param string $amount
     * @return $this
     */
    private function submitValue(PriceField $field, ?Currency $currency, ?string $amount): self
    {
        $value = [];

        if ($currency !== null) {
            $value['Currency'] = $currency->getCode();
        }

        if ($amount !== null) {
            $value['Amount'] = $amount;
        }

        $field->setSubmittedValue($value);
        $field->validate(new TestValidator());

        return $this;
    }

    /**
     *
     */
    public function testAmountToNoSubUnit()
    {
        $jpy = new Currency('JPY');
        $oneJpy = new Money(1, $jpy);
        $priceField = PriceField::create('Price');

        // 1 = 1 JPY
        $this->submitValue($priceField, $jpy, '1')
            ->assertTrue($priceField->getMoney()->equals($oneJpy));

        // 0.5 rounded to 1
        $this->submitValue($priceField, $jpy, '0.5')
            ->assertTrue($priceField->getMoney()->equals($oneJpy));
    }

    /**
     *
     */
    public function testCurrencyFieldDisallowedCurrency()
    {
        $priceField = PriceField::create('Price')
            ->setAllowedCurrencies(['NZD']);

        $this->expectException(ValidationException::class);
        $this->submitValue($priceField, new Currency('JPY'), '20');
    }

    /**
     * @throws ValidationException
     */
    public function testSetValue()
    {
        $priceField = PriceField::create('Price');

        /** @var SupportedCurrenciesInterface $supportedCurrencies */
        $supportedCurrencies = Injector::inst()->get(SupportedCurrenciesInterface::class);
        $defaultCurrency = $supportedCurrencies->getDefaultCurrency();

        $priceField->setValue(null);
        $this->assertTrue($priceField->getMoney()->equals(new Money(0, $defaultCurrency)));

        $priceField->setValue(new Money(20, new Currency('NZD')));
        $this->assertTrue($priceField->getMoney()->equals(new Money(20, new Currency('NZD'))));

        $priceField->setValue(DBPrice::create_field(DBPrice::class, [
            'Currency' => 'JPY',
            'Amount'   => '1',
        ]));
        $this->assertTrue($priceField->getMoney()->equals(new Money(1, new Currency('JPY'))));
    }

    /**
     * @throws ValidationException
     */
    public function testSetTextValue()
    {
        $priceField = PriceField::create('Price');

        $priceField->setValue('2.00 NZD');
        $this->assertTrue($priceField->getMoney()->equals(
            new Money(200, new Currency('NZD'))));

        $priceField->setValue('200JPY');
        $this->assertTrue($priceField->getMoney()->equals(
            new Money(200, new Currency('JPY'))));
    }

    /**
     * @throws ValidationException
     */
    public function testSetInvalidValue()
    {
        $priceField = PriceField::create('Price');

        $this->expectException(ValidationException::class);
        $priceField->setValue('Hello world 123');
    }

    /**
     *
     */
    public function testSingleCurrencyAssumption()
    {
        $priceField = PriceField::create('Price');
        $priceField->setAllowedCurrencies(['NZD']);

        $priceField->setSubmittedValue([
            'Amount' => '2.00',
        ]);

        $this->assertTrue($priceField->getMoney()->equals(
            new Money(200, new Currency('NZD'))));
    }

    /**
     * @throws ValidationException
     */
    public function testSaveInto()
    {
        $priceField = PriceField::create('Price');

        // Set value to 200NZD
        $twoHundred = new Money(20000, new Currency('NZD'));
        $priceField->setValue($twoHundred);

        // Set fields
        $product = TestProduct::create();
        $this->assertFalse($product->Price->getMoney()->equals($twoHundred));
        $priceField->saveInto($product);
        $this->assertTrue($product->Price->getMoney()->equals($twoHundred));

        // Call setter
        $priceField->setName('SettablePrice');
        $this->assertNull($product->SettablePrice);
        $priceField->saveInto($product);
        $this->assertTrue($product->SettablePrice->getMoney()->equals($twoHundred));
    }

    /**
     *
     */
    public function testReadOnly()
    {
        $priceField = PriceField::create('Price');
        $this->assertFalse($priceField->getAmountField()->isReadonly());
        $this->assertFalse($priceField->getCurrencyField()->isReadonly());

        $priceField->setReadonly(true);
        $this->assertTrue($priceField->getAmountField()->isReadonly());
        $this->assertTrue($priceField->getCurrencyField()->isReadonly());

        $priceField->setReadonly(false);
        $this->assertFalse($priceField->getAmountField()->isReadonly());
        $this->assertFalse($priceField->getCurrencyField()->isReadonly());
    }

    /**
     *
     */
    public function testDisable()
    {
        $priceField = PriceField::create('Price');
        $this->assertFalse($priceField->getAmountField()->isDisabled());
        $this->assertFalse($priceField->getCurrencyField()->isDisabled());

        $priceField->setDisabled(true);
        $this->assertTrue($priceField->getAmountField()->isDisabled());
        $this->assertTrue($priceField->getCurrencyField()->isDisabled());

        $priceField->setDisabled(false);
        $this->assertFalse($priceField->getAmountField()->isDisabled());
        $this->assertFalse($priceField->getCurrencyField()->isDisabled());
    }

    /**
     *
     */
    public function testReadOnlyTransformation()
    {
        $priceField = PriceField::create('Price');
        $this->assertFalse($priceField->getAmountField()->isReadonly());
        $this->assertFalse($priceField->getCurrencyField()->isReadonly());

        $readOnlyField = $priceField->performReadonlyTransformation();
        $this->assertTrue($readOnlyField->getAmountField()->isReadonly());
        $this->assertTrue($readOnlyField->getCurrencyField()->isReadonly());
    }

    /**
     *
     */
    public function testSetForm()
    {
        $priceField = PriceField::create('Price');
        $form = Form::create(null, Form::DEFAULT_NAME, FieldList::create($priceField), FieldList::create());
        $this->assertSame($form, $priceField->getForm());
        $this->assertSame($form, $priceField->getAmountField()->getForm());
        $this->assertSame($form, $priceField->getCurrencyField()->getForm());

        $form2 = Form::create(null, Form::DEFAULT_NAME, FieldList::create(), FieldList::create());
        $priceField->setForm($form2);
        $this->assertSame($form2, $priceField->getForm());
        $this->assertSame($form2, $priceField->getAmountField()->getForm());
        $this->assertSame($form2, $priceField->getCurrencyField()->getForm());
    }
}

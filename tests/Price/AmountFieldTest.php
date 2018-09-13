<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Price;

use SilverStripe\Dev\SapphireTest;
use SwipeStripe\Price\AmountField;

/**
 * Class AmountFieldTest
 * @package SwipeStripe\Tests\Price
 */
class AmountFieldTest extends SapphireTest
{
    /**
     *
     */
    public function testEmptyValue()
    {
        $field = AmountField::create('Amount');
        $field->setSubmittedValue('');

        $this->assertSame('', $field->Value());
        $this->assertNull($field->dataValue());
    }

    /**
     *
     */
    public function testTextValue()
    {
        $field = AmountField::create('Amount');
        $field->setSubmittedValue('Foo');

        $this->assertSame('Foo', $field->Value());
        $this->assertNull($field->dataValue());
    }

    /**
     *
     */
    public function testIntegerValue()
    {
        $field = AmountField::create('Amount');
        $field->setSubmittedValue('123');

        $this->assertSame('123', $field->Value());
        $this->assertSame('123', $field->dataValue());
    }

    /**
     *
     */
    public function testFloatValue()
    {
        $field = AmountField::create('Amount');
        $field->setSubmittedValue('1.23');

        $this->assertSame('1.23', $field->Value());
        $this->assertSame('1.23', $field->dataValue());
    }

    /**
     *
     */
    public function testValueOverIntMax()
    {
        $field = AmountField::create('Amount');
        $field->setSubmittedValue('92358843092582304921849021584309583045');

        $this->assertSame('92358843092582304921849021584309583045', $field->Value());
        $this->assertSame('92358843092582304921849021584309583045', $field->dataValue());
    }
}

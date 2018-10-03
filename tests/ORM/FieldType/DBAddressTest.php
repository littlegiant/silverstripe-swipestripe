<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\ORM\FieldType;

use SilverStripe\Dev\SapphireTest;
use SwipeStripe\ORM\FieldType\DBAddress;

/**
 * Class DBAddressTest
 * @package SwipeStripe\Tests\ORM\FieldType
 */
class DBAddressTest extends SapphireTest
{
    const TEST_ADDRESS = [
        'Unit'     => '1A',
        'Street'   => '1 Test Street',
        'Suburb'   => 'Test Hill',
        'City'     => 'Test City',
        'Region'   => 'North Test',
        'Postcode' => '1111',
        'Country'  => 'Testland',
    ];

    /**
     *
     */
    public function testScaffoldedFormFieldTitle()
    {
        $dbAddress = DBAddress::create('Address');

        $this->assertSame('Address', $dbAddress->scaffoldFormField()->Title());
        $this->assertEmpty($dbAddress->scaffoldFormField('')->Title());
        $this->assertSame('Test', $dbAddress->scaffoldFormField('Test')->Title());
    }

    /**
     *
     */
    public function testEmpty()
    {
        $dbAddress = DBAddress::create();
        $this->assertTrue($dbAddress->Empty());

        $dbAddress->Unit = static::TEST_ADDRESS['Unit'];
        $this->assertFalse($dbAddress->Empty());
    }

    /**
     *
     */
    public function testCopyFrom()
    {
        $original = DBAddress::create('Original');
        $original->Unit = static::TEST_ADDRESS['Unit'];
        $original->Street = static::TEST_ADDRESS['Street'];
        $original->Suburb = static::TEST_ADDRESS['Suburb'];
        $original->City = static::TEST_ADDRESS['City'];
        $original->Region = static::TEST_ADDRESS['Region'];
        $original->Postcode = static::TEST_ADDRESS['Postcode'];
        $original->Country = static::TEST_ADDRESS['Country'];
        $this->assertSameAsTestAddress($original);

        $dbAddress = DBAddress::create('Address');
        $this->assertTrue($dbAddress->Empty());
        $dbAddress->copyFrom($original);
        $this->assertSameAsTestAddress($dbAddress);
    }

    /**
     *
     */
    public function testCopyFromArray()
    {
        $dbAddress = DBAddress::create('Address');
        $this->assertTrue($dbAddress->Empty());

        $dbAddress->copyFromArray(static::TEST_ADDRESS);
        $this->assertSameAsTestAddress($dbAddress);

        $prefix = 'Shipping';
        $prefixedArray = [];
        foreach (static::TEST_ADDRESS as $component => $value) {
            $prefixedArray[$prefix . $component] = $value;
        }

        $dbAddress = DBAddress::create('Address');
        $dbAddress->copyFromArray($prefixedArray, $prefix);
        $this->assertSameAsTestAddress($dbAddress);
    }

    /**
     * @param DBAddress $dbAddress
     */
    protected function assertSameAsTestAddress(DBAddress $dbAddress): void
    {
        $this->assertSame(static::TEST_ADDRESS['Unit'], $dbAddress->Unit);
        $this->assertSame(static::TEST_ADDRESS['Street'], $dbAddress->Street);
        $this->assertSame(static::TEST_ADDRESS['Suburb'], $dbAddress->Suburb);
        $this->assertSame(static::TEST_ADDRESS['City'], $dbAddress->City);
        $this->assertSame(static::TEST_ADDRESS['Region'], $dbAddress->Region);
        $this->assertSame(static::TEST_ADDRESS['Postcode'], $dbAddress->Postcode);
        $this->assertSame(static::TEST_ADDRESS['Country'], $dbAddress->Country);
    }
}

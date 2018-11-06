<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Address;

use SilverStripe\Dev\SapphireTest;
use SwipeStripe\Address\DBAddress;

/**
 * Class DBAddressTest
 * @package SwipeStripe\Tests\Address
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
}

<?php
declare(strict_types=1);

namespace SwipeStripe\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\FieldType\DBDatetime;

/**
 * Trait NeedsMockedTime
 * @package SwipeStripe\Tests
 * @mixin SapphireTest
 */
trait WaitsMockTime
{
    /**
     * @param int $seconds
     * @throws \Exception
     */
    protected function mockWait(int $seconds = 5): void
    {
        DBDatetime::set_mock_now(DBDatetime::now()->getTimestamp() + $seconds);
    }

    /**
     *
     */
    protected function tearDown()
    {
        parent::tearDown();

        DBDatetime::clear_mock_now();
    }
}

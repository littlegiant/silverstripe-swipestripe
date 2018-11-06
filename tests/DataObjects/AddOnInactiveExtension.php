<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\DataObjects;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataExtension;
use SwipeStripe\Order\OrderAddOn;
use SwipeStripe\Order\OrderItem\OrderItemAddOn;

/**
 * Class AddOnInactiveExtension
 * @package SwipeStripe\Tests\DataObjects
 * @property OrderAddOn|OrderItemAddOn|AddOnInactiveExtension $owner
 */
class AddOnInactiveExtension extends DataExtension implements TestOnly
{
    /**
     * @param bool $active
     */
    public function isActive(bool &$active): void
    {
        $active = false;
    }
}

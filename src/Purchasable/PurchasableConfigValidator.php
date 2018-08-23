<?php

namespace SwipeStripe\Purchasable;

use LittleGiant\SilverStripe\ConfigValidator\ClassConfigValidationResult;
use LittleGiant\SilverStripe\ConfigValidator\ClassConfigValidator;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config_ForClass;
use SilverStripe\Core\Extensible;
use SilverStripe\Versioned\Versioned;

/**
 * Class PurchasableConfigValidator
 * @package SwipeStripe\Purchasable
 */
class PurchasableConfigValidator implements ClassConfigValidator
{
    /**
     * @inheritDoc
     */
    public static function getConfigValidatedClasses()
    {
        return ClassInfo::implementorsOf(Purchasable::class);
    }

    /**
     * @inheritDoc
     */
    public static function validateClassConfig($className, Config_ForClass $config, ClassConfigValidationResult $result)
    {
        if (!Extensible::has_extension($className, Versioned::class)) {
            $result->addError('extensions', Purchasable::class . ' implementations must have the "Versioned" extension.');
        }
    }
}

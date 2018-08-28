<?php
declare(strict_types=1);

namespace SwipeStripe\Config;

use LittleGiant\SilverStripe\ConfigValidator\ClassConfigValidationResult;
use LittleGiant\SilverStripe\ConfigValidator\ClassConfigValidator;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config_ForClass;
use SilverStripe\Core\Extensible;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\Purchasable\PurchasableInterface;

/**
 * Class VersionedObjectsConfigValidator
 * @package SwipeStripe\Config
 */
final class VersionedObjectsConfigValidator implements ClassConfigValidator
{
    const INTERFACES = [
        PurchasableInterface::class,
    ];

    /**
     * @inheritDoc
     */
    public static function getConfigValidatedClasses()
    {
        return ClassInfo::implementorsOf(PurchasableInterface::class);
    }

    /**
     * @inheritDoc
     */
    public static function validateClassConfig($className, Config_ForClass $config, ClassConfigValidationResult $result)
    {
        if (!Extensible::has_extension($className, Versioned::class)) {
            $implInterfaces = PurchasableInterface::class;
            $versioned = Versioned::class;

            $result->addError('extensions', "Implementations of '{$implInterfaces}' must have the '{$versioned}' extension.");
        }
    }
}

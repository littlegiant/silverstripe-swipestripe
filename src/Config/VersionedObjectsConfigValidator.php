<?php

namespace SwipeStripe\Config;

use LittleGiant\SilverStripe\ConfigValidator\ClassConfigValidationResult;
use LittleGiant\SilverStripe\ConfigValidator\ClassConfigValidator;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config_ForClass;
use SilverStripe\Core\Extensible;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\AddOnInterface;
use SwipeStripe\Purchasable\PurchasableInterface;

/**
 * Class VersionedObjectsConfigValidator
 * @package SwipeStripe\Config
 */
final class VersionedObjectsConfigValidator implements ClassConfigValidator
{
    const INTERFACES = [
        AddOnInterface::class,
        PurchasableInterface::class,
    ];

    /**
     * @inheritDoc
     */
    public static function getConfigValidatedClasses()
    {
        $classes = [];
        foreach (static::INTERFACES as $interface) {
            $classes = array_merge($classes, ClassInfo::implementorsOf($interface));
        }

        return array_values($classes);
    }

    /**
     * @inheritDoc
     */
    public static function validateClassConfig($className, Config_ForClass $config, ClassConfigValidationResult $result)
    {
        if (!Extensible::has_extension($className, Versioned::class)) {
            $implInterfaces = implode("', '", array_filter(static::INTERFACES,
                function (string $interface) use ($className) {
                    return ClassInfo::classImplements($className, $interface);
                }));
            $versioned = Versioned::class;

            $result->addError('extensions', "Implementations of '{$implInterfaces}' must have the '{$versioned}' extension.");
        }
    }
}

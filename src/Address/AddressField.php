<?php
declare(strict_types=1);

namespace SwipeStripe\Address;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\TextField;
use SwipeStripe\Forms\Fields\CountryDropdownField;

/**
 * Class AddressField
 * @package SwipeStripe\Address
 */
class AddressField extends FieldGroup
{
    /**
     * @var array
     */
    private static $field_specs = [
        'Unit'     => TextField::class,
        'Street'   => TextField::class,
        'Suburb'   => TextField::class,
        'City'     => TextField::class,
        'Region'   => TextField::class,
        'Postcode' => TextField::class,
        'Country'  => CountryDropdownField::class,
    ];

    /**
     * @inheritDoc
     */
    public function __construct(string $name, ?string $title = null)
    {
        $fields = [];
        $injector = Injector::inst();

        /**
         * @var string $field
         * @var string|FormField $fieldType
         */
        foreach (static::config()->get('field_specs') as $fieldName => $fieldType) {
            $fields[] = $injector->create(
                $fieldType,
                $name . $fieldName,
                _t(self::class . ".{$fieldName}_TITLE", $fieldType::name_to_label($fieldName))
            );
        }

        parent::__construct($name, $fields);
        $this->setTitle($title ?? static::name_to_label($name));
    }
}

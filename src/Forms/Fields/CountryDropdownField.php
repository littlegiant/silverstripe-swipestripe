<?php
declare(strict_types=1);

namespace SwipeStripe\Forms\Fields;

use SilverStripe\Forms\DropdownField;
use SilverStripe\i18n\i18n;

/**
 * Class CountryDropdownField
 * @package SwipeStripe\Forms\Fields
 */
class CountryDropdownField extends DropdownField
{
    /**
     * @inheritDoc
     */
    public function __construct(string $name, ?string $title = null, $source = [], $value = null)
    {
        $source = empty($source)
            ? i18n::getData()->getCountries()
            : $source;

        parent::__construct($name, $title, $source, $value);
    }
}

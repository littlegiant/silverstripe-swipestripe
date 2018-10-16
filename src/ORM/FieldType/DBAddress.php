<?php
declare(strict_types=1);

namespace SwipeStripe\ORM\FieldType;

use SilverStripe\Forms\FieldGroup;
use SilverStripe\i18n\Data\Intl\IntlLocales;
use SilverStripe\ORM\FieldType\DBComposite;
use SwipeStripe\Forms\Fields\CountryDropdownField;

/**
 * Class DBAddress
 * @package SwipeStripe\ORM\FieldType
 * @property string $Unit
 * @property string $Street
 * @property string $Suburb
 * @property string $City
 * @property string $Region
 * @property string $Postcode
 * @property string $Country
 */
class DBAddress extends DBComposite
{
    const INJECTOR_SPEC = 'Address';

    /**
     * @var array
     */
    private static $composite_db = [
        'Unit'     => 'Varchar',
        'Street'   => 'Varchar',
        'Suburb'   => 'Varchar',
        'City'     => 'Varchar',
        'Region'   => 'Varchar',
        'Postcode' => 'Varchar',
        'Country'  => 'Varchar(2)',
    ];

    /**
     * @return string
     */
    public function Nice(): string
    {
        $address = '';

        if (!empty($this->Unit) || !empty($this->Street)) {
            $address .= trim("{$this->Unit} {$this->Street}") . ",\n";
        }

        if (!empty($this->Suburb)) {
            $address .= "{$this->Suburb},\n";
        }

        if (!empty($this->City)) {
            $address .= "{$this->City},\n";
        }

        if (!empty($this->Region)) {
            $address .= "{$this->Region},\n";
        }

        if (!empty($this->Country)) {
            $address .= IntlLocales::singleton()->countryName($this->Country) . ' ';
        }

        if (!empty($this->Postcode)) {
            $address .= $this->Postcode;
        }

        return rtrim($address);
    }

    /**
     * @return bool
     */
    public function Empty(): bool
    {
        foreach ($this->compositeDatabaseFields() as $field => $type) {
            if (!empty($this->getField($field))) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function scaffoldFormField($title = null, $params = null)
    {
        $fieldGroup = FieldGroup::create($title ?? FieldGroup::name_to_label($this->getName()));

        foreach ($this->compositeDatabaseFields() as $field => $type) {
            $title = _t(self::class . ".TITLE_{$field}", FieldGroup::name_to_label($field));
            $field = $field === 'Country'
                ? CountryDropdownField::create("{$this->getName()}Country", $title)
                : $this->dbObject($field)->scaffoldFormField($title);

            $fieldGroup->push($field);
        }

        return $fieldGroup;
    }
}

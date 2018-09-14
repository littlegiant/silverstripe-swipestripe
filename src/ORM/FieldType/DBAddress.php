<?php
declare(strict_types=1);

namespace SwipeStripe\ORM\FieldType;

use Dynamic\CountryDropdownField\Fields\CountryDropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\TextField;
use SilverStripe\i18n\Data\Intl\IntlLocales;
use SilverStripe\ORM\FieldType\DBComposite;
use SilverStripe\ORM\FieldType\DBVarchar;

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
    /**
     * @var array
     */
    private static $composite_db = [
        'Unit'     => DBVarchar::class,
        'Street'   => DBVarchar::class,
        'Suburb'   => DBVarchar::class,
        'City'     => DBVarchar::class,
        'Region'   => DBVarchar::class,
        'Postcode' => DBVarchar::class,
        'Country'  => DBVarchar::class,
    ];

    /**
     * @return string
     */
    public function Nice(): string
    {
        $countryName = IntlLocales::singleton()->countryName($this->Country);

        return <<<EOT
{$this->Unit} {$this->Street},
{$this->Suburb},
{$this->City},
{$this->Region},
{$countryName} {$this->Postcode}
EOT;
    }

    /**
     * @inheritDoc
     */
    public function scaffoldFormField($title = null, $params = null)
    {
        return FieldGroup::create([
            TextField::create("{$this->getName()}Unit", 'Unit'),
            TextField::create("{$this->getName()}Street", 'Street'),
            TextField::create("{$this->getName()}Suburb", 'Suburb'),
            TextField::create("{$this->getName()}City", 'City'),
            TextField::create("{$this->getName()}Region", 'Region'),
            TextField::create("{$this->getName()}Postcode", 'Post Code / Zip'),
            CountryDropdownField::create("{$this->getName()}Country", 'Country'),
        ]);
    }
}

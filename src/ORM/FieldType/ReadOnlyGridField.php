<?php
declare(strict_types=1);

namespace SwipeStripe\ORM\FieldType;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldImportButton;

/**
 * Class ReadOnlyGridField
 * @package SwipeStripe\ORM\FieldType
 */
class ReadOnlyGridField extends GridField
{
    /**
     * ReadOnlyGridField constructor.
     * @param GridField $original
     */
    public function __construct(GridField $original)
    {
        parent::__construct($original->getName(),
            $original->Title(),
            $original->getList(),
            $original->getConfig());
    }

    /**
     * @param FieldList $fieldList
     * @param string[]|null $fieldNames
     * @return FieldList
     */
    public static function replaceFields(FieldList $fieldList, ?array $fieldNames = null): FieldList
    {
        if ($fieldNames === null) {
            $fieldNames = [];

            foreach ($fieldList->dataFields() as $field) {
                if ($field instanceof GridField) {
                    $fieldNames[] = $field->getName();
                }
            }
        }

        foreach ($fieldNames as $fieldName) {
            static::replaceField($fieldList, $fieldName);
        }

        return $fieldList;
    }

    /**
     * @param FieldList $fieldList
     * @param string $fieldName
     * @return FieldList
     */
    public static function replaceField(FieldList $fieldList, string $fieldName): FieldList
    {
        $replacement = static::create($fieldList->dataFieldByName($fieldName));
        $fieldList->replaceField($fieldName, $replacement);

        return $fieldList;
    }

    /**
     * @inheritDoc
     */
    public function performReadonlyTransformation()
    {
        $this->getConfig()->removeComponentsByType([
            GridFieldAddExistingAutocompleter::class,
            GridFieldAddNewButton::class,
            GridFieldDeleteAction::class,
            GridFieldImportButton::class,
        ]);

        return $this;
    }
}

<?php
declare(strict_types=1);

namespace SwipeStripe;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SwipeStripe\ORM\FieldType\ReadOnlyGridField;

/**
 * Class CMSHelper
 * @package SwipeStripe
 */
class CMSHelper
{
    use Injectable;

    /**
     * @param FieldList $fieldList
     * @param array|null $fieldNames
     * @return FieldList
     */
    public function convertGridFieldsToReadOnly(FieldList $fieldList, ?array $fieldNames = null): FieldList
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
            $replacement = ReadOnlyGridField::create($fieldList->dataFieldByName($fieldName));
            $fieldList->replaceField($fieldName, $replacement);
        }

        return $fieldList;
    }
}

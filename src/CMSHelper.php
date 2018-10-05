<?php
declare(strict_types=1);

namespace SwipeStripe;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SwipeStripe\Forms\Fields\ReadOnlyGridField;

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
            /** @var GridField $originalField */
            $originalField = $fieldList->dataFieldByName($fieldName);
            $replacement = $this->getReadOnlyGridField($originalField);

            $fieldList->replaceField($fieldName, $replacement);
        }

        return $fieldList;
    }

    /**
     * @param GridField $original
     * @return GridField
     */
    public function getReadOnlyGridField(GridField $original): GridField
    {
        $injector = Injector::inst();
        $originalClass = get_class($original);

        $service = $injector->has("{$originalClass}.ReadOnly")
            ? "{$originalClass}.ReadOnly"
            : ReadOnlyGridField::class;

        return $injector->create($service, $original);
    }

    /**
     * @param FieldList $fieldList
     * @param string $moveBefore
     * @param string $tabToMove
     * @return FieldList
     */
    public function moveTabBefore(FieldList $fieldList, string $moveBefore, string $tabToMove): FieldList
    {
        $tab = $fieldList->findOrMakeTab($tabToMove);

        $fieldList->removeByName($tab->getName());
        $fieldList->insertBefore($moveBefore, $tab);

        return $fieldList;
    }
}

<?php
declare(strict_types=1);

namespace SwipeStripe;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldViewButton;

/**
 * Trait CMSHelper
 * @package SwipeStripe
 */
trait CMSHelper
{
    /**
     * @param FieldList $fieldList
     * @param array|null $fieldNames
     * @return FieldList
     */
    public function addViewButtonToGridFields(FieldList $fieldList, ?array $fieldNames = null): FieldList
    {
        /** @var GridField[] $gridFields */
        $gridFields = [];
        if ($fieldNames === null) {
            foreach ($fieldList->dataFields() as $field) {
                if ($field instanceof GridField) {
                    $gridFields[] = $field;
                }
            }
        } else {
            foreach ($fieldNames as $name) {
                $gridFields[] = $fieldList->dataFieldByName($name);
            }
        }

        foreach ($gridFields as $field) {
            $config = $field->getConfig();

            if ($config->getComponentByType(GridFieldEditButton::class) !== null &&
                $config->getComponentByType(GridFieldViewButton::class) === null) {

                $config->addComponent(new GridFieldViewButton());
            }
        }

        return $fieldList;
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

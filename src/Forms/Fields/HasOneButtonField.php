<?php
declare(strict_types=1);

namespace SwipeStripe\Forms\Fields;

use SilverShop\HasOneField\GridFieldHasOneButtonRow;
use SilverShop\HasOneField\GridFieldHasOneEditButton;
use SilverShop\HasOneField\GridFieldSummaryField;
use SilverShop\HasOneField\HasOneButtonField as BaseHasOneButtonField;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\ORM\DataObject;

/**
 * Class HasOneButtonField
 * @package SwipeStripe\Forms\Fields
 */
class HasOneButtonField extends BaseHasOneButtonField
{
    /**
     * @inheritDoc
     */
    public function __construct(
        DataObject $parent,
        string $relationName,
        ?string $fieldName = null,
        ?string $title = null
    ) {
        parent::__construct($parent, $relationName, $fieldName, $title);

        if (property_exists($this, 'readonlyComponents')) {
            $this->readonlyComponents = array_merge($this->readonlyComponents, [
                GridFieldHasOneButtonRow::class,
                GridFieldSummaryField::class,
                GridFieldHasOneEditButton::class,
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function performReadonlyTransformation()
    {
        $readOnly = parent::performReadonlyTransformation();

        if ($readOnly instanceof GridField) {
            $detailForm = $readOnly->getConfig()->getComponentByType(GridFieldDetailForm::class);

            if ($detailForm instanceof GridFieldDetailForm) {
                $detailForm->setItemEditFormCallback(function (Form $form) {
                    $form->makeReadonly();
                });
            }
        }

        return $readOnly;
    }
}

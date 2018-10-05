<?php
declare(strict_types=1);

namespace SwipeStripe\Forms\Fields;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;

/**
 * Class GridFieldDetailForm_ItemRequestExtension
 * @package SwipeStripe\Forms\Fields
 * @property GridFieldDetailForm_ItemRequest $owner
 */
class GridFieldDetailForm_ItemRequestExtension extends Extension
{
    /**
     * @param Form $form
     */
    public function updateItemEditForm(Form $form): void
    {
        if ($this->owner->getGridField()->isReadonly()) {
            $form->makeReadonly();
        }
    }
}

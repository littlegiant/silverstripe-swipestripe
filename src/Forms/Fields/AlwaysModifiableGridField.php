<?php
declare(strict_types=1);

namespace SwipeStripe\Forms\Fields;

use SilverStripe\Forms\GridField\GridField;

/**
 * Class AlwaysModifiableGridField
 * @package SwipeStripe\Forms\Fields
 */
class AlwaysModifiableGridField extends GridField
{
    /**
     * @inheritDoc
     */
    public function setReadonly($readonly)
    {
        return parent::setReadonly(false);
    }

    /**
     * @inheritDoc
     */
    public function performReadonlyTransformation()
    {
        return $this;
    }
}

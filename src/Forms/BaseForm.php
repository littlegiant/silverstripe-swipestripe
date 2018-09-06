<?php
declare(strict_types=1);

namespace SwipeStripe\Forms;

use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\Validator;

/**
 * Class BaseForm
 * @package SwipeStripe\Forms
 */
abstract class BaseForm extends Form
{
    /**
     * BaseForm constructor.
     * @param null|RequestHandler $controller
     * @param null|string $name
     * @param null|Validator $validator
     */
    public function __construct(?RequestHandler $controller = null, ?string $name = null, ?Validator $validator = null)
    {
        $fields = $this->buildFields();
        $actions = $this->buildActions();

        $this->extend('updateFields', $fields);
        $this->extend('updateActions', $actions);
        $this->setHTMLID($this->getFormId());

        parent::__construct($controller, $name ?? static::DEFAULT_NAME, $fields, $actions, $validator);
    }

    /**
     * @return FieldList
     */
    abstract protected function buildFields(): FieldList;

    /**
     * @return FieldList
     */
    abstract protected function buildActions(): FieldList;

    /**
     * @return string The form's HTML ID.
     */
    public function getFormId(): string
    {
        return ClassInfo::shortName(static::class);
    }
}

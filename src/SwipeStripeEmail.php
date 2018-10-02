<?php
declare(strict_types=1);

namespace SwipeStripe;

use SilverStripe\Control\Email\Email;

/**
 * Class SwipeStripeEmail
 * @package SwipeStripe
 */
class SwipeStripeEmail extends Email
{
    /**
     * @config
     * @var string|array|null
     */
    private static $default_sender;

    /**
     * @see Email::$HTMLTemplate (is private, so can't )
     * @var string|null
     */
    protected $HTMLTemplateOverride;

    /**
     * @inheritDoc
     * TODO - add CMS editable (siteconfig?) default sender of store emails
     */
    public function __construct(
        $from = null,
        $to = null,
        ?string $subject = null,
        ?string $body = null,
        $cc = null,
        $bcc = null,
        ?string $returnPath = null
    ) {
        $from = $from ?? static::config()->get('default_sender');

        parent::__construct($from, $to, $subject, $body, $cc, $bcc, $returnPath);
    }

    /**
     * @inheritDoc
     */
    public function getHTMLTemplate()
    {
        if ($this->HTMLTemplateOverride) {
            return $this->HTMLTemplateOverride;
        }

        return $this->getViewerTemplates();
    }

    /**
     * @inheritDoc
     * @param string|array $template
     */
    public function setHTMLTemplate($template)
    {
        if (is_string($template) && substr($template, -3) == '.ss') {
            $template = substr($template, 0, -3);
        }

        $this->HTMLTemplateOverride = $template;

        return $this;
    }
}

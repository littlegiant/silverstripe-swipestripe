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
}

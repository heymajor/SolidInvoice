<?php

declare(strict_types=1);

/*
 * This file is part of SolidInvoice project.
 *
 * (c) 2013-2017 Pierre du Plessis <info@customscripts.co.za>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\MailerBundle\Decorator;

use SolidInvoice\MailerBundle\Event\MessageEvent;
use SolidInvoice\MailerBundle\Template\TextTemplateMessage;
use SolidInvoice\SettingsBundle\SystemConfig;
use Symfony\Component\Templating\EngineInterface;

class TextTemplateDecorator implements MessageDecorator, VerificationMessageDecorator
{
    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var SystemConfig
     */
    private $systemConfig;

    public function __construct(SystemConfig $systemConfig, EngineInterface $engine)
    {
        $this->engine = $engine;
        $this->systemConfig = $systemConfig;
    }

    /**
     * @param MessageEvent $event
     *
     * @throws \RuntimeException
     */
    public function decorate(MessageEvent $event): void
    {
        /** @var TextTemplateMessage|\Swift_Message $message */
        $message = $event->getMessage();

        $template = $message->getTextTemplate();

        $format = $this->systemConfig->get('email/format');

        $method = 'text' === $format ? 'setBody' : 'addPart';

        $message->$method($this->engine->render($template->getTemplate(), array_merge($event->getContext()->toArray(), $template->getParameters())), 'text/plain');
    }

    public function shouldDecorate(MessageEvent $event): bool
    {
        return $event->getMessage() instanceof TextTemplateMessage && \in_array($this->systemConfig->get('email/format'), ['text', 'both'], true);
    }
}

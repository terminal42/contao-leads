<?php

declare(strict_types=1);

/*
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2018, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\LeadsBundle\Util;

use Contao\StringUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Terminal42\LeadsBundle\Event\ExportFileEvent;

class ExportFile
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getFilenameForConfig(\stdClass $config): string
    {
        $event = $this->dispatchEvent($config);

        // An event listener decided to override the filename (propagation stopped)
        if ($filename = $event->getFilename()) {
            return $filename;
        }

        if (empty($config->filename)) {
            return $this->getUniqueFilename($config);
        }

        return StringUtil::parseSimpleTokens($config->filename, $event->getTokens());
    }

    private function dispatchEvent(\stdClass $config): ExportFileEvent
    {
        $event = new ExportFileEvent(
            $config,
            [
                'time' => \Date::parse($GLOBALS['TL_CONFIG']['timeFormat']),
                'date' => \Date::parse($GLOBALS['TL_CONFIG']['dateFormat']),
                'datim' => \Date::parse($GLOBALS['TL_CONFIG']['datimFormat']),
            ]
        );

        return $this->eventDispatcher->dispatch($event, 'terminal42_leads.export_file');
    }

    private function getUniqueFilename(\stdClass $config)
    {
        $filename = 'export_'.md5(uniqid('', false));

        if ($config->type) {
            $filename .= '.'.$config->type;
        }

        return $filename;
    }
}

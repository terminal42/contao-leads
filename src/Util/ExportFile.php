<?php

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
                'time'  => \Date::parse($GLOBALS['TL_CONFIG']['timeFormat']),
                'date'  => \Date::parse($GLOBALS['TL_CONFIG']['dateFormat']),
                'datim' => \Date::parse($GLOBALS['TL_CONFIG']['datimFormat']),
            ]
        );

        return $this->eventDispatcher->dispatch('terminal42_leads.export_file', $event);
    }

    private function getUniqueFilename(\stdClass $config)
    {
        $filename = 'export_' . md5(uniqid('', false));

        if ($config->type) {
            $filename .= '.' . $config->type;
        }

        return $filename;
    }
}

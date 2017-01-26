<?php

namespace Terminal42\LeadsBundle\EventListener;

use Contao\DataContainer;
use Symfony\Component\Filesystem\Filesystem;

class LeadExportListener
{
    /**
     * Validates the given path exists.
     *
     * @param mixed         $value
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function onSaveTargetPath($value, DataContainer $dc)
    {
        $value = rtrim($value, '/\\');
        $folder = $value;

        // Path not starting with slash means it's relative to Contao root
        if ('/' !== substr($folder, 0, 1)) {
            $folder = TL_ROOT.'/'.$folder;
        }

        if (!is_dir($folder)) {
            throw new \InvalidArgumentException($GLOBALS['TL_LANG']['tl_lead_export']['invalidTargetPath']);
        }

        return $value;
    }
}

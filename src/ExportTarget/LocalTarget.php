<?php

namespace Terminal42\LeadsBundle\ExportTarget;

use Contao\File;
use Contao\System;

class LocalTarget implements TargetInterface
{
    /**
     * Send the export data to target and return true on success
     *
     * @param File  $file
     * @param array $config
     *
     * @return bool
     */
    public function send(File $file, array $config)
    {
        if (!is_dir($config['targetPath'])) {
            System::log(
                sprintf('The target path "%s" does not exist or is not a directory', $config['targetPath']),
                __METHOD__,
                TL_ERROR
            );

            return false;
        }

        return copy(TL_ROOT.'/'.$file->path, $config['targetPath'].'/'.$file->name);
    }
}

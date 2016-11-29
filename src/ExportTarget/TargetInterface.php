<?php

namespace Terminal42\LeadsBundle\ExportTarget;

use Contao\File;

interface TargetInterface
{
    /**
     * Send the export data to target and return true on success
     *
     * @param File  $file
     * @param array $config
     *
     * @return bool
     */
    public function send(File $file, array $config);
}

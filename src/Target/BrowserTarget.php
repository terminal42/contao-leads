<?php

namespace Terminal42\LeadsBundle\Target;

use Contao\File;

class BrowserTarget implements TargetInterface
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
        $file->sendToBrowser();

        return true;
    }
}

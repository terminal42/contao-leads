<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\LeadsBundle\Exporter\Utils;

class File
{
    /**
     * Get the filename from a database config.
     *
     * @param   \Database\Result $config
     * @return  string
     */
    public static function getName($config)
    {
        if ($config->filename == '') {

            $filename = 'export_' . md5(uniqid());

            if ($config->type) {

                $filename .= '.' . $config->type;
            }

            return $filename;
        }

        $tokens = array
        (
            'time'  => \Date::parse($GLOBALS['TL_CONFIG']['timeFormat']),
            'date'  => \Date::parse($GLOBALS['TL_CONFIG']['dateFormat']),
            'datim' => \Date::parse($GLOBALS['TL_CONFIG']['datimFormat']),
        );

        // Add custom logic
        if (isset($GLOBALS['TL_HOOKS']['getLeadsFilenameTokens']) && is_array($GLOBALS['TL_HOOKS']['getLeadsFilenameTokens'])) {
            foreach ($GLOBALS['TL_HOOKS']['getLeadsFilenameTokens'] as $callback) {
                if (is_array($callback)) {
                    $tokens = \System::importStatic($callback[0])->$callback[1]($tokens, $config);
                } elseif (is_callable($callback)) {
                    $tokens = $callback($tokens, $config);
                }
            }
        }

        return \String::parseSimpleTokens($config->filename, $tokens);
    }
}

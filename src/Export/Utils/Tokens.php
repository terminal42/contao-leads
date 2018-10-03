<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\LeadsBundle\Export\Utils;

use Haste\Haste;

class Tokens
{
    /**
     * Recursively replace simple tokens and insert tags
     *
     * @param string $text
     * @param array  $tokens
     *
     * @return  string
     */
    public static function recursiveReplaceTokensAndTags($text, $tokens)
    {
        // Must decode, tokens could be encoded
        $text = \StringUtil::decodeEntities($text);

        // Replace all opening and closing tags with a hash so they don't get stripped
        // by parseSimpleTokens()
        $hash                = md5($text);
        $openTagReplacement  = 'LEADS-TAG-OPEN-' . $hash;
        $closeTagReplacement = 'LEADS-TAG-CLOSE-' . $hash;
        $original            = array('<', '>');
        $replacement         = array($openTagReplacement, $closeTagReplacement);
        $text                = str_replace($original, $replacement, $text);


        // first parse the tokens as they might have if-else clauses
        $buffer = \StringUtil::parseSimpleTokens($text, $tokens);

        // Restore tags
        $buffer = str_replace($replacement, $original, $buffer);

        // Replace the Insert Tags
        $buffer = Haste::getInstance()->call('replaceInsertTags', array($buffer, false));

        // Check if the Insert Tags have returned a Simple Token or an Insert Tag to parse
        if (
            (strpos($buffer, '##') !== false
                || strpos($buffer, '{{') !== false
            )
            && $buffer != $text
        ) {
            $buffer = static::recursiveReplaceTokensAndTags($buffer, $tokens);
        }

        $buffer = \StringUtil::restoreBasicEntities($buffer);

        return $buffer;
    }
}

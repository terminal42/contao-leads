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

namespace Terminal42\LeadsBundle\EventListener;

use Contao\System;

class FormDataListener
{
    /**
     * Process data submitted through the form generator.
     *
     * @param array $arrPost
     * @param array $arrForm
     * @param array $arrFiles
     */
    public function onProcessFormData(&$arrPost, &$arrForm, &$arrFiles): void
    {
        if ($arrForm['leadEnabled']) {
            $time = time();

            $intLead = \Database::getInstance()->prepare('
                INSERT INTO tl_lead (tstamp,created,language,form_id,master_id,member_id,post_data) VALUES (?,?,?,?,?,?,?)
            ')->execute(
                $time,
                $time,
                $GLOBALS['TL_LANGUAGE'],
                $arrForm['id'],
                ($arrForm['leadMaster'] ?: $arrForm['id']),
                (FE_USER_LOGGED_IN === true ? \FrontendUser::getInstance()->id : 0),
                serialize($arrPost)
            )->insertId;

            // Fetch master form fields
            if ($arrForm['leadMaster'] > 0) {
                $objFields = \Database::getInstance()
                                      ->prepare("SELECT f2.*, f1.id AS master_id, f1.name AS postName FROM tl_form_field f1 LEFT JOIN tl_form_field f2 ON f1.leadStore=f2.id WHERE f1.pid=? AND f1.leadStore>0 AND f2.leadStore='1' AND f1.invisible='' ORDER BY f2.sorting")
                                      ->execute($arrForm['id'])
                ;
            } else {
                $objFields = \Database::getInstance()
                                      ->prepare("SELECT *, id AS master_id, name AS postName FROM tl_form_field WHERE pid=? AND leadStore='1' AND invisible='' ORDER BY sorting")
                                      ->execute($arrForm['id'])
                ;
            }

            while ($objFields->next()) {
                $arrSet = [];

                // Regular data
                if (isset($arrPost[$objFields->postName])) {
                    $varValue = $this->prepareValue($arrPost[$objFields->postName], $objFields);
                    $varLabel = $this->prepareLabel($varValue, $objFields);

                    $arrSet = [
                        'pid'       => $intLead,
                        'sorting'   => $objFields->sorting,
                        'tstamp'    => $time,
                        'master_id' => $objFields->master_id,
                        'field_id'  => $objFields->id,
                        'name'      => $objFields->name,
                        'value'     => $varValue,
                        'label'     => $varLabel,
                    ];
                }

                // Files
                if (isset($arrFiles[$objFields->postName]) && $arrFiles[$objFields->postName]['uploaded']) {
                    $varValue = $this->prepareValue($arrFiles[$objFields->postName], $objFields);
                    $varLabel = $this->prepareLabel($varValue, $objFields);

                    $arrSet = [
                        'pid'       => $intLead,
                        'sorting'   => $objFields->sorting,
                        'tstamp'    => $time,
                        'master_id' => $objFields->master_id,
                        'field_id'  => $objFields->id,
                        'name'      => $objFields->name,
                        'value'     => $varValue,
                        'label'     => $varLabel,
                    ];
                }

                if (!empty($arrSet)) {
                    // HOOK: add custom logic
                    if (isset($GLOBALS['TL_HOOKS']['modifyLeadsDataOnStore']) && \is_array($GLOBALS['TL_HOOKS']['modifyLeadsDataOnStore'])) {
                        foreach ($GLOBALS['TL_HOOKS']['modifyLeadsDataOnStore'] as $callback) {
                            $objCallback = System::importStatic($callback[0]);
                            $objCallback->{$callback[1]}($arrPost, $arrForm, $arrFiles, $intLead, $objFields, $arrSet);
                        }
                    }

                    \Database::getInstance()->prepare('INSERT INTO tl_lead_data %s')->set($arrSet)->execute();
                }
            }

            // HOOK: add custom logic
            if (isset($GLOBALS['TL_HOOKS']['storeLeadsData']) && \is_array($GLOBALS['TL_HOOKS']['storeLeadsData'])) {
                foreach ($GLOBALS['TL_HOOKS']['storeLeadsData'] as $callback) {
                    $objCallback = System::importStatic($callback[0]);
                    $objCallback->{$callback[1]}($arrPost, $arrForm, $arrFiles, $intLead, $objFields);
                }
            }
        }
    }

    /**
     * Prepare a form value for storage in lead table.
     *
     * @param \Database\Result|object $objField
     *
     * @return array|int
     */
    private function prepareValue($varValue, $objField)
    {
        // File upload
        if ('upload' === $objField->type) {
            return $varValue['uuid'];
        }

        // Run for all values in an array
        if (\is_array($varValue)) {
            foreach ($varValue as $k => $v) {
                $varValue[$k] = $this->prepareValue($v, $objField);
            }

            return $varValue;
        }

        return $this->convertRgxp($varValue, $objField->rgxp);
    }

    /**
     * Get the label for a form value to store in lead table.
     *
     * @param \Database\Result|object $objField
     */
    private function prepareLabel($varValue, $objField)
    {
        // Run for all values in an array
        if (\is_array($varValue)) {
            foreach ($varValue as $k => $v) {
                $varValue[$k] = $this->prepareLabel($v, $objField);
            }

            return $varValue;
        }

        // File upload
        if ('upload' === $objField->type) {
            $objFile = \FilesModel::findByUuid($varValue);

            if (null !== $objFile) {
                return $objFile->path;
            }
        }

        $varValue = $this->convertRgxp($varValue, $objField->rgxp);

        if (!empty($objField->options)) {
            $arrOptions = deserialize($objField->options, true);

            foreach ($arrOptions as $arrOption) {
                if ($arrOption['value'] === $varValue && '' !== $arrOption['label']) {
                    $varValue = $arrOption['label'];
                }
            }
        }

        return $varValue;
    }

    /**
     * @param string $value
     * @param string $rgxp
     *
     * @return string
     */
    private function convertRgxp($value, $rgxp)
    {
        // Convert date formats into timestamps
        if (!empty($value)
            && \in_array($rgxp, ['date', 'time', 'datim'], true)
            && \Validator::{'is'.ucfirst($rgxp)}($value)
        ) {
            $format = \Date::{'getNumeric'.ucfirst($rgxp).'Format'}();
            $date = new \Date($value, $format);

            return (string) $date->tstamp;
        }

        return $value;
    }
}

<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\BackendUser;
use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Terminal42\LeadsBundle\DataTransformer\DataTransformerFactory;
use Terminal42\LeadsBundle\Exporter\ExporterFactory;
use Terminal42\LeadsBundle\Model\Lead;

class LeadExportListener
{
    /**
     * @var ExporterFactory
     */
    private $exportFactory;

    /**
     * @var DataTransformerFactory
     */
    private $dataTransformers;

    public function __construct(ExporterFactory $exportFactory, DataTransformerFactory $dataTransformers)
    {
        $this->exportFactory = $exportFactory;
        $this->dataTransformers = $dataTransformers;
    }

    public function onLoadCallback(DataContainer $dc): void
    {
        $this->checkPermission();
        $this->updatePalette($dc);
        $this->loadJs();
    }

    public function onChildRecordCallback(array $row): string
    {
        return '<div>'.$row['name'].'</div>';
    }

    public function onTypeOptionsCallback()
    {
        $options = [];

        foreach ($this->exportFactory->getServices() as $export) {
            $options[$export->getType()] = $export->getLabel();
        }

        return $options;
    }

    /**
     * Validates the given path exists.
     */
    public function onSaveTargetPath($value)
    {
        $value = rtrim($value, '/\\');
        $folder = $value;

        // Path not starting with slash means it's relative to Contao root
        if (0 === strpos($folder, '/')) {
            $folder = TL_ROOT.'/'.$folder;
        }

        if (!is_dir($folder)) {
            throw new \InvalidArgumentException($GLOBALS['TL_LANG']['tl_lead_export']['invalidTargetPath']);
        }

        return $value;
    }

    /**
     * Load the lead fields.
     *
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function onFieldsLoadCallback($varValue, $dc = null)
    {
        $arrFields = StringUtil::deserialize($varValue, true);

        // Load the form fields
        if (empty($arrFields) && $dc->id) {
            $arrFields = array_values(Lead::getSystemColumns());

            $objFields = Database::getInstance()->prepare(
                "SELECT * FROM tl_form_field WHERE leadStore!='' AND pid=(SELECT pid FROM tl_lead_export WHERE id=?)"
            )->execute($dc->id);

            while ($objFields->next()) {
                $arrFields[] = [
                    'field' => $objFields->id,
                    'name' => '',
                    'value' => 'all',
                    'format' => 'raw',
                ];
            }
        }

        return serialize($arrFields);
    }

    /**
     * Get the export fields as array.
     *
     * @return array
     */
    public function onExportOptionsCallback()
    {
        if (!\Input::get('id')) {
            return [];
        }

        $arrFields = [];

        foreach (Lead::getSystemColumns() as $k => $column) {
            $arrFields[$k] = $column['name'];
        }

        $objFields = Database::getInstance()
            ->prepare("SELECT * FROM tl_form_field WHERE leadStore!='' AND pid=(SELECT pid FROM tl_lead_export WHERE id=?)")
            ->execute(Input::get('id'))
        ;

        while ($objFields->next()) {
            $strLabel = $objFields->name;

            // Use the field label
            if ('' !== $objFields->label) {
                $strLabel = $objFields->label.' ['.$objFields->name.']';
            }

            $arrFields[$objFields->id] = $strLabel;
        }

        return $arrFields;
    }

    public function onFormatOptionsCallback()
    {
        $options = [];

        foreach ($this->dataTransformers->getServices() as $dataTransformer) {
            $options[$dataTransformer->getType()] = $dataTransformer->getLabel();
        }

        return $options;
    }

    /**
     * Check permissions to edit table.
     */
    private function checkPermission(): void
    {
        $user = BackendUser::getInstance();

        if (!$user->isAdmin && !$user->canEditFieldsOf('tl_lead_export')) {
            System::log('Not enough permissions to access leads export ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
            Controller::redirect('contao/main.php?act=error');
        }
    }

    /**
     * Update the palette depending on the export type.
     *
     * @param DataContainer $dc
     */
    private function updatePalette($dc = null): void
    {
        if (!$dc->id) {
            return;
        }

        $objRecord = Database::getInstance()->prepare(
            'SELECT * FROM tl_lead_export WHERE id=?'
        )->execute($dc->id);

        if (!$objRecord->export || 'all' === $objRecord->export) {
            return;
        }

        $strPalette = $objRecord->type ?: 'default';
        $GLOBALS['TL_DCA']['tl_lead_export']['palettes'][$strPalette] = str_replace(
            'export',
            'export,'.($GLOBALS['TL_DCA']['tl_lead_export']['subpalettes']['export'] ?? ''),
            $GLOBALS['TL_DCA']['tl_lead_export']['palettes'][$strPalette]
        );
    }

    /**
     * Loads JS.
     */
    private function loadJs()
    {
        $GLOBALS['TL_JAVASCRIPT'][] = $GLOBALS['BE_MOD']['leads']['lead']['javascript'];
    }
}

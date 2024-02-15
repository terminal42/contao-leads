<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\UploadableWidgetInterface;
use Contao\Widget;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback('tl_form_field', 'config.onload')]
class FormFieldConfigListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function __invoke(DataContainer $dc): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request || !$dc->currentPid) {
            return;
        }

        $mainFormId = $this->getMainFormId($dc->currentPid);

        if (null === $mainFormId) {
            unset($GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore']);

            return;
        }

        $types = $this->getSubmittedFields($dc->currentPid);

        if (empty($types)) {
            return;
        }

        $pm = PaletteManipulator::create()->addField('leadStore', 'type');

        // We have to check a prefix as the palette name can also refer to the
        // subpalette, for example "rgxp" field of text field could make it "text" but
        // also "textdigit" or "textcustom".
        foreach (array_keys($GLOBALS['TL_DCA']['tl_form_field']['palettes']) as $k) {
            foreach ($types as $type) {
                if (str_starts_with($k, (string) $type)) {
                    $pm->applyToPalette($k, 'tl_form_field');
                    break;
                }
            }
        }

        // This form is a main form
        if (0 === $mainFormId) {
            $GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore']['options'] = ['1' => $GLOBALS['TL_LANG']['MSC']['yes']];
            $GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore']['eval']['blankOptionLabel'] = $GLOBALS['TL_LANG']['MSC']['no'];

            return;
        }

        $GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore']['options_callback'] = fn (DataContainer $dc) => $this->getMainFormFields($request->query->get('act', ''), (int) $dc->id, $mainFormId);
        $GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['eval']['tl_class'] = 'w50';
    }

    private function getMainFormFields(string $action, int $id, int $mainFormId): array
    {
        if ('overrideAll' === $action) {
            return [];
        }

        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('id, name, label')
            ->from('tl_form_field')
            ->where("leadStore='1'")
            ->andWhere("name!=''")
            ->andWhere('pid=:mainId')
            ->setParameter('mainId', $mainFormId)
            ->orderBy('sorting')
        ;

        if ('edit' === $action || 'editAll' === $action) {
            $qb
                ->andWhere('id NOT IN (SELECT leadStore FROM tl_form_field WHERE pid=(SELECT pid FROM tl_form_field WHERE id=:id) AND id!=:id)')
                ->setParameter('id', $id)
            ;
        }

        $options = [];

        foreach ($qb->fetchAllAssociative() as $field) {
            $options[$field['id']] = empty($field['label']) ? $field['name'] : sprintf('%s (%s)', $field['label'], $field['name']);
        }

        return $options;
    }

    private function getMainFormId(int $id): int|null
    {
        $form = $this->connection->fetchAssociative('SELECT leadEnabled, leadMain FROM tl_form WHERE id=?', [$id]);

        if (false === $form || !$form['leadEnabled']) {
            return null;
        }

        return (int) $form['leadMain'];
    }

    private function getSubmittedFields(int $id): array
    {
        $fields = $this->connection->fetchAllAssociative('SELECT * FROM tl_form_field WHERE pid=?', [$id]);

        $types = [];

        foreach ($fields as $field) {
            if ($this->isWidgetSubmitted($field)) {
                $types[] = $field['type'];
            }
        }

        return array_unique($types);
    }

    private function isWidgetSubmitted(array $field): bool
    {
        $className = $GLOBALS['TL_FFL'][$field['type']] ?? null;

        if (null === $className || !class_exists($className)) {
            return false;
        }

        /** @var Widget $widget */
        $widget = new $className($field);
        $widget->required = (bool) $field['mandatory'];

        return $widget->submitInput() || $widget instanceof UploadableWidgetInterface;
    }
}

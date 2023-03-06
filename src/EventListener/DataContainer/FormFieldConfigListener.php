<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Widget;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback('tl_form_field', 'config.onload')]
class FormFieldConfigListener
{
    public function __construct(private readonly Connection $connection, private readonly RequestStack $requestStack)
    {
    }

    public function __invoke(DataContainer $dc): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$dc->id || null === $request || null === ($action = $request->query->get('act'))) {
            return;
        }

        $mainFormId = $this->getMainFormId($action, (int) $dc->id);

        if (null === $mainFormId) {
            unset($GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore']);

            return;
        }

        $types = $this->getSubmittedFields($action, (int) $dc->id);

        if (empty($types)) {
            return;
        }

        $pm = PaletteManipulator::create()->addField('leadStore', 'type');

        // We have to check a prefix as the palette name can also refer to the subpalette,
        // for example "rgxp" field of text field could make it "text" but also "textdigit" or "textcustom".
        foreach ($GLOBALS['TL_DCA']['tl_form_field']['palettes'] as $k => $v) {
            foreach ($types as $type) {
                if (str_starts_with($k, $type)) {
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

        $GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore']['options_callback'] = fn (DataContainer $dc) => $this->getMainFormFields($action, (int) $dc->id, $mainFormId);
        $GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['eval']['tl_class'] = 'w50';
    }

    private function getMainFormFields(string $action, int $id, int $mainFormId): array
    {
        if ('edit' !== $action && 'editAll' !== $action) {
            return [];
        }

        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('id, name, label')
            ->from('tl_form_field')
            ->where("leadStore='1'")
            ->andWhere("name!=''")
            ->andWhere('pid=:mainId')
            ->andWhere('id NOT IN (SELECT leadStore FROM tl_form_field WHERE pid=(SELECT pid FROM tl_form_field WHERE id=:id) AND id!=:id)')
            ->setParameters(['mainId' => $mainFormId, 'id' => $id])
            ->orderBy('sorting')
        ;

        $options = [];

        foreach ($qb->fetchAllAssociative() as $field) {
            $options[$field['id']] = empty($field['label']) ? $field['name'] : sprintf('%s (%s)', $field['label'], $field['name']);
        }

        return $options;
    }

    private function getMainFormId(string $action, int $id): int|null
    {
        switch ($action) {
            case 'edit':
                $form = $this->connection->fetchAssociative(
                    'SELECT leadEnabled, leadMain FROM tl_form WHERE id=(SELECT pid FROM tl_form_field WHERE id=?)',
                    [$id]
                );
                break;

            case 'editAll':
            case 'overrideAll':
                $form = $this->connection->fetchAssociative('SELECT leadEnabled, leadMain FROM tl_form WHERE id=?', [$id]);
                break;

            default:
                return null;
        }

        if (false === $form || !$form['leadEnabled']) {
            return null;
        }

        return (int) $form['leadMain'];
    }

    private function getSubmittedFields(string $action, int $id): array
    {
        switch ($action) {
            case 'edit':
                $fields = $this->connection->fetchAllAssociative('SELECT * FROM tl_form_field WHERE id=?', [$id]);
                break;

            case 'editAll':
            case 'overrideAll':
                $fields = $this->connection->fetchAllAssociative('SELECT * FROM tl_form_field WHERE pid=?', [$id]);
                break;

            default:
                return [];
        }

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

        if (!class_exists($className)) {
            return false;
        }

        /** @var Widget $widget */
        $widget = new $className($field);
        $widget->required = (bool) $field['mandatory'];

        return $widget->submitInput();
    }
}

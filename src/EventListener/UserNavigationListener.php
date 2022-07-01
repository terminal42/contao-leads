<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener;

use Contao\BackendUser;
use Contao\Input;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserNavigationListener
{
    /**
     * @var Connection
     */
    private $database;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var array
     */
    private $forms;

    public function __construct(Connection $database, TokenStorageInterface $tokenStorage)
    {
        $this->database = $database;
        $this->tokenStorage = $tokenStorage;
    }

    public function onLoadLanguageFile(string $name): void
    {
        if ('modules' === $name && 'lead' === \Input::get('do')) {
            $formId = \Input::get('master');

            foreach ($this->getForms() as $form) {
                if ($form['id'] === $formId) {
                    $GLOBALS['TL_LANG']['MOD']['lead'][0] = $form['leadMenuLabel'] ?: $form['title'];
                    break;
                }
            }
        }
    }

    /**
     * Add leads to the backend navigation.
     *
     * @param bool $modules
     *
     * @return array
     */
    public function onGetUserNavigation(array $modules)
    {
        $forms = $this->getForms();

        if (0 === \count($forms)) {
            unset($modules['leads']);

            return $modules;
        }

        $modules['leads']['modules'] = [];

        foreach ($forms as $form) {
            $modules['leads']['modules']['lead_'.$form['id']] = [
                'title' => StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MOD']['leads'][1], $form['title'])),
                'label' => $form['leadMenuLabel'] ?: $form['title'],
                'class' => 'navigation leads',
                'href' => 'contao/main.php?do=lead&master='.$form['id'],
                'isActive' => 'lead' === Input::get('do') && $form['id'] === Input::get('master'),
            ];
        }

        return $modules;
    }

    /**
     * Gets forms with enabled leads.
     *
     * @return array
     */
    private function getForms()
    {
        if (null !== $this->forms) {
            return $this->forms;
        }

        if (!$this->database->getSchemaManager()->tablesExist(['tl_lead'])) {
            return [];
        }

        $allowedIds = $this->getAllowedFormIds();

        if (empty($allowedIds) && !$this->isAdmin()) {
            return [];
        }

        $qb = $this->database->createQueryBuilder();
        $qb
            ->select('id, title, leadMenuLabel')
            ->from('tl_form')
            ->where("leadEnabled='1'")
            ->andWhere('leadMaster=0')
        ;

        if (!$this->isAdmin()) {
            $qb->andWhere('id IN (:ids)')->setParameter('ids', $allowedIds, Connection::PARAM_INT_ARRAY);
        }

        $forms = $qb->execute()->fetchAll();
        $forms = array_merge($forms, $this->findOrphans(array_column($forms, 'id')));

        usort(
            $forms,
            static function ($a, $b) {
                $labelA = $a['leadMenuLabel'] ?: $a['title'];
                $labelB = $b['leadMenuLabel'] ?: $b['title'];

                return $labelA > $labelB;
            }
        );

        return $this->forms = $forms;
    }

    /**
     * Find lead records where the related form has been deleted.
     */
    private function findOrphans(array $formIds): array
    {
        if (!$this->isAdmin()) {
            return [];
        }

        $qb = $this->database->createQueryBuilder();

        $qb
            ->select("master_id AS id, CONCAT('ID ', master_id) AS title, CONCAT('ID ', master_id) AS leadMenuLabel")
            ->from('tl_lead')
            ->groupBy('master_id')
        ;

        if (!empty($formIds)) {
            $qb->where('master_id NOT IN (:masterId)')->setParameter('masterId', $formIds, Connection::PARAM_INT_ARRAY);
        }

        return $qb->execute()->fetchAll();
    }

    private function getAllowedFormIds(): array
    {
        $user = ($token = $this->tokenStorage->getToken()) !== null ? $token->getUser() : null;

        if (
            !$user instanceof BackendUser
            || !$user->hasAccess('lead', 'modules')
            || !\is_array($user->forms)
        ) {
            return [];
        }

        return array_map('intval', (array) $user->forms);
    }

    private function isAdmin()
    {
        $token = $this->tokenStorage->getToken();

        return null !== $token && $token->getUser() instanceof BackendUser && $token->getUser()->isAdmin;
    }
}

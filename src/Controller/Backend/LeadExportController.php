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

namespace Terminal42\LeadsBundle\Controller\Backend;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Terminal42\LeadsBundle\Exporter\ExporterFactory;

class LeadExportController
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var ExporterFactory
     */
    private $exportFactory;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, ExporterFactory $exportFactory)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->exportFactory = $exportFactory;
    }

    /**
     * @Route("/contao/lead/export/{id}", name="terminal42_leads.export", requirements={"id"="\d+"}, defaults={"_scope"="backend"})
     */
    public function __invoke(int $id): void
    {
        $config = $this->exportFactory->buildConfig($id);

        if (!$this->authorizationChecker->isGranted('lead_form', $config->master)) {
            $exception = new AccessDeniedException('Not enough permissions to export leads of form ID "'.$config->id.'"');
            $exception->setAttributes('lead_form');
            $exception->setSubject($config->master);

            throw $exception;
        }

        // TODO: allow to filter leads on export
        // $arrIds = \is_array($GLOBALS['TL_DCA']['tl_lead']['list']['sorting']['root']) ? $GLOBALS['TL_DCA']['tl_lead']['list']['sorting']['root'] : null;

        $file = $this->exportFactory->createForType($config->type)->export($config/*, $arrIds*/);
        $file->sendToBrowser();
    }
}

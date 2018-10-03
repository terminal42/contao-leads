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

namespace Terminal42\LeadsBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Config\ModuleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Terminal42\LeadsBundle\Terminal42LeadsBundle;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            (new BundleConfig(Terminal42LeadsBundle::class))
                ->setReplace(['leads'])
                ->setLoadAfter([ContaoCoreBundle::class, 'haste', 'multicolumnwizard']),
            new ModuleConfig('haste'),
            new ModuleConfig('multicolumnwizard'),
        ];
    }
}

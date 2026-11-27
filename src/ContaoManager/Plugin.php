<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;
use Terminal42\NotificationCenterBundle\Terminal42NotificationCenterBundle;
use WEM\ContaoFormDataManagerBundle\ContaoFormDataManagerBundle;
use WEM\PersonalDataManagerBundle\WEMPersonalDataManagerBundle;

class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoFormDataManagerBundle::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                    WEMPersonalDataManagerBundle::class,
                    Terminal42NotificationCenterBundle::class,
                ])
                ->setReplace(['wem-formdatamanager']),
        ];
    }

    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel): RouteCollection|null
    {
        $file = __DIR__ . '/../../config/routes.yaml';

        return $resolver->resolve($file)->load($file);
    }
}

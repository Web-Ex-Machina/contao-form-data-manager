<?php

namespace WEM\ContaoFormDataManagerBundle\ContaoManager;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use WEM\ContaoFormDataManagerBundle\WEMFormDataManagerBundle;
use WEM\PersonalDataManagerBundle\WEMPersonalDataManagerBundle;

class Plugin implements BundlePluginInterface, RoutingPluginInterface
{

    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(WEMFormDataManagerBundle::class)
                ->setLoadAfter([
                    WEMPersonalDataManagerBundle::class,
                ])
                ->setReplace(['wem-formdatamanager']),
        ];
    }

    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
        $file = __DIR__ . '/../Resources/config/routes.yaml';
        return $resolver->resolve($file)->load($file);
    }
}
<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use WEM\ContaoFormDataManagerBundle\EventListener\NotificationCenter\UsefulDataTokenListener;

class ContaoFormDataManagerBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        // terminal42/notification_center is an optional dependency
        if (isset($builder->getParameter('kernel.bundles')['Terminal42NotificationCenterBundle'])) {
            $container->services()
                ->set(UsefulDataTokenListener::class)
                ->autowire()
                ->autoconfigure()
            ;
        }
    }
}

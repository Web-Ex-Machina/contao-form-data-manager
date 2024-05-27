<?php


namespace WEM\ContaoFormDataManagerBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WEM\ContaoFormDataManagerBundle\DependencyInjection\WEMFormDataManagerExtension;

//use old bundle and not AbstractBundle because contao 4.13 is on symfony 5
class WEMFormDataManagerBundle extends Bundle
{
    /**
     * Retrieve the container extension for this Symfony Bundle.
     * Needed because the non standard naming
     *
     * @return WEMFormDataManagerExtension|null The container extension object or null if not available.
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new WEMFormDataManagerExtension();
    }
}
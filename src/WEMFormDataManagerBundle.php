<?php


namespace WEM\ContaoFormDataManagerBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WEM\ContaoFormDataManagerBundle\DependencyInjection\WEMFormDataManagerExtension;

//use old bundle and not AbstractBundle because contao 4.13 is on symfony 5
class WEMFormDataManagerBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new WEMFormDataManagerExtension();
    }
}
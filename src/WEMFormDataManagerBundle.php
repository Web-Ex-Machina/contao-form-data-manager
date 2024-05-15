<?php


namespace Wem\ContaoFormDataManagerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

//use old bundle and not AbstractBundle because contao 4.13 is on symfony 5
class WEMFormDataManagerBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
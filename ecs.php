<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withConfiguredRule(HeaderCommentFixer::class, [
        'header' => "Form Data Manager for Contao Open Source CMS\n@author     Web Ex Machina\n\n@see        https://github.com/Web-Ex-Machina/contao-form-data-manager\n@license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt",
    ])
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/contao',
    ])
    ->withSkip([
        ArrayOpenerAndCloserNewlineFixer::class,
        __DIR__.'/contao/templates',
        __DIR__.'/vendor',
    ])
    ->withPreparedSets(
        psr12: true,
        common: true,
    )
;

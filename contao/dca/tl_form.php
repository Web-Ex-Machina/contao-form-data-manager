<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_form']['config']['ctable'][] = 'tl_wem_form_storage';

$GLOBALS['TL_DCA']['tl_form']['list']['label']['fields'] = ['title', 'submissions'];
$GLOBALS['TL_DCA']['tl_form']['list']['label']['showColumns'] = true;

$GLOBALS['TL_DCA']['tl_form']['list']['operations']['contacts'] = [
    'href' => 'table=tl_wem_form_storage',
    'icon' => 'user.svg',
    'prefetch' => true,
];

$GLOBALS['TL_DCA']['tl_form']['fields']['storeViaFormDataManager'] = [
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'w50',
    ],
    'sql' => [
        'type' => 'boolean',
        'default' => false,
    ],
];

$GLOBALS['TL_DCA']['tl_form']['fields']['submissions'] = [];

PaletteManipulator::create()
    ->addField('storeViaFormDataManager', 'storeValues', PaletteManipulator::POSITION_BEFORE)
    ->applyToPalette('default', 'tl_form')
;

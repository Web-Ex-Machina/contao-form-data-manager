<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * Copyright (c) 2024-2042 Web ex Machina
 *
 * @category ContaoBundle
 * @package  Web-Ex-Machina/wem-contao-form-data-manager
 * @author   Web ex Machina <contact@webexmachina.fr>
 * @link     https://github.com/Web-Ex-Machina/wem-contao-form-data-manager/
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DcaLoader;

(new DcaLoader('tl_form'))->load();

$GLOBALS['TL_DCA']['tl_form']['list']['operations']['contacts'] = [
    'href' => 'table=tl_wem_form_storage',
    'icon' => 'user.svg',
];
$GLOBALS['TL_DCA']['tl_form']['config']['ctable'][] = 'tl_wem_form_storage';
$GLOBALS['TL_DCA']['tl_form']['config']['onsubmit_callback'][] = ['wem.form_data_manager.data_container.form', 'onSubmitCallback'];
$GLOBALS['TL_DCA']['tl_form']['list']['label']['fields'] = ['title', 'submissions'];
$GLOBALS['TL_DCA']['tl_form']['list']['label']['showColumns'] = true;
$GLOBALS['TL_DCA']['tl_form']['list']['label']['label_callback'] = ['wem.form_data_manager.data_container.form', 'listItems'];
$GLOBALS['TL_DCA']['tl_form']['fields']['storeViaFormDataManager'] = [
    'inputType' => 'checkbox',
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_form']['fields']['submissions'] = [];

PaletteManipulator::create()
    ->addField('storeViaFormDataManager', 'storeValues', PaletteManipulator::POSITION_BEFORE)
    ->applyToPalette('default', 'tl_form');
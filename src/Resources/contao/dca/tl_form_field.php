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

$GLOBALS['TL_DCA']['tl_form_field']['fields']['contains_personal_data'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 cbx m12'],
    'sql' => 'TINYINT(1) unsigned NOT NULL DEFAULT 0',
];
$GLOBALS['TL_DCA']['tl_form_field']['fields']['is_technical_field'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 cbx m12'],
    'sql' => 'TINYINT(1) unsigned NOT NULL DEFAULT 0',
];

PaletteManipulator::create()
    ->addField('contains_personal_data', 'type')
    ->addField('is_technical_field', 'type')
    ->applyToPalette('text', 'tl_form_field')
    ->applyToPalette('textdigit', 'tl_form_field')
    ->applyToPalette('textcustom', 'tl_form_field')
    ->applyToPalette('password', 'tl_form_field')
    ->applyToPalette('passwordcustom', 'tl_form_field')
    ->applyToPalette('textarea', 'tl_form_field')
    ->applyToPalette('textareacustom', 'tl_form_field')
    ->applyToPalette('select', 'tl_form_field')
    ->applyToPalette('radio', 'tl_form_field')
    ->applyToPalette('checkbox', 'tl_form_field')
    ->applyToPalette('range', 'tl_form_field')
    ->applyToPalette('hidden', 'tl_form_field')
    ->applyToPalette('hiddencustom', 'tl_form_field')
    ->applyToPalette('upload', 'tl_form_field')
;
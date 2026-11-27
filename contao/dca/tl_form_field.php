<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_form_field']['fields']['contains_personal_data'] = [
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'w50 cbx m12',
    ],
    'sql' => [
        'type' => 'boolean',
        'default' => false,
    ],
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['is_technical_field'] = [
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'w50 cbx m12',
    ],
    'sql' => [
        'type' => 'boolean',
        'default' => false,
    ],
];

$paletteManipulator = PaletteManipulator::create()
    ->addField('contains_personal_data', 'type')
    ->addField('is_technical_field', 'type')
;

foreach (['text', 'textdigit', 'textcustom', 'password', 'passwordcustom', 'textarea', 'textareacustom', 'select', 'radio', 'checkbox', 'range', 'hidden', 'hiddencustom', 'upload'] as $palette) {
    $paletteManipulator->applyToPalette($palette, 'tl_form_field');
}

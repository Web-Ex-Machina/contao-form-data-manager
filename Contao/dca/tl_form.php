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
//use WEM\WEMFormDataManagerBundle\Classes\Dca\Manipulator as DCAManipulator;
// TODO : reprendre ce que fait le manipulator
(new DcaLoader('tl_form'))->load();

//DCAManipulator::create('tl_form')
//    ->addListOperation('contacts', [
//        'href' => 'table=tl_sm_form_storage',
//        'icon' => 'user.svg',
//    ])
//    ->addCtable('tl_sm_form_storage')
//    ->addConfigOnsubmitCallback('wemformdatamanager.data_container.form', 'onSubmitCallback')
//    ->setListLabelFields(['title', 'submissions'])
//    ->setListLabelShowColumns(true)
//    ->addListLabelLabelCallback('wemformdatamanager.data_container.form', 'listItems')
//    ->addField('storeViaFormDataManager', [
//        'inputType' => 'checkbox',
//        'sql' => "char(1) NOT NULL default ''",
//    ])
//    ->addField('submissions', []) // to have translations in tl_form list column label
//;

PaletteManipulator::create()
    ->addField('storeViaFormDataManager', 'storeValues', PaletteManipulator::POSITION_BEFORE)
    ->applyToPalette('default', 'tl_form');

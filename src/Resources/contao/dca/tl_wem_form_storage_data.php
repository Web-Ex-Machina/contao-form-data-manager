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

use Contao\DataContainer;

$GLOBALS['TL_DCA']['tl_wem_form_storage_data'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'ptable' => 'tl_wem_form_storage',
        'switchToEdit' => false,
        'enableVersioning' => false,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'field' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['pid'],
            'headerFields' => ['form', 'tstamp', 'status'],
            // 'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
            // 'child_record_callback' => [WEM\ContaoFormDataManagerBundle\DataContainer\FormStorageData::class, 'listItems'],
            'child_record_callback' => ['wem.form_data_manager.data_container.form_storage_data', 'listItems'],
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],
    // Palettes
    'palettes' => [
        'default' => '{title_legend},pid,field,value,contains_personal_data',
    ],
    // Fields
    'fields' => [
        'id' => [
            'search' => true,
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'flag' => 8,
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'createdAt' => [
            'default' => time(),
            'flag' => 8,
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'pid' => [
            'exclude' => true,
            'inputType' => 'picker',
            'foreignKey' => 'tl_wem_form_storage.id',
            'eval' => ['mandatory' => true, 'tl_class' => 'clr'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'field' => [
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_form_field.label',
            'eval' => ['mandatory' => true, 'tl_class' => 'clr'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'field_label' => [
            'inputType' => 'text',
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'field_name' => [
            'inputType' => 'text',
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'field_type' => [
            'inputType' => 'text',
            'sql' => "varchar(64) NOT NULL default 'text'",
        ],
        'value' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'load_callback' => [['wem.form_data_manager.classes.dca.field.callback.load.tl_wem_form_storage_data.value', '__invoke']],
            'save_callback' => [['wem.form_data_manager.classes.dca.field.callback.save.tl_wem_form_storage_data.value', '__invoke']],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'contains_personal_data' => [
            'search' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => 'TINYINT(1) unsigned NOT NULL DEFAULT 0',
        ],
    ],
];
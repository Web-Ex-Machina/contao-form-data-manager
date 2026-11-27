<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_wem_form_storage_data'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_wem_form_storage',
        'closed' => true,
        'notCopyable' => true,
        'notSortable' => true,
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

    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['pid'],
            'headerFields' => ['pid', 'createdAt', 'status'],
            'panelLayout' => 'filter;search,limit',
        ],
        'global_operations' => [
            'all',
        ],
        'operations' => [
            'show',
        ],
    ],

    'palettes' => [
        'default' => '{title_legend},pid,field,value,contains_personal_data',
    ],

    'fields' => [
        'id' => [
            'search' => true,
            'sql' => [
                'type' => 'integer',
                'unsigned' => true,
                'autoincrement' => true,
            ],
        ],
        'tstamp' => [
            'flag' => DataContainer::SORT_MONTH_DESC,
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'createdAt' => [
            'default' => time(),
            'flag' => DataContainer::SORT_MONTH_DESC,
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'pid' => [
            'exclude' => true,
            'inputType' => 'picker',
            'foreignKey' => 'tl_wem_form_storage.id',
            'eval' => [
                'mandatory' => true,
                'tl_class' => 'clr',
            ],
            'sql' => [
                'type' => 'integer',
                'unsigned' => true,
                'default' => 0,
            ],
            'relation' => [
                'type' => 'belongsTo',
                'load' => 'lazy',
            ],
        ],
        'field' => [
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_form_field.label',
            'eval' => [
                'mandatory' => true,
                'tl_class' => 'clr',
            ],
            'sql' => [
                'type' => 'integer',
                'unsigned' => true,
                'default' => 0,
            ],
            'relation' => [
                'type' => 'hasOne',
                'load' => 'lazy',
            ],
        ],
        'field_label' => [
            'inputType' => 'text',
            'sql' => [
                'type' => 'string',
                'length' => 255,
                'default' => '',
            ],
        ],
        'field_name' => [
            'inputType' => 'text',
            'sql' => [
                'type' => 'string',
                'length' => 255,
                'default' => '',
            ],
        ],
        'field_type' => [
            'inputType' => 'text',
            'sql' => [
                'type' => 'string',
                'length' => 64,
                'default' => 'text',
            ],
        ],
        'value' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => [
                'tl_class' => 'clr',
            ],
            'sql' => 'mediumtext NULL',
        ],
        'contains_personal_data' => [
            'search' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50',
            ],
            'sql' => [
                'type' => 'boolean',
                'default' => false,
            ],
        ],
    ],
];

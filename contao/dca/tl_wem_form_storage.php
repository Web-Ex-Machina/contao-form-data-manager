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
use WEM\ContaoFormDataManagerBundle\Controller\Backend\ExportController;
use WEM\ContaoFormDataManagerBundle\Enum\FormStorageStatus;

$GLOBALS['TL_DCA']['tl_wem_form_storage'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_form',
        'ctable' => ['tl_wem_form_storage_data'],
        'closed' => true,
        'notCopyable' => true,
        'switchToEdit' => false,
        'enableVersioning' => false,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['createdAt', 'status'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['pid', 'createdAt', 'status', 'sender'],
            'showColumns' => true,
        ],
        'global_operations' => [
            'all',
            'export_all' => [
                'icon' => 'store.svg',
            ],
        ],
        'operations' => [
            'edit',
            'children',
            'delete',
            'show',
            'export' => [
                'route' => ExportController::ROUTE_SINGLE,
                'icon' => 'store.svg',
            ],
        ],
    ],

    'palettes' => [
        'default' => '{title_legend},pid,status,note;{statistics_legend},completion_percentage,delay_to_first_interaction,delay_to_submission;{page_legend},current_page,current_page_url,referer_page,referer_page_url;{data_legend},sender,form_storage_data',
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
            'filter' => true,
            'search' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_form.title',
            'eval' => [
                'mandatory' => true,
                'tl_class' => 'clr',
                'disabled' => true,
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
        'status' => [
            'search' => true,
            'filter' => true,
            'inputType' => 'select',
            'enum' => FormStorageStatus::class,
            'eval' => [
                'mandatory' => true,
                'maxlength' => 32,
                'tl_class' => 'w50',
            ],
            'sql' => [
                'type' => 'string',
                'length' => 32,
                'default' => '',
            ],
        ],
        'note' => [
            'inputType' => 'textarea',
            'eval' => [
                'tl_class' => 'w50',
                'rows' => 3,
            ],
            'sql' => [
                'type' => 'text',
                'notnull' => false,
            ],
        ],
        'token' => [
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50',
            ],
            'sql' => [
                'type' => 'string',
                'length' => 255,
                'default' => '',
            ],
        ],
        'completion_percentage' => [
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'rgxp' => 'custom',
                'customRgxp' => '/^(\d{1,3}),(\d{2})$/',
                'tl_class' => 'w50 clr',
                'disabled' => true,
                'maxval' => 100,
                'minval' => 0,
            ],
            'sql' => 'DECIMAL(5,2) unsigned NOT NULL default 0',
        ],
        'delay_to_first_interaction' => [
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'rgxp' => 'custom',
                'customRgxp' => '/^(\d{1,14})$/',
                'tl_class' => 'w50',
                'disabled' => true,
                'minval' => 0,
            ],
            'sql' => [
                'type' => 'string',
                'length' => 14,
                'default' => '',
            ],
        ],
        'delay_to_submission' => [
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'rgxp' => 'custom',
                'customRgxp' => '/^(\d{1,14})$/',
                'tl_class' => 'w50 clr',
                'disabled' => true,
                'minval' => 0,
            ],
            'sql' => [
                'type' => 'string',
                'length' => 14,
                'default' => '',
            ],
        ],
        'current_page' => [
            'search' => true,
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_page.title',
            'eval' => [
                'tl_class' => 'w50',
                'disabled' => true,
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
        'current_page_url' => [
            'inputType' => 'text',
            'eval' => [
                'tl_class' => 'w50',
                'mandatory' => true,
                'rgxp' => 'httpurl',
                'disabled' => true,
            ],
            'sql' => [
                'type' => 'text',
                'notnull' => false,
            ],
        ],
        'referer_page' => [
            'search' => true,
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_page.title',
            'eval' => [
                'tl_class' => 'w50',
                'disabled' => true,
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
        'referer_page_url' => [
            'inputType' => 'text',
            'eval' => [
                'tl_class' => 'w50',
                'rgxp' => 'httpurl',
                'disabled' => true,
            ],
            'sql' => [
                'type' => 'text',
                'notnull' => false,
            ],
        ],
        'form_storage_data' => [
            'exclude' => true,
        ],
        'sender' => [
            'search' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => [
                'tl_class' => 'w50',
                'disabled' => true,
                'rgxp' => 'email',
            ],
            'sql' => [
                'type' => 'string',
                'length' => 255,
                'default' => '',
            ],
        ],
    ],
];

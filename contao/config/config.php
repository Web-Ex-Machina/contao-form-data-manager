<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

use Contao\ArrayUtil;
use WEM\ContaoFormDataManagerBundle\EventListener\PersonalDataManager\AnonymizeListener;
use WEM\ContaoFormDataManagerBundle\EventListener\PersonalDataManager\CsvFormatterListener;
use WEM\ContaoFormDataManagerBundle\EventListener\PersonalDataManager\ExportListener;
use WEM\ContaoFormDataManagerBundle\EventListener\PersonalDataManager\ManagerListener;
use WEM\ContaoFormDataManagerBundle\EventListener\PersonalDataManager\UiListener;
use WEM\ContaoFormDataManagerBundle\Model\FormStorage;
use WEM\ContaoFormDataManagerBundle\Model\FormStorageData;

/*
 * Back end modules
 */
ArrayUtil::arrayInsert(
    $GLOBALS['BE_MOD']['content'],
    array_search('form', array_keys($GLOBALS['BE_MOD']['content']), true) + 1,
    [
        'wem_form_data_manager' => [
            'tables' => ['tl_wem_form_storage', 'tl_wem_form_storage_data'],
        ],
    ],
);

$GLOBALS['BE_MOD']['content']['form']['tables'][] = 'tl_wem_form_storage';
$GLOBALS['BE_MOD']['content']['form']['tables'][] = 'tl_wem_form_storage_data';

/*
 * Models
 */
$GLOBALS['TL_MODELS'][FormStorage::getTable()] = FormStorage::class;
$GLOBALS['TL_MODELS'][FormStorageData::getTable()] = FormStorageData::class;

/*
 * Personal Data Manager hooks
 */
// Export
$GLOBALS['WEM_HOOKS']['formatSinglePersonalDataForCsvExport'][] = [CsvFormatterListener::class, 'formatSingle'];
$GLOBALS['WEM_HOOKS']['exportByPidAndPtableAndEmail'][] = [ExportListener::class, 'exportByPidAndPtableAndEmail'];
// Anonymize
$GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmail'][] = [AnonymizeListener::class, 'anonymizeByPidAndPtableAndEmail'];
// UI
$GLOBALS['WEM_HOOKS']['sortData'][] = [UiListener::class, 'sortData'];
$GLOBALS['WEM_HOOKS']['renderSingleItemTitle'][] = [UiListener::class, 'renderSingleItemTitle'];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingle'][] = [UiListener::class, 'renderSingleItemBodyOriginalModelSingle'];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldValue'][] = [UiListener::class, 'renderSingleItemBodyOriginalModelSingleFieldValue'];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingle'][] = [UiListener::class, 'renderSingleItemBodyPersonalDataSingle'];
$GLOBALS['WEM_HOOKS']['buildSingleItemBodyPersonalDataSingleButtons'][] = [UiListener::class, 'buildSingleItemBodyPersonalDataSingleButtons'];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldLabel'][] = [UiListener::class, 'renderSingleItemBodyPersonalDataSingleFieldLabel'];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldValue'][] = [UiListener::class, 'renderSingleItemBodyPersonalDataSingleFieldValue'];
// Manager
$GLOBALS['WEM_HOOKS']['getFileByPidAndPtableAndEmailAndField'][] = [ManagerListener::class, 'getFileByPidAndPtableAndEmailAndField'];
$GLOBALS['WEM_HOOKS']['isPersonalDataLinkedToFile'][] = [ManagerListener::class, 'isPersonalDataLinkedToFile'];

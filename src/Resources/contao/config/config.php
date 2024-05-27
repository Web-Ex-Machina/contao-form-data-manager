<?php

use Contao\ArrayUtil;

ArrayUtil::arrayInsert(
    $GLOBALS['BE_MOD']['content'],
    array_search('form', array_keys($GLOBALS['BE_MOD']['content']), true) + 1,
    [
        'wem_form_data_manager' => [
            'tables' => ['tl_sm_form_storage', 'tl_sm_form_storage_data'],
            'export_all' => ['wem.form_data_manager.backend.backend_controller', 'exportAll'],
            'export' => ['wem.form_data_manager.backend.backend_controller', 'exportSingle'],
        ],
    ]
);
$GLOBALS['BE_MOD']['content']['form']['export_all'] = ['wem.form_data_manager.backend.backend_controller', 'exportAllFromForm'];
$GLOBALS['BE_MOD']['content']['form']['export'] = ['wem.form_data_manager.backend.backend_controller', 'exportSingle'];

$GLOBALS['TL_HOOKS']['processFormData'][] = ['wem.form_data_manager.listener.process_form_data', '__invoke'];
$GLOBALS['TL_HOOKS']['compileFormFields'][] = ['wem.form_data_manager.listener.compile_form_fields', '__invoke'];
$GLOBALS['TL_HOOKS']['sendNotificationMessage'][] = ['wem.form_data_manager.listener.send_notification_message', '__invoke'];

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_text'][] = 'useful_data'; // kept for BC compatibility
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_text'][] = 'useful_data_text';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_text'][] = 'useful_data_filled'; // kept for BC compatibility
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_text'][] = 'useful_data_filled_text';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_html'][] = 'useful_data';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_html'][] = 'useful_data_filled';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['file_content'][] = 'useful_data';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['file_content'][] = 'useful_data_filled';


// PDM EXPORT
$GLOBALS['WEM_HOOKS']['formatSinglePersonalDataForCsvExport'][] = ['wem.form_data_manager.listener.personal_data_csv_formatter', 'formatSingle'];
$GLOBALS['WEM_HOOKS']['exportByPidAndPtableAndEmail'][] = ['wem.form_data_manager.listener.personal_data_export', 'exportByPidAndPtableAndEmail'];
// PDM ANONYMIZE
$GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmail'][] = ['wem.form_data_manager.listener.personal_data_anonymize', 'anonymizeByPidAndPtableAndEmail'];
// PDM UI
$GLOBALS['WEM_HOOKS']['sortData'][] = ['wem.form_data_manager.listener.personal_data_ui', 'sortData'];
$GLOBALS['WEM_HOOKS']['renderSingleItemTitle'][] = ['wem.form_data_manager.listener.personal_data_ui', 'renderSingleItemTitle'];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingle'][] = ['wem.form_data_manager.listener.personal_data_ui', 'renderSingleItemBodyOriginalModelSingle'];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldValue'][] = ['wem.form_data_manager.listener.personal_data_ui', 'renderSingleItemBodyOriginalModelSingleFieldValue'];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingle'][] = ['wem.form_data_manager.listener.personal_data_ui', 'renderSingleItemBodyPersonalDataSingle'];
$GLOBALS['WEM_HOOKS']['buildSingleItemBodyPersonalDataSingleButtons'][] = ['wem.form_data_manager.listener.personal_data_ui', 'buildSingleItemBodyPersonalDataSingleButtons'];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldLabel'][] = ['wem.form_data_manager.listener.personal_data_ui', 'renderSingleItemBodyPersonalDataSingleFieldLabel'];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldValue'][] = ['wem.form_data_manager.listener.personal_data_ui', 'renderSingleItemBodyPersonalDataSingleFieldValue'];
// PDM Manager
$GLOBALS['WEM_HOOKS']['getFileByPidAndPtableAndEmailAndField'][] = ['wem.form_data_manager.listener.personal_data_manager', 'getFileByPidAndPtableAndEmailAndField'];
$GLOBALS['WEM_HOOKS']['isPersonalDataLinkedToFile'][] = ['wem.form_data_manager.listener.personal_data_manager', 'isPersonalDataLinkedToFile'];
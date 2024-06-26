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

namespace WEM\ContaoFormDataManagerBundle\DataContainer;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\DataContainer;
use Contao\Date;
use Contao\FormModel;
use Contao\System;
use Contao\Validator;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\UtilsBundle\Classes\DateUtil;
use WEM\ContaoFormDataManagerBundle\Model\FormStorage as ModelFormStorage;
use WEM\ContaoFormDataManagerBundle\Model\FormStorageData;

class FormStorage
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function listItems(array $row): array
    {
        $objForm = FormModel::findById($row['pid']);
        $objFormStorageDataEmail = FormStorageData::findItems(['pid' => $row['id'], 'field_label' => 'Email'], 1);

        return [
            $objForm ? $objForm->title : $row['pid'],
            Date::parse(Config::get('datimFormat'), (int) $row['tstamp']),
            $this->translator->trans(sprintf('tl_wem_form_storage.status.%s', $row['status']), [], 'contao_default'),
            empty($row['sender']) ? ($objFormStorageDataEmail ? $objFormStorageDataEmail->value : 'NR') : ($row['sender']),
        ];
    }

    public function showData(DataContainer $dc, string $extendedLabel): string
    {
        System::loadLanguageFile('tl_wem_form_storage_data');
        $formStorageDatas = FormStorageData::findItems(['pid' => $dc->id]);
        $arrFormStorageDatas = [];
        $objTemplate = new BackendTemplate('be_wem_form_storage_data');

        if ($formStorageDatas) {
            while ($formStorageDatas->next()) {
                $arrFormStorageDatas[$formStorageDatas->id] = $formStorageDatas->current()->row();
                $arrFormStorageDatas[$formStorageDatas->id]['raw_value'] = $arrFormStorageDatas[$formStorageDatas->id]['value'];
                $arrFormStorageDatas[$formStorageDatas->id]['value'] = $formStorageDatas->current()->getValueAsStringFormatted();
                $arrFormStorageDatas[$formStorageDatas->id]['is_uuid'] = Validator::isStringUuid($arrFormStorageDatas[$formStorageDatas->id]['raw_value']);
            }
        }

        $objTemplate->arrFormStorageDatas = $arrFormStorageDatas;

        return $objTemplate->parse();
    }

    public function onShowCallback(array $modalData, array $recordData, DataContainer $dc): array
    {
        $key = sprintf('%s <small>%s</small>', $GLOBALS['TL_LANG'][ModelFormStorage::getTable()]['delay_to_submission'][0], 'delay_to_submission');
        $modalData[ModelFormStorage::getTable()][0][$key] = DateUtil::humanReadableDuration((int) $recordData['delay_to_submission']);
        $key = sprintf('%s <small>%s</small>', $GLOBALS['TL_LANG'][ModelFormStorage::getTable()]['delay_to_first_interaction'][0], 'delay_to_first_interaction');
        $modalData[ModelFormStorage::getTable()][0][$key] = DateUtil::humanReadableDuration((int) $recordData['delay_to_first_interaction']);
        $key = sprintf('%s <small>%s</small>', $GLOBALS['TL_LANG'][ModelFormStorage::getTable()]['completion_percentage'][0], 'completion_percentage');
        $modalData[ModelFormStorage::getTable()][0][$key] = $recordData['completion_percentage'].'%';

        $formStorageDatas = FormStorageData::findItems(['pid' => $dc->id]);
        if ($formStorageDatas) {
            $modalData[FormStorageData::getTable()] = [0 => []];
            while ($formStorageDatas->next()) {
                $modalData[FormStorageData::getTable()][0][$formStorageDatas->field_label] = $formStorageDatas->current()->getValueAsString();
            }
        }

        return $modalData;
    }
}
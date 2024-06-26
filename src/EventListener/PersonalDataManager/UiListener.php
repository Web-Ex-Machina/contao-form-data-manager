<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * Copyright (c) 2015-2024 Web ex Machina
 *
 * @category ContaoBundle
 * @package  Web-Ex-Machina/wem-contao-form-data-manager
 * @author   Web ex Machina <contact@webexmachina.fr>
 * @link     https://github.com/Web-Ex-Machina/wem-contao-form-data-manager/
 */

namespace WEM\ContaoFormDataManagerBundle\EventListener\PersonalDataManager;

use Contao\Config;
use Contao\Date;
use Contao\File;
use Contao\FilesModel;
use Contao\MemberGroupModel;
use Contao\Model;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\Validator;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\ContaoFormDataManagerBundle\Classes\FormUtil;
use WEM\PersonalDataManagerBundle\Model\PersonalData;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManagerUi;
use WEM\UtilsBundle\Classes\Files as FileUtil;
use WEM\ContaoFormDataManagerBundle\Model\FormStorage;
use WEM\ContaoFormDataManagerBundle\Model\FormStorageData;

class UiListener
{

    protected TranslatorInterface $translator;

    protected PersonalDataManagerUi $personalDataManagerUi;

    public function __construct(
        TranslatorInterface $translator,
        PersonalDataManagerUi $personalDataManagerUi
    ) {
        $this->translator = $translator;
        $this->personalDataManagerUi = $personalDataManagerUi;

        $GLOBALS['TL_CSS'][] = 'bundles/ContaoFormDataManager/css/frontend.css';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/ContaoFormDataManager/js/personaldatamanager/frontend.js';
    }

    public function sortData(array $sorted, ?Collection $personalDatas): array
    {
        $sorted2 = $sorted;

        foreach ($sorted as $ptable => $ptableDatas) {
            if ($ptable == FormStorageData::getTable()) {
                foreach ($ptableDatas as $id => $idDatas) {
                    $objFormStorageData = FormStorageData::findOneBy('id', $idDatas['personalDatas'][0]->pid);
                    /** @var FormStorage */
                    $objFormStorage = $objFormStorageData->getRelated('pid');

                    if (!\array_key_exists(FormStorage::getTable(), $sorted2)) {
                        $sorted2[FormStorage::getTable()] = [];
                    }

                    if (!\array_key_exists($objFormStorage->id, $sorted2[FormStorage::getTable()])) {
                        $arrPersonalDatas = $this->getPersonalDataForFormStorage($objFormStorage);
                        $sorted2[FormStorage::getTable()][$objFormStorage->id] = [
                            'originalModel' => $arrPersonalDatas[0],
                            'personalDatas' => $arrPersonalDatas[1],
                        ];
                    }

                    unset($sorted2[$ptable][$id]);
                }
            }
        }

        return $sorted2;
    }

    public function renderSingleItemTitle(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel, string $buffer): string
    {
        switch ($ptable) {
            case 'tl_member':
                $buffer = 'Member';
                break;
            case FormStorage::getTable():
                $objFormStorage = FormStorage::findOneBy('id', $pid);
                $objForm = $objFormStorage->getRelated('pid');
                $buffer = sprintf('%s %s', 'Formulaire', $objForm->title);
                break;
        }

        return $buffer;
    }

    public function renderSingleItemBodyOriginalModelSingle(int $pid, string $ptable, string $email, string $field, $value, array $personalDatas, Model $originalModel, string $buffer): string
    {
        switch ($ptable) {
            case 'tl_member':
                switch ($field) {
                    case 'id':
                    case 'tstamp':
                    case 'password':
                    case 'dateAdded':
                    case 'lastLogin':
                    case 'loginAttempts':
                    case 'locked':
                    case 'session':
                    case 'secret':
                    case 'backupCodes':
                    case 'trustedTokenVersion':
                    case 'currentLogin':
                        $buffer = '';
                        break;
                }

                break;
            case FormStorage::getTable():
                switch ($field) {
                    case 'id':
                    case 'tstamp':
                    case 'status':
                    case 'token':
                    case 'completion_percentage':
                    case 'delay_to_submission':
                    case 'delay_to_first_interaction':
                    case 'note':
                        $buffer = '';
                        break;
                }

                break;
        }

        return $buffer;
    }

    public function renderSingleItemBodyOriginalModelSingleFieldValue(int $pid, string $ptable, string $email, string $field, $value, array $personalDatas, Model $originalModel, string $buffer): string
    {
        if (empty($buffer)) {
            return sprintf('<i>%s</i>', $this->translator->trans('WEM.SMARTGEAR.DEFAULT.NotFilled', [], 'contao_default'));
        }

        switch ($ptable) {
            case 'tl_member':
                switch ($field) {
                    case 'login':
                        $buffer = sprintf('<input type="checkbox" readonly %s />', true === (bool) $value ? 'checked' : '');
                        break;
                    case 'groups':
                        $groupIds = unserialize($value);
                        $buffer = '<ul>';
                        foreach ($groupIds as $groupId) {
                            $objGroup = MemberGroupModel::findById($groupId);
                            $buffer .= sprintf('<li>- %s</li>', null !== $objGroup ? $objGroup->name : $this->translator->trans('WEM.SMARTGEAR.DEFAULT.elementUnknown', [], 'contao_default'));
                        }

                        $buffer .= '<ul>';
                        break;
                }

                break;
            case FormStorage::getTable():
                switch ($field) {
                    case 'pid':
                        $objFormStorage = FormStorage::findOneBy('id', $pid);
                        $objForm = $objFormStorage->getRelated('pid');
                        $buffer = $objForm->title;
                        break;
                    case 'createdAt':
                        $buffer = Date::parse(Config::get('datimFormat'), (int) $value);
                        break;
                    case 'current_page':
                    case 'referer_page':
                        if (!empty($value)) {
                            $objPage = PageModel::findOneById($value);
                            if ($objPage) {
                                $buffer = $objPage->title;
                            }
                        }

                        break;
                }

                break;
        }

        return $buffer;
    }

    public function renderSingleItemBodyPersonalDataSingleFieldValue(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel, string $buffer): string
    {
        switch ($ptable) {
            case 'tl_member':
                switch ($personalData->field) {
                    case 'dateOfBirth':
                        $buffer = !empty($buffer) ? Date::parse(Config::get('dateFormat'), (int) $buffer) : $buffer;
                        break;
                    default:
                        if (empty($buffer)) {
                            return sprintf('<i>%s</i>', $this->translator->trans('WEM.SMARTGEAR.DEFAULT.NotFilled', [], 'contao_default'));
                        }

                        break;
                }

                break;
            case FormStorage::getTable():
                $buffer = FormUtil::getFormStorageDataValueAsString($this->personalDataManagerUi->formatSingleItemBodyPersonalDataSingleFieldValue($pid, $ptable, $email, $personalData, $personalDatas, $originalModel));
                break;
            case FormStorageData::getTable():
                $objFormStorageData = FormStorageData::findByPk($pid);
                if ($objFormStorageData) {
                    switch ($objFormStorageData->field_type) {
                        case 'upload':
                            $buffer = FormUtil::getFormStorageDataValueAsString($this->personalDataManagerUi->formatSingleItemBodyPersonalDataSingleFieldValue($pid, $ptable, $email, $personalData, $personalDatas, $originalModel));
                            if (Validator::isStringUuid($buffer)) {
                                $objFileModel = FilesModel::findByUuid($buffer);
                                if (!$objFileModel) {
                                    $buffer = $this->translator->trans('WEM.FDM.PDMUI.fileNotFound', [], 'contao_default');
                                } else {
                                    $buffer = $objFileModel->name;
                                }
                            }

                            break;
                        default:
                            $buffer = FormUtil::getFormStorageDataValueAsString($this->personalDataManagerUi->formatSingleItemBodyPersonalDataSingleFieldValue($pid, $ptable, $email, $personalData, $personalDatas, $originalModel));
                    }
                } else {
                    $buffer = FormUtil::getFormStorageDataValueAsString($this->personalDataManagerUi->formatSingleItemBodyPersonalDataSingleFieldValue($pid, $ptable, $email, $personalData, $personalDatas, $originalModel));
                }

                break;
            default:
                if (empty($buffer)) {
                    return sprintf('<i>%s</i>', $this->translator->trans('WEM.SMARTGEAR.DEFAULT.NotFilled', [], 'contao_default'));
                }

                break;
        }

        return $buffer;
    }

    public function renderSingleItemBodyPersonalDataSingleFieldLabel(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel, string $buffer): string
    {
        switch ($ptable) {
            case FormStorage::getTable():
            case FormStorageData::getTable():
                $buffer = $personalData->field_label ?? $buffer;
                break;
        }

        return $buffer;
    }

    public function renderSingleItemBodyPersonalDataSingle(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel, string $buffer): string
    {
        if ($ptable == FormStorage::getTable()) {
            $buffer = $this->personalDataManagerUi->formatSingleItemBodyPersonalDataSingle((int)$personalData->pid, $personalData->ptable, $email, $personalData, $personalDatas, $originalModel);
        }

        return $buffer;
    }

    public function buildSingleItemBodyPersonalDataSingleButtons(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel, ?File $file, array $buttons): array
    {

        if ($file instanceof File) {
            if (FileUtil::isDisplayableInBrowser($file)) {
                $buttons['show'] = sprintf('<br /><a href="%s" class="pdm-button pdm-button_show_file pdm-item__personal_data_single__button_show_file" target="_blank" data-path="%s">%s</a>',
                    $this->personalDataManagerUi->getUrl(),
                    $file->path,
                    $this->translator->trans('WEM.FDM.PDMUI.buttonShowFile', [], 'contao_default')
                );
            }

            $buttons['download'] = sprintf('<br /><a href="%s" class="pdm-button pdm-button_download_file pdm-item__personal_data_single__button_download_file" target="_blank" data-path="%s">%s</a>',
                $this->personalDataManagerUi->getUrl(),
                $file->path,
                $this->translator->trans('WEM.FDM.PDMUI.buttonDownloadFile', [], 'contao_default')
            );
        }


        return $buttons;
    }

    protected function getPersonalDataForFormStorage(FormStorage $objFormStorage): array
    {
        $formStorageDatas = FormStorageData::findItems(['pid' => $objFormStorage->id]);
        // make personalDatas all personal datas attached to this form
        $arrPersonalDatas = [];
        if ($formStorageDatas) {
            while ($formStorageDatas->next()) {
                // $objFormStorage->{$formStorageDatas->field_name} = $formStorageDatas->current()->getValueAsString();
                $objPersonalData = PersonalData::findOneByPidAndPTableAndField((int) $formStorageDatas->id, FormStorageData::getTable(), 'value');
                if ($objPersonalData instanceof Collection) {
                    $objPersonalData = $objPersonalData->current();
                    $arrPersonalDataValues = $objPersonalData->row();
                    $arrPersonalDataValues['field_label'] = $formStorageDatas->field_label;
                    $objPersonalData->setRow($arrPersonalDataValues);
                    $arrPersonalDatas[] = $objPersonalData;
                }
            }
        }

        return [$objFormStorage, $arrPersonalDatas];
    }
}

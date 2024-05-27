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

namespace WEM\ContaoFormDataManagerBundle\EventListener\PersonalDataManager;

use Contao\FilesModel;
use Contao\Validator;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\PersonalDataManagerBundle\Model\PersonalData;
use WEM\ContaoFormDataManagerBundle\Model\FormStorageData;

class ManagerListener
{
    protected TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function getFileByPidAndPtableAndEmailAndField(int $pid, string $ptable, string $email, string $field, PersonalData $personalData, $value, ?FilesModel $objFileModel): ?FilesModel
    {
        if ($ptable === FormStorageData::getTable()) {
            $objFormStorageData = FormStorageData::findByPk($pid);
            if ($objFormStorageData && $objFormStorageData->field_type === 'upload') {
                if (Validator::isStringUuid($objFormStorageData->value)) {
                    $objFileModel = FilesModel::findByUuid($objFormStorageData->value);
                }
            }
        }

        return $objFileModel;
    }

    public function isPersonalDataLinkedToFile(PersonalData $personalData, bool $isLinkedToFile): bool
    {
        if ($personalData->ptable === FormStorageData::getTable()) {
            $objFormStorageData = FormStorageData::findByPk($personalData->pid);
            if ($objFormStorageData && $objFormStorageData->field_type === 'upload') {
                if (Validator::isStringUuid($objFormStorageData->value)) {
                    $isLinkedToFile = true;
                }
            }
        }

        return $isLinkedToFile;
    }
}

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

namespace WEM\WEMFormDataManagerBundle\EventListener\PersonalDataManager;

use Contao\FilesModel;
use Contao\Validator;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\PersonalDataManagerBundle\Model\PersonalData;
use WEM\WEMFormDataManagerBundle\Model\FormStorageData;

class ManagerListener
{
    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function getFileByPidAndPtableAndEmailAndField(int $pid, string $ptable, string $email, string $field, PersonalData $personalData, $value, ?FilesModel $objFileModel): ?FilesModel
    {
        switch ($ptable) {
            case FormStorageData::getTable():
                $objFormStorageData = FormStorageData::findByPk($pid);
                if ($objFormStorageData) {
                    switch ($objFormStorageData->field_type) {
                        case 'upload':
                            if (Validator::isStringUuid($objFormStorageData->value)) {
                                $objFileModel = FilesModel::findByUuid($objFormStorageData->value);
                            }
                        break;
                    }
                }
            break;
        }

        return $objFileModel;
    }

    public function isPersonalDataLinkedToFile(PersonalData $personalData, bool $isLinkedToFile): bool
    {
        switch ($personalData->ptable) {
            case FormStorageData::getTable():
                $objFormStorageData = FormStorageData::findByPk($personalData->pid);
                if ($objFormStorageData) {
                    switch ($objFormStorageData->field_type) {
                        case 'upload':
                            if (Validator::isStringUuid($objFormStorageData->value)) {
                                $isLinkedToFile = true;
                            }
                        break;
                    }
                }
            break;
        }

        return $isLinkedToFile;
    }
}

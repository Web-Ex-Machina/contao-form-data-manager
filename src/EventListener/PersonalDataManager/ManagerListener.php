<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\EventListener\PersonalDataManager;

use Contao\FilesModel;
use Contao\Validator;
use WEM\ContaoFormDataManagerBundle\Model\FormStorageData;
use WEM\PersonalDataManagerBundle\Model\PersonalData;

/**
 * Registered as $GLOBALS['WEM_HOOKS']['getFileByPidAndPtableAndEmailAndField'] and ['isPersonalDataLinkedToFile'].
 */
class ManagerListener
{
    public function getFileByPidAndPtableAndEmailAndField(int $pid, string $ptable, string $email, string $field, PersonalData $personalData, mixed $value, FilesModel|null $objFileModel): FilesModel|null
    {
        if (FormStorageData::getTable() !== $ptable) {
            return $objFileModel;
        }

        $uuid = $this->getUploadedFileUuid($pid);

        return $uuid !== null ? FilesModel::findByUuid($uuid) : $objFileModel;
    }

    public function isPersonalDataLinkedToFile(PersonalData $personalData, bool $isLinkedToFile): bool
    {
        if (FormStorageData::getTable() !== $personalData->ptable) {
            return $isLinkedToFile;
        }

        return $this->getUploadedFileUuid((int) $personalData->pid) !== null || $isLinkedToFile;
    }

    private function getUploadedFileUuid(int $formStorageDataId): string|null
    {
        $objFormStorageData = FormStorageData::findByPk($formStorageDataId);

        if (! $objFormStorageData instanceof FormStorageData || $objFormStorageData->field_type !== 'upload') {
            return null;
        }

        return Validator::isStringUuid((string) $objFormStorageData->value) ? $objFormStorageData->value : null;
    }
}

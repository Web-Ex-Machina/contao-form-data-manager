<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\Model;

use Contao\Database;
use Contao\FilesModel;
use Contao\Model\Collection;
use Contao\System;
use Contao\Validator;
use WEM\ContaoFormDataManagerBundle\Classes\FormUtil;
use WEM\PersonalDataManagerBundle\Model\Traits\PersonalDataTrait as PDMTrait;
use WEM\UtilsBundle\Model\Model as CoreModel;

/**
 * @property int         $id
 * @property int         $pid
 * @property int|string  $tstamp
 * @property int|string  $createdAt
 * @property int         $field
 * @property string      $field_label
 * @property string      $field_name
 * @property string      $field_type
 * @property string|null $value
 * @property bool        $contains_personal_data
 */
class FormStorageData extends CoreModel
{
    use PDMTrait;

    public const string NO_FILE_UPLOADED = 'no_file_uploaded';

    public const string FILE_UPLOADED_BUT_NOT_STORED = 'file_uploaded_but_not_stored';

    protected static array $personalDataFieldsNames = [
        'value',
    ];

    protected static array $personalDataFieldsDefaultValues = [
        'value' => 'managed_by_pdm',
    ];

    protected static array $personalDataFieldsAnonymizedValues = [
        'value' => 'anonymized',
    ];

    protected static string $personalDataPidField = 'id';

    protected static string $personalDataEmailField = 'email';

    protected static string $personalDataPtable = 'tl_wem_form_storage_data';

    protected static $strTable = 'tl_wem_form_storage_data';

    public function getPersonalDataEmailFieldValue(): string
    {
        $objFS = FormStorage::findItems([
            'id' => $this->pid,
        ]);

        if ($objFS instanceof Collection && ! empty($objFS->sender)) {
            return $objFS->sender;
        }

        $objFDS = self::findItems([
            'pid' => $this->pid,
            'field_name' => 'email',
        ], 1);

        if (! $objFDS instanceof Collection) {
            throw new \RuntimeException('Unable to find the email field');
        }

        return $objFDS->value;
    }

    public function shouldManagePersonalData(): bool
    {
        return (bool) $this->contains_personal_data;
    }

    public function getValueAsString(): string
    {
        return FormUtil::getFormStorageDataValueAsString($this->value);
    }

    public function getValueAsStringFormatted(): string
    {
        $value = $this->getValueAsString();

        return match ($this->field_type) {
            'textarea', 'textareacustom' => nl2br($value),
            'upload' => $this->formatUploadValue($value),
            default => $value,
        };
    }

    public static function deleteAll(): void
    {
        Database::getInstance()->prepare(\sprintf('DELETE FROM %s', self::getTable()))->execute();

        System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager')->deleteByPtable(self::getTable());
    }

    private function formatUploadValue(string $value): string
    {
        $translator = System::getContainer()->get('translator');

        return match (true) {
            $value === self::NO_FILE_UPLOADED => $translator->trans('WEM.FDM.ERROR.noFileUploaded', [], 'contao_default'),
            $value === self::FILE_UPLOADED_BUT_NOT_STORED => $translator->trans('WEM.FDM.ERROR.fileUploadedButNotStored', [], 'contao_default'),
            Validator::isStringUuid($value) => FilesModel::findByUuid($value)?->path ?? $translator->trans('WEM.FDM.ERROR.uploadedFileNotFound', [], 'contao_default'),
            default => $value,
        };
    }
}

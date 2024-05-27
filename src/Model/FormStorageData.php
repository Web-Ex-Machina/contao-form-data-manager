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

namespace WEM\ContaoFormDataManagerBundle\Model;

use Contao\Database;
use Contao\FilesModel;
use Contao\Model\Collection;
use Contao\System;
use Contao\Validator;
use Exception;
use WEM\PersonalDataManagerBundle\Model\Traits\PersonalDataTrait as PDMTrait;
use WEM\UtilsBundle\Classes\StringUtil;
use WEM\UtilsBundle\Model\Model as CoreModel;

/**
 * Reads and writes items.
 */
class FormStorageData extends CoreModel
{
    use PDMTrait;

    public const NO_FILE_UPLOADED = 'no_file_uploaded';

    public const FILE_UPLOADED_BUT_NOT_STORED = 'file_uploaded_but_not_stored';

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

    protected static string $personalDataPtable = 'tl_sm_form_storage_data';

    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_sm_form_storage_data';

    public function getPersonalDataEmailFieldValue(): string
    {
        $objFS = FormStorage::findItems(['id' => $this->pid]);
        if ($objFS && !empty($objFS->sender)) {
            return $objFS->sender;
        }

        $objFDS = self::findItems(['pid' => $this->pid, 'field_name' => 'email'], 1);
        if (!$objFDS instanceof Collection) {
            throw new Exception('Unable to find the email field');
        }

        return $objFDS->value;
    }

    public function shouldManagePersonalData(): bool
    {
        return (bool) $this->contains_personal_data;
    }

    public function getValueAsString(): string
    {
        return StringUtil::getFormStorageDataValueAsString($this->value);
    }

    public function getValueAsStringFormatted(): string
    {
        $value = $this->getValueAsString();
        switch ($this->field_type) {
            case 'textarea':
            case 'textareacustom':
                $value = nl2br($value ?? '');
                break;
            case 'upload':
                switch ($value) {
                    case self::NO_FILE_UPLOADED:
                        $value = $GLOBALS['TL_LANG']['WEMSG']['FDM']['ERROR']['noFileUploaded'];
                        break;
                    case self::FILE_UPLOADED_BUT_NOT_STORED:
                        $value = $GLOBALS['TL_LANG']['WEMSG']['FDM']['ERROR']['fileUploadedButNotStored'];
                        break;
                    default:
                        if (Validator::isStringUuid($value)) {
                            // we should have an UUID here
                            $objFile = FilesModel::findByUuid($value);
                            $value = $objFile ? $objFile->path : $GLOBALS['TL_LANG']['WEMSG']['FDM']['ERROR']['uploadedFileNotFound'];
                        }

                        break;
                }

                break;
        }

        return $value;
    }

    public static function deleteAll(): void
    {
        $objStatement = Database::getInstance()->prepare(sprintf('DELETE FROM %s', self::getTable()));
        $objStatement->execute();

        $manager = System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager');
        $manager->deleteByPtable(self::getTable());
    }
}
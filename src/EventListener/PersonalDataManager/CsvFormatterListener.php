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

use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\PersonalDataManagerBundle\Model\PersonalData as PersonalDataModel;
use WEM\WEMFormDataManagerBundle\Model\FormStorage;
use WEM\WEMFormDataManagerBundle\Model\FormStorageData;

class CsvFormatterListener
{
    protected TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function formatSingle(PersonalDataModel $personalData, array $header, array $row): array
    {
        if ($personalData->ptable === FormStorageData::getTable()) {
            $objFormStorageData = FormStorageData::findByPk($personalData->pid);
            return [
                FormStorage::getTable(),
                $personalData->email,
                $objFormStorageData->field_label,
                $personalData->anonymized ? $personalData->value : '"'.$objFormStorageData->getValueAsString().'"',
                $personalData->anonymized ? $this->translator->trans('WEM.PEDAMA.CSV.columnAnonymizedValueYes', [], 'contao_default') : $this->translator->trans('WEM.PEDAMA.CSV.columnAnonymizedValueNo', [], 'contao_default'),
            ];
        }

        return $row;
    }
}

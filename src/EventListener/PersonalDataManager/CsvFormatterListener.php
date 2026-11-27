<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\EventListener\PersonalDataManager;

use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\ContaoFormDataManagerBundle\Model\FormStorage;
use WEM\ContaoFormDataManagerBundle\Model\FormStorageData;
use WEM\PersonalDataManagerBundle\Model\PersonalData;

/**
 * Registered as $GLOBALS['WEM_HOOKS']['formatSinglePersonalDataForCsvExport'].
 */
class CsvFormatterListener
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function formatSingle(PersonalData $personalData, array $header, array $row): array
    {
        if (FormStorageData::getTable() !== $personalData->ptable) {
            return $row;
        }

        $objFormStorageData = FormStorageData::findByPk($personalData->pid);

        return [
            FormStorage::getTable(),
            $personalData->email,
            $objFormStorageData->field_label,
            $personalData->anonymized ? $personalData->value : '"' . $objFormStorageData->getValueAsString() . '"',
            $this->translator->trans($personalData->anonymized ? 'WEM.PEDAMA.CSV.columnAnonymizedValueYes' : 'WEM.PEDAMA.CSV.columnAnonymizedValueNo', [], 'contao_default'),
        ];
    }
}

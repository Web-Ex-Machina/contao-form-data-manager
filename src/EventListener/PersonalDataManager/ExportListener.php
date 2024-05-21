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

use Contao\Model;
use Contao\Model\Collection;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\PersonalDataManagerBundle\Model\PersonalData;
use WEM\WEMFormDataManagerBundle\Model\FormStorage;
use WEM\WEMFormDataManagerBundle\Model\FormStorageData;

class ExportListener
{
    protected TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function exportByPidAndPtableAndEmail(int $pid, string $ptable, string $email, ?Collection $pdms): ?Collection
    {
        if ($ptable === FormStorage::getTable()) {
            $arrModels = $pdms instanceof Collection ? $pdms->getModels() : [];
            $formStorageData = FormStorageData::findBy('pid', $pid);
            if ($formStorageData) {
                while ($formStorageData->next()) {
                    $objPersonalData = PersonalData::findOneByPidAndPTableAndEmail((int) $formStorageData->id, FormStorageData::getTable(), $email);
                    if ($objPersonalData instanceof Model) {
                        $arrModels[] = $objPersonalData;
                    }
                }
            }
            $pdms = \count($arrModels) > 0 ? new Collection($arrModels, PersonalData::getTable()) : null;
        }

        return $pdms;
    }
}

<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\Classes;

use Contao\Model;
use Contao\Model\Collection;
use WEM\ContaoFormDataManagerBundle\Model\FormStorageData;
use WEM\PersonalDataManagerBundle\Model\PersonalData;

/**
 * Appends the personal data of every data row of a form storage to the given collection.
 *
 * @internal
 */
final class FormStoragePersonalData
{
    public static function collect(int $formStorageId, string $email, Collection|null $pdms): Collection|null
    {
        $arrModels = $pdms instanceof Collection ? $pdms->getModels() : [];

        foreach (FormStorageData::findBy('pid', $formStorageId) ?? [] as $formStorageData) {
            $objPersonalData = PersonalData::findOneByPidAndPtableAndEmail((int) $formStorageData->id, FormStorageData::getTable(), $email);

            if ($objPersonalData instanceof Model) {
                $arrModels[] = $objPersonalData;
            }
        }

        return $arrModels !== [] ? new Collection($arrModels, PersonalData::getTable()) : null;
    }
}

<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\EventListener\PersonalDataManager;

use Contao\Model\Collection;
use WEM\ContaoFormDataManagerBundle\Classes\FormStoragePersonalData;
use WEM\ContaoFormDataManagerBundle\Model\FormStorage;

/**
 * Registered as $GLOBALS['WEM_HOOKS']['exportByPidAndPtableAndEmail'].
 */
class ExportListener
{
    public function exportByPidAndPtableAndEmail(int $pid, string $ptable, string $email, Collection|null $pdms): Collection|null
    {
        if (FormStorage::getTable() !== $ptable) {
            return $pdms;
        }

        return FormStoragePersonalData::collect($pid, $email, $pdms);
    }
}

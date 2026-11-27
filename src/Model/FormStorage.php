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
use Contao\Model\Collection;
use WEM\UtilsBundle\Model\Model as CoreModel;

/**
 * @property int    $id
 * @property int    $pid
 * @property int|string $tstamp
 * @property int|string $createdAt
 * @property string $status
 * @property string $note
 * @property string $token
 * @property float  $completion_percentage
 * @property int|string $delay_to_first_interaction
 * @property int|string $delay_to_submission
 * @property int    $current_page
 * @property string $current_page_url
 * @property int    $referer_page
 * @property string $referer_page_url
 * @property string $sender
 */
class FormStorage extends CoreModel
{
    protected static $strTable = 'tl_wem_form_storage';

    public function getSender(): string|null
    {
        if (! empty($this->sender)) {
            return $this->sender;
        }

        $formStorageDatas = FormStorageData::findItems([
            'pid' => $this->id,
            'field_name' => 'email',
        ], 1);

        if (! $formStorageDatas instanceof Collection) {
            return null;
        }

        return $formStorageDatas->first()->current()->getValueAsString();
    }

    public static function deleteAll(): void
    {
        Database::getInstance()->prepare(\sprintf('DELETE FROM %s', self::getTable()))->execute();

        FormStorageData::deleteAll();
    }
}

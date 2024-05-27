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
use Contao\Model\Collection;
use WEM\UtilsBundle\Model\Model as CoreModel;

/**
 * Reads and writes items.
 */
class FormStorage extends CoreModel
{
    public const STATUS_UNREAD = 'unread';

    public const STATUS_READ = 'read';

    public const STATUS_SPAM = 'spam';

    public const STATUS_OK = 'ok';

    public const STATUS_REPLIED = 'replied';

    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_sm_form_storage';

    public function getSender(): ?string
    {
        if (!empty($this->sender)) {
            return $this->sender;
        }

        $formStorageDatas = FormStorageData::findItems(['pid' => $this->id, 'field_name' => 'email'], 1);
        if (!$formStorageDatas instanceof Collection) {
            return null;
        }

        return $formStorageDatas->first()->current()->getValueAsString();
    }

    public static function deleteAll(): void
    {
        $objStatement = Database::getInstance()->prepare(sprintf('DELETE FROM %s', self::getTable()));
        $objStatement->execute();

        FormStorageData::deleteAll();
    }
}
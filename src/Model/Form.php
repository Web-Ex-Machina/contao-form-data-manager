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

use WEM\UtilsBundle\Model\Model as CoreModel;

/**
 * Reads and writes items.
 */
class Form extends CoreModel
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_form';

    /**
     * Default order column.
     */
    protected static $strOrderColumn = 'tstamp DESC';

    public function isManagedByFormDataManager(): bool
    {
        return (bool) $this->storeViaFormDataManager;
    }
}
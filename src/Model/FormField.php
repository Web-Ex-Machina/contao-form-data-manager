<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\Model;

use WEM\UtilsBundle\Model\Model as CoreModel;

/**
 * Gives access to the WEM query helpers (findItems, countItems…) on tl_form_field.
 */
class FormField extends CoreModel
{
    protected static $strTable = 'tl_form_field';

    protected static $strOrderColumn = 'tstamp DESC';
}

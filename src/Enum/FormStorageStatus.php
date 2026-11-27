<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\Enum;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum FormStorageStatus: string implements TranslatableLabelInterface
{
    case Unread = 'unread';
    case Read = 'read';
    case Spam = 'spam';
    case Ok = 'ok';
    case Replied = 'replied';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('tl_wem_form_storage.status.' . $this->value, [], 'contao_tl_wem_form_storage');
    }
}

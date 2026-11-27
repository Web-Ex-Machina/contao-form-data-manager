<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use WEM\ContaoFormDataManagerBundle\Model\FormStorageData;
use WEM\PersonalDataManagerBundle\Dca\Field\Callback\Load as PdmLoadCallback;

/**
 * Decrypts the stored value through the Personal Data Manager when the field contains personal data.
 */
#[AsCallback(table: 'tl_wem_form_storage_data', target: 'fields.value.load')]
class FormStorageDataValueListener
{
    public function __construct(
        #[Autowire(service: 'wem.personal_data_manager.dca.field.callback.load')]
        private readonly PdmLoadCallback $pdmCallback,
    ) {
        $this->pdmCallback->setFrontendField('value')->setTable(FormStorageData::getTable());
    }

    public function __invoke(mixed $value, DataContainer $dc): mixed
    {
        if (! ($dc->getCurrentRecord()['contains_personal_data'] ?? false)) {
            return $value;
        }

        return $this->pdmCallback->invokeBackend($value, $dc);
    }
}

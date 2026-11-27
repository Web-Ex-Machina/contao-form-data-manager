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
use Contao\FormFieldModel;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\ContaoFormDataManagerBundle\Model\FormStorageData;

class FormStorageDataListener
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    #[AsCallback(table: 'tl_wem_form_storage_data', target: 'list.sorting.child_record')]
    public function listItems(array $row): string
    {
        $objFormStorageData = FormStorageData::findByPk($row['id']);
        $objFormField = FormFieldModel::findById($row['field']);

        $strFormFieldLabel = $objFormField instanceof FormFieldModel
            ? ($objFormField->label ?: $objFormField->placeholder ?: $objFormField->name)
            : $objFormStorageData->field_label;

        return \sprintf(
            '<div><b>%s</b> : %s<br /><b>%s</b> : %s</div>',
            $this->translator->trans('tl_wem_form_storage_data.field.0', [], 'contao_tl_wem_form_storage_data'),
            $strFormFieldLabel,
            $this->translator->trans('tl_wem_form_storage_data.value.0', [], 'contao_tl_wem_form_storage_data'),
            $objFormStorageData->getValueAsString(),
        );
    }
}

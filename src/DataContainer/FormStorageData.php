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

namespace WEM\ContaoFormDataManagerBundle\DataContainer;

use Contao\FormFieldModel;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\ContaoFormDataManagerBundle\Model\FormStorage;
use WEM\ContaoFormDataManagerBundle\Model\FormStorageData as FormStorageDataModel;

class FormStorageData
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function listItems(array $row): string
    {
        $objFormStorageData = FormStorageDataModel::findByPk($row['id']);
        $objFormField = FormFieldModel::findById($row['field']);

        $strFormFieldLabel = '';
        if(!$objFormField){
            $strFormFieldLabel = $objFormStorageData->field_label;
        }else{
            if($objFormField->label){
                $strFormFieldLabel = $objFormField->label;
            }else if($objFormField->placeholder){
                $strFormFieldLabel = $objFormField->placeholder;
            }else{
                $strFormFieldLabel = $objFormField->name;
            }
        }

        return sprintf('<div><b>%s</b> : %s<br /><b>%s</b> : %s</div>', $this->translator->trans('tl_wem_form_storage_data.field.0', [], 'contao_default'), $strFormFieldLabel, $this->translator->trans('tl_wem_form_storage_data.value.0', [], 'contao_default'), $objFormStorageData->getValueAsString());
    }
}
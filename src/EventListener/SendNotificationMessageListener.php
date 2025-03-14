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

namespace WEM\ContaoFormDataManagerBundle\EventListener;

use Contao\FormFieldModel;

class SendNotificationMessageListener
{

    /**
     * This method takes a message object, an array of tokens, a language code, and a gateway model object.
     * It processes the tokens and extracts useful data from them, stores the data in the appropriate arrays,
     * and formats the data as strings. Finally, it returns true.
     *
     * @param mixed $objMessage The message object
     * @param array $arrTokens An array of tokens
     * @param string $language The language code
     * @param mixed $objGatewayModel The gateway model object
     * @return bool Returns true
     */
    public function __invoke($objMessage, array &$arrTokens, string $language, $objGatewayModel): bool
    {
        $arrTokens2 = [];

        foreach($arrTokens as $key=>$value){
            $chunks = explode('_',$key);
            if($chunks === []){
                continue;
            }

            if('form' === $chunks[0]){
                unset($chunks[0]);
                $formFieldKey = implode('',$chunks);
                $arrTokens2[$formFieldKey]['value'] = $value;
            }elseif('formlabel' === $chunks[0]
            ){
                unset($chunks[0]);
                $formFieldKey = implode('',$chunks);
                $arrTokens2[$formFieldKey]['label'] = $value;
            }
        }

        foreach($arrTokens2 as $fieldName => $fieldDefinition){
            $objFormField = FormFieldModel::findOneBy(['pid = ?','name = ?'],[$arrTokens['formconfig_id'],$fieldName]);
            if(!$objFormField
            || $objFormField->is_technical_field 
            ){
                unset($arrTokens2[$fieldName]);
                continue;
            }

            $arrTokens['useful_data_arr'][$fieldDefinition['label']] = $fieldDefinition['value'];
            if(!empty($fieldDefinition['value'])){
                $arrTokens['useful_data_filled_arr'][$fieldDefinition['label']] = $fieldDefinition['value'];
            }
        }

        $arrTokens['useful_data'] = '';
        $arrTokens['useful_data_text'] = '';
        
        foreach($arrTokens['useful_data_arr'] as $label => $value){
            $arrTokens['useful_data'] .= \sprintf('%s: %s<br />', $label, $value);
            $arrTokens['useful_data_text'] .= \sprintf("%s: %s\n", $label, $value);
        }

        $arrTokens['useful_data_filled'] = '';
        $arrTokens['useful_data_filled_text'] = '';

        foreach($arrTokens['useful_data_filled_arr'] as $label => $value){
            $arrTokens['useful_data_filled'] .= \sprintf('%s: %s<br />', $label, $value);
            $arrTokens['useful_data_filled_text'] .= \sprintf("%s: %s\n", $label, $value);
        }

        unset($arrTokens['useful_data_arr']);
        unset($arrTokens['useful_data_filled_arr']);

        return true;
    }
}

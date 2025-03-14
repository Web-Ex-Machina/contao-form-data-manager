<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * Copyright (c) 2024-2025 Web ex Machina
 *
 * @category ContaoBundle
 * @package  Web-Ex-Machina/contao-form-data-manager
 * @author   Web ex Machina <contact@webexmachina.fr>
 * @link     https://github.com/Web-Ex-Machina/contao-form-data-manager/
 */

namespace WEM\ContaoFormDataManagerBundle\EventListener;

use Contao\Environment;
use Contao\Form;
use Contao\FormFieldModel;
use Contao\System;

class CompileFormFieldsListener
{
    public function __construct()
    {
    }

    /**
     * Sets the fdm_formdatamanager javascript file for the given form.
     *
     * @param array  $arrFields the array fields for the form
     * @param string $formId    the ID of the form
     * @param Form   $form      the form object
     */
    public function __invoke(
        array $arrFields,
        string $formId,
        Form $form
    ): array {
        $GLOBALS['TL_JAVASCRIPT']['fdm_formdatamanager'] = 'bundles/contaoformdatamanager/js/formdatamanager/frontend.js';
        if ((bool) $form->getModel()->storeViaFormDataManager) {
            global $objPage;

            $objFormFieldFirstAppearance = (new FormFieldModel());
            $objFormFieldFirstAppearance->name = 'fdm[first_appearance]';
            $objFormFieldFirstAppearance->type = 'hidden';
            $objFormFieldFirstAppearance->is_technical_field = 1;
            $arrFields['first_appearance'] = $objFormFieldFirstAppearance;

            $objFormFieldFirstInteraction = (new FormFieldModel());
            $objFormFieldFirstInteraction->name = 'fdm[first_interaction]';
            $objFormFieldFirstInteraction->type = 'hidden';
            $objFormFieldFirstInteraction->is_technical_field = 1;
            $arrFields['first_interaction'] = $objFormFieldFirstInteraction;

            $objFormFieldCurrentPage = (new FormFieldModel());
            $objFormFieldCurrentPage->name = 'fdm[current_page]';
            $objFormFieldCurrentPage->type = 'hidden';
            $objFormFieldCurrentPage->is_technical_field = 1;
            $objFormFieldCurrentPage->value = $objPage ? $objPage->id : 0;
            $arrFields['current_page'] = $objFormFieldCurrentPage;

            $objFormFieldCurrentPage = (new FormFieldModel());
            $objFormFieldCurrentPage->name = 'fdm[current_page_url]';
            $objFormFieldCurrentPage->type = 'hidden';
            $objFormFieldCurrentPage->is_technical_field = 1;
            $objFormFieldCurrentPage->value = Environment::get('uri');
            $arrFields['current_page_url'] = $objFormFieldCurrentPage;

            // Previous page
            $objFormFieldRefererPage = (new FormFieldModel());
            $objFormFieldRefererPage->name = 'fdm[referer_page_url]';
            $objFormFieldRefererPage->type = 'hidden';
            $objFormFieldRefererPage->is_technical_field = 1;
            $objFormFieldRefererPage->value = System::getReferer();
            $arrFields['referer_page_url'] = $objFormFieldRefererPage;
        }

        return $arrFields;
    }
}

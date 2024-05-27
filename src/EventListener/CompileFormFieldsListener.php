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

namespace WEM\WEMFormDataManagerBundle\EventListener;

use Contao\Form;


class CompileFormFieldsListener
{
    protected array $listeners;

    public function __construct(

        array $listeners
    ) {


        $this->listeners = $listeners;
    }

    /**
     * Sets the fdm_formdatamanager javascript file for the given form.
     *
     * @param array $arrFields The array fields for the form.
     * @param string $formId The ID of the form.
     * @param Form $form The form object.
     *
     * @return void
     */
    public function __invoke(
        array $arrFields,
        string $formId,
        Form $form
    ): void {
        $GLOBALS['TL_JAVASCRIPT']['fdm_formdatamanager'] = 'bundles/wemformdatamanager/js/module/formdatamanager/frontend.js';
    }

}

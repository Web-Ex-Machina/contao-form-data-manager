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

namespace WEM\WEMFormDataManagerBundle\Backend\Module\FormDataManager\EventListener;

use Contao\Form;


class CompileFormFieldsListener
{
    protected array $listeners;

    public function __construct(

        array $listeners
    ) {


        $this->listeners = $listeners;
    }

    public function __invoke(
        array $arrFields,
        string $formId,
        Form $form
    ): void {
        $GLOBALS['TL_JAVASCRIPT']['fdm_formdatamanager'] = 'bundles/wemformdatamanager/js/module/formdatamanager/frontend.js';
    }

}

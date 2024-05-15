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

namespace WEM\WEMFormDataManagerBundle\Classes;

use Contao\Form;
use Contao\FormModel;
use Contao\Model;
use Contao\PageModel;
use Exception;
use WEM\WEMFormDataManagerBundle\Exceptions\Module\FormDataManager\EmailFieldNotMandatoryInForm;
use WEM\WEMFormDataManagerBundle\Exceptions\Module\FormDataManager\FormNotConfiguredToStoreValues;
use WEM\WEMFormDataManagerBundle\Exceptions\Module\FormDataManager\NoEmailFieldInForm;
use WEM\WEMFormDataManagerBundle\Model\FormField;

class FormUtil
{
    public static function getPageFromForm(Form $form): ?PageModel
    {
        $objParent = $form->getParent();
        $model = Model::getClassFromTable($objParent->ptable);
        $objGreatParent = $model::findOneById($objParent->pid);

        return PageModel::findOneById($objGreatParent->pid);
    }

    public static function checkFormConfigurationCompliantForFormDataManager($formId): void
    {
        $objForm = FormModel::findById($formId);
        if (!$objForm) {
            throw new Exception('Form not found');
        }

        if (!$objForm->storeViaFormDataManager) {
            throw new FormNotConfiguredToStoreValues($GLOBALS['TL_LANG']['WEMSG']['FDM']['FORM']['notManagedByFDM']);
        }

        $objFormFieldEmail = FormField::findItems(['pid' => $formId, 'name' => 'email']);
        if (!$objFormFieldEmail) {
            throw new NoEmailFieldInForm($GLOBALS['TL_LANG']['WEMSG']['FDM']['FORM']['noEmailField']);
        }

        if (!$objFormFieldEmail->mandatory) {
            throw new EmailFieldNotMandatoryInForm($GLOBALS['TL_LANG']['WEMSG']['FDM']['FORM']['emailFieldPresentButNotMandatory']);
        }
    }

    public static function isFormConfigurationCompliantForFormDataManager($formId): bool
    {
        try {
            self::checkFormConfigurationCompliantForFormDataManager($formId);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
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

namespace WEM\ContaoFormDataManagerBundle\Classes;

use Contao\Form;
use Contao\FormModel;
use Contao\Model;
use Contao\PageModel;
use WEM\ContaoFormDataManagerBundle\Exceptions\EmailFieldNotMandatoryInForm;
use WEM\ContaoFormDataManagerBundle\Exceptions\FormNotConfiguredToStoreValues;
use WEM\ContaoFormDataManagerBundle\Exceptions\NoEmailFieldInForm;
use WEM\ContaoFormDataManagerBundle\Model\FormField;
use WEM\UtilsBundle\Classes\StringUtil;

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
            throw new \Exception('Form not found');
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
        } catch (\Exception $exception) {
            return false;
        }
    }

    public static function getFormStorageDataValueAsString($mixed): string
    {
        $value = StringUtil::deserialize($mixed);
        if (\is_array($value)) {
            $formattedValue = [];
            foreach ($value as $valueChunk) {
                $formattedValue[] = \sprintf('%s (%s)', $valueChunk['label'], $valueChunk['value']);
            }
            $formattedValue = implode(',', $formattedValue);
        } else {
            $formattedValue = (string) $value;
        }

        return $formattedValue;
    }
}

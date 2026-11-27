<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\Classes;

use Contao\FormModel;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;
use WEM\ContaoFormDataManagerBundle\Exceptions\EmailFieldNotMandatoryInForm;
use WEM\ContaoFormDataManagerBundle\Exceptions\FormNotConfiguredToStoreValues;
use WEM\ContaoFormDataManagerBundle\Exceptions\NoEmailFieldInForm;
use WEM\ContaoFormDataManagerBundle\Model\FormField;

class FormUtil
{
    /**
     * @throws FormNotConfiguredToStoreValues
     * @throws NoEmailFieldInForm
     * @throws EmailFieldNotMandatoryInForm
     * @throws \RuntimeException
     */
    public static function checkFormConfigurationCompliantForFormDataManager(int|string $formId): void
    {
        $objForm = FormModel::findById($formId);

        if (! $objForm instanceof FormModel) {
            throw new \RuntimeException('Form not found');
        }

        $translator = System::getContainer()->get('translator');

        if (! $objForm->storeViaFormDataManager) {
            throw new FormNotConfiguredToStoreValues($translator->trans('WEM.FDM.FORM.notManagedByFDM', [], 'contao_default'));
        }

        $objFormFieldEmail = FormField::findItems([
            'pid' => $formId,
            'name' => 'email',
        ]);

        if (! $objFormFieldEmail instanceof Collection) {
            throw new NoEmailFieldInForm($translator->trans('WEM.FDM.FORM.noEmailField', [], 'contao_default'));
        }

        if (! $objFormFieldEmail->mandatory) {
            throw new EmailFieldNotMandatoryInForm($translator->trans('WEM.FDM.FORM.emailFieldPresentButNotMandatory', [], 'contao_default'));
        }
    }

    public static function isFormConfigurationCompliantForFormDataManager(int|string $formId): bool
    {
        try {
            self::checkFormConfigurationCompliantForFormDataManager($formId);
        } catch (\Exception) {
            return false;
        }

        return true;
    }

    public static function getFormStorageDataValueAsString(mixed $mixed): string
    {
        $value = StringUtil::deserialize($mixed);

        if (! \is_array($value)) {
            return (string) $value;
        }

        return implode(',', array_map(
            static fn (array $valueChunk): string => \sprintf('%s (%s)', $valueChunk['label'], $valueChunk['value']),
            $value,
        ));
    }
}

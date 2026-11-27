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
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\ContaoFormDataManagerBundle\Classes\FormUtil;
use WEM\ContaoFormDataManagerBundle\Exceptions\EmailFieldNotMandatoryInForm;
use WEM\ContaoFormDataManagerBundle\Exceptions\FormNotConfiguredToStoreValues;
use WEM\ContaoFormDataManagerBundle\Exceptions\NoEmailFieldInForm;
use WEM\ContaoFormDataManagerBundle\Model\FormField;
use WEM\ContaoFormDataManagerBundle\Model\FormStorage;

class FormListener
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    #[AsCallback(table: 'tl_form', target: 'list.label.label')]
    public function listItems(array $row, string $label, DataContainer $dc, array $labels): array
    {
        try {
            FormUtil::checkFormConfigurationCompliantForFormDataManager($row['id']);

            $labels[1] = FormStorage::countItems([
                'pid' => $row['id'],
            ]);
        } catch (\Exception $exception) {
            $labels[1] = $exception->getMessage();
        }

        return $labels;
    }

    /**
     * If the form has to be managed by the Form Data Manager, make sure it has a mandatory email field.
     */
    #[AsCallback(table: 'tl_form', target: 'config.onsubmit')]
    public function onSubmit(DataContainer $dc): void
    {
        try {
            FormUtil::checkFormConfigurationCompliantForFormDataManager($dc->id);
        } catch (NoEmailFieldInForm) {
            $objFormFieldEmail = new FormField();
            $objFormFieldEmail->pid = $dc->id;
            $objFormFieldEmail->type = 'text';
            $objFormFieldEmail->rgxp = 'email';
            $objFormFieldEmail->name = 'email';
            $objFormFieldEmail->label = $this->translator->trans('WEM.FDM.FORM.emailFieldLabel', [], 'contao_default');
            $objFormFieldEmail->placeholder = $this->translator->trans('WEM.FDM.FORM.emailFieldPlaceholder', [], 'contao_default');
            $objFormFieldEmail->sorting = 32;
            $objFormFieldEmail->mandatory = 1;
            $objFormFieldEmail->tstamp = time();
            $objFormFieldEmail->save();
        } catch (EmailFieldNotMandatoryInForm) {
            $objFormFieldEmail = FormField::findItems([
                'pid' => $dc->id,
                'name' => 'email',
            ]);
            $objFormFieldEmail->mandatory = 1;
            $objFormFieldEmail->save();
        } catch (FormNotConfiguredToStoreValues|\RuntimeException) {
            // Nothing to do
        }
    }
}

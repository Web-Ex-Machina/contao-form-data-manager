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

namespace WEM\ContaoFormDataManagerBundle\DataContainer;

use Contao\Backend;
use Contao\DataContainer;
use WEM\ContaoFormDataManagerBundle\Classes\FormUtil;
use WEM\ContaoFormDataManagerBundle\Exceptions\EmailFieldNotMandatoryInForm;
use WEM\ContaoFormDataManagerBundle\Exceptions\FormNotConfiguredToStoreValues;
use WEM\ContaoFormDataManagerBundle\Exceptions\NoEmailFieldInForm;
use WEM\ContaoFormDataManagerBundle\Model\FormField;
use WEM\ContaoFormDataManagerBundle\Model\FormStorage;

// class Form extends \tl_form
class Form extends Backend
{
    /** @var Backend */
    private $parent;

    public function __construct()
    {
        parent::__construct();

        $this->parent = new \tl_form();
    }

    public function listItems(array $row, string $label, DataContainer $dc, array $labels): array
    {
        try {
            // check form configuration
            FormUtil::checkFormConfigurationCompliantForFormDataManager($row['id']);

            $labels[1] = FormStorage::countItems(['pid' => $row['id']]);
        } catch (FormNotConfiguredToStoreValues $e) {
            $labels[1] = $e->getMessage();
        } catch (NoEmailFieldInForm $e) {
            $labels[1] = $e->getMessage();
        } catch (EmailFieldNotMandatoryInForm $e) {
            $labels[1] = $e->getMessage();
        } catch (\Exception $e) {
            $labels[1] = $e->getMessage();
        }

        return $labels;
    }

    public function onSubmitCallback(DataContainer $dc): void
    {
        // if the form has to be managed by FDM, assign a mandatory email field
        try {
            // check form configuration
            FormUtil::checkFormConfigurationCompliantForFormDataManager($dc->id);
        } catch (FormNotConfiguredToStoreValues $e) {
            // do nothing
        } catch (NoEmailFieldInForm $e) {
            // add a mandatory email field
            $objFormFieldEmail = new FormField();
            $objFormFieldEmail->pid = $dc->id;
            $objFormFieldEmail->type = 'text';
            $objFormFieldEmail->rgxp = 'email';
            $objFormFieldEmail->name = 'email';
            $objFormFieldEmail->label = $GLOBALS['TL_LANG']['WEMSG']['FDM']['FORM']['emailFieldLabel'];
            $objFormFieldEmail->placeholder = $GLOBALS['TL_LANG']['WEMSG']['FDM']['FORM']['emailFieldPlaceholder'];
            $objFormFieldEmail->sorting = 32;
            $objFormFieldEmail->mandatory = 1;
            $objFormFieldEmail->tstamp = time();
            $objFormFieldEmail->save();
        } catch (EmailFieldNotMandatoryInForm $e) {
            // retrieve the email field and make it mandatory
            $objFormFieldEmail = FormField::findItems(['pid' => $dc->id, 'name' => 'email']);
            $objFormFieldEmail->mandatory = 1;
            $objFormFieldEmail->save();
        } catch (\Exception $e) {
            $labels[1] = $e->getMessage();
        }
    }
}

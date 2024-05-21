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

namespace WEM\WEMFormDataManagerBundle\DataContainer;

use Contao\Backend;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Exception;
use tl_form;
use WEM\WEMFormDataManagerBundle\Classes\FormUtil;
use WEM\WEMFormDataManagerBundle\Exceptions\Module\FormDataManager\EmailFieldNotMandatoryInForm;
use WEM\WEMFormDataManagerBundle\Exceptions\Module\FormDataManager\FormNotConfiguredToStoreValues;
use WEM\WEMFormDataManagerBundle\Exceptions\Module\FormDataManager\NoEmailFieldInForm;
use WEM\WEMFormDataManagerBundle\Model\FormField;

// class Form extends \tl_form
class Form extends Backend
{

    /** @var Backend */
    private $parent;

    public function __construct()
    {
        parent::__construct();

        $this->parent = new tl_form();
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
        } catch (Exception $e) {
            $labels[1] = $e->getMessage();
        }
    }

    /**
     * Check permissions to edit table tl_form.
     *
     * @throws AccessDeniedException
     */
    public function checkPermission(): void
    {
        // parent::checkPermission();
        $this->parent->checkPermission();

        // Check current action
        switch (Input::get('act')) {
            case 'delete':
                if ($this->isItemUsedBySmartgear((int) Input::get('id'))) {
                    throw new AccessDeniedException('Not enough permissions to '.Input::get('act').' form ID '.Input::get('id').'.');
                }
                break;
        }
    }

    /**
     * Return the delete form button.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function deleteItem($row, $href, $label, $title, $icon, $attributes)
    {
        if ($this->isItemUsedBySmartgear((int) $row['id'])) {
            return Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
        }

        return $this->parent->deleteForm($row, $href, $label, $title, $icon, $attributes);
    }

    /**
     * Check if the form is being used by Smartgear.
     *
     * @param int $id form's ID
     */
    protected function isItemUsedBySmartgear(int $id): bool
    {
        try {
            $formContactConfig = $this->configurationManager->load()->getSgFormContact();
            if ($formContactConfig->getSgInstallComplete() && $id === (int) $formContactConfig->getSgFormContact()) {
                return true;
            }
        } catch (\Exception $e) {
        }

        return false;
    }
}

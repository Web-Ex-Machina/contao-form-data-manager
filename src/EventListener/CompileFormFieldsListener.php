<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Form;
use Contao\FormFieldModel;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Adds the technical hidden fields used to compute the submission statistics.
 */
#[AsHook('compileFormFields')]
class CompileFormFieldsListener
{
    public const string JS_PATH = 'bundles/contaoformdatamanager/js/formdatamanager/frontend.js';

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    /**
     * @param array<string, FormFieldModel> $arrFields
     *
     * @return array<string, FormFieldModel>
     */
    public function __invoke(array $arrFields, string $formId, Form $form): array
    {
        if (! $form->getModel()->storeViaFormDataManager) {
            return $arrFields;
        }

        $GLOBALS['TL_JAVASCRIPT']['fdm_formdatamanager'] = self::JS_PATH . '|static';

        $request = $this->requestStack->getCurrentRequest();
        $page = $request?->attributes->get('pageModel');

        $arrFields['first_appearance'] = $this->createHiddenField('first_appearance');
        $arrFields['first_interaction'] = $this->createHiddenField('first_interaction');
        $arrFields['current_page'] = $this->createHiddenField('current_page', $page instanceof PageModel ? (string) $page->id : '0');
        $arrFields['current_page_url'] = $this->createHiddenField('current_page_url', (string) $request?->getUri());
        $arrFields['referer_page_url'] = $this->createHiddenField('referer_page_url', (string) $request?->headers->get('referer'));

        return $arrFields;
    }

    private function createHiddenField(string $name, string $value = ''): FormFieldModel
    {
        $field = new FormFieldModel();
        $field->name = \sprintf('fdm[%s]', $name);
        $field->type = 'hidden';
        $field->is_technical_field = 1;
        $field->value = $value;

        return $field;
    }
}

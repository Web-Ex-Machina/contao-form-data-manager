<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\EventListener;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Form;
use Contao\FormFieldModel;
use Contao\PageModel;
use Contao\StringUtil;
use Symfony\Cmf\Component\Routing\Candidates\CandidatesInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use WEM\ContaoFormDataManagerBundle\Classes\FormUtil;
use WEM\ContaoFormDataManagerBundle\Enum\FormStorageStatus;
use WEM\ContaoFormDataManagerBundle\Model\FormField;
use WEM\ContaoFormDataManagerBundle\Model\FormStorage;
use WEM\ContaoFormDataManagerBundle\Model\FormStorageData;

/**
 * Stores the submitted form data in the form storage.
 */
#[AsHook('processFormData')]
class ProcessFormDataListener
{
    private const array TECHNICAL_FIELDS = [
        'fdm[first_appearance]',
        'fdm[first_interaction]',
        'fdm[current_page]',
        'fdm[current_page_url]',
        'fdm[referer_page_url]',
    ];

    public function __construct(
        #[Autowire(service: 'contao.routing.locale_candidates')]
        private readonly CandidatesInterface $routingCandidates,
        private readonly ContaoCsrfTokenManager $csrfTokenManager,
    ) {
    }

    /**
     * @param array<string, mixed>      $submittedData
     * @param array<string, mixed>      $formData
     * @param array<string, mixed>|null $files
     * @param array<string, mixed>      $labels
     */
    public function __invoke(array $submittedData, array $formData, array|null $files, array $labels, Form $form): void
    {
        $formModel = $form->getModel();

        if (! $formModel->storeViaFormDataManager || ! FormUtil::isFormConfigurationCompliantForFormDataManager($formModel->id)) {
            return;
        }

        $files ??= [];
        $now = time();

        $objFormStorage = new FormStorage();
        $objFormStorage->tstamp = $now;
        $objFormStorage->createdAt = $now;
        $objFormStorage->pid = $formModel->id;
        $objFormStorage->status = FormStorageStatus::Unread->value;
        $objFormStorage->token = $this->csrfTokenManager->getDefaultTokenValue();
        $objFormStorage->completion_percentage = $this->calculateCompletionPercentage($submittedData, $files, $form);
        $objFormStorage->delay_to_first_interaction = $this->calculateDelayToFirstInteraction($submittedData['fdm[first_appearance]'] ?? '', $submittedData['fdm[first_interaction]'] ?? '');
        $objFormStorage->delay_to_submission = $this->calculateDelayToSubmission($submittedData['fdm[first_interaction]'] ?? '');
        $objFormStorage->current_page = (int) ($submittedData['fdm[current_page]'] ?? 0);
        $objFormStorage->current_page_url = $submittedData['fdm[current_page_url]'] ?? '';
        $objFormStorage->referer_page = $this->getRefererPageId($submittedData['fdm[referer_page_url]'] ?? '') ?? 0;
        $objFormStorage->referer_page_url = $submittedData['fdm[referer_page_url]'] ?? '';

        if (\array_key_exists('email', $submittedData)) {
            $objFormStorage->sender = $submittedData['email'];
        }

        $objFormStorage->save();

        if (\array_key_exists('email', $submittedData)) {
            $this->storeFieldValue('email', $submittedData['email'], $objFormStorage);
            unset($submittedData['email']);
        }

        // Empty fields are transmitted
        foreach (array_diff_key($submittedData, array_flip(self::TECHNICAL_FIELDS)) as $fieldName => $value) {
            $this->storeFieldValue((string) $fieldName, $value, $objFormStorage);
        }

        $this->storeFilesValues($files, $objFormStorage);
    }

    private function getRefererPageId(string $url): int|null
    {
        if ($url === '') {
            return null;
        }

        try {
            $candidates = $this->routingCandidates->getCandidates(Request::create($url));
        } catch (\Exception) {
            return null;
        }

        if ($candidates === []) {
            return null;
        }

        $objPage = PageModel::findOneByAlias($candidates[0]);

        return $objPage instanceof PageModel ? (int) $objPage->id : null;
    }

    /**
     * Delay in milliseconds between the first appearance of the form and the first interaction with it.
     */
    private function calculateDelayToFirstInteraction(string $firstAppearanceMs, string $firstInteractionMs): int
    {
        return (int) $firstInteractionMs - (int) $firstAppearanceMs;
    }

    /**
     * Delay in milliseconds between the first interaction with the form and its submission.
     */
    private function calculateDelayToSubmission(string $firstInteractionMs): int
    {
        return (int) (microtime(true) * 1000) - (int) $firstInteractionMs;
    }

    private function calculateCompletionPercentage(array $submittedData, array $files, Form $form): float
    {
        $fieldsTotal = 0;
        $fieldsCompleted = 0;

        foreach (FormFieldModel::findPublishedByPid($form->getModel()->id) ?? [] as $formField) {
            if (\in_array($formField->type, ['captcha', 'submit'], true)) {
                continue;
            }

            ++$fieldsTotal;

            if (! empty($submittedData[$formField->name]) || ! empty($files[$formField->name])) {
                ++$fieldsCompleted;
            }
        }

        return $fieldsTotal === 0 ? 0.0 : $fieldsCompleted * 100 / $fieldsTotal;
    }

    private function storeFieldValue(string $fieldName, mixed $value, FormStorage $objFormStorage): FormStorageData
    {
        $objFormField = $this->findFormField($fieldName, $objFormStorage);

        return $this->createFormStorageData($objFormStorage, $objFormField, $this->formatValueToStore($value, $objFormField));
    }

    /**
     * Empty file fields aren't transmitted, so every upload field of the form is stored.
     *
     * @return list<FormStorageData>
     */
    private function storeFilesValues(array $files, FormStorage $objFormStorage): array
    {
        $formStorageDatas = [];

        foreach (FormField::findItems([
            'type' => 'upload',
            'pid' => $objFormStorage->pid,
        ]) ?? [] as $formField) {
            $fileData = $files[$formField->name] ?? [];

            $value = match (true) {
                empty($fileData) => FormStorageData::NO_FILE_UPLOADED,
                (bool) $formField->storeFile => $fileData['uuid'],
                default => FormStorageData::FILE_UPLOADED_BUT_NOT_STORED,
            };

            $formStorageDatas[] = $this->createFormStorageData($objFormStorage, $formField, $value);
        }

        return $formStorageDatas;
    }

    private function findFormField(string $fieldName, FormStorage $objFormStorage): FormField
    {
        $objFormField = FormField::findItems([
            'name' => $fieldName,
            'pid' => $objFormStorage->pid,
        ], 1)?->current();

        if (! $objFormField instanceof FormField) {
            throw new \RuntimeException(\sprintf('Unable to find field "%s" in form "%s"', $fieldName, $objFormStorage->getRelated('pid')?->title));
        }

        return $objFormField;
    }

    private function createFormStorageData(FormStorage $objFormStorage, FormField $objFormField, mixed $value): FormStorageData
    {
        $objFormStorageData = new FormStorageData();
        $objFormStorageData->tstamp = time();
        $objFormStorageData->createdAt = time();
        $objFormStorageData->pid = $objFormStorage->id;
        $objFormStorageData->field = $objFormField->id;
        $objFormStorageData->field_label = $objFormField->label;
        $objFormStorageData->field_name = $objFormField->name;
        $objFormStorageData->field_type = $objFormField->type;
        $objFormStorageData->value = $value;
        $objFormStorageData->contains_personal_data = $objFormField->contains_personal_data;
        $objFormStorageData->save();

        return $objFormStorageData;
    }

    /**
     * Options based fields are stored as a serialized array of selected options (label + value).
     */
    private function formatValueToStore(mixed $submittedValue, FormField $objFormField): mixed
    {
        if (! \in_array($objFormField->type, ['radio', 'checkbox', 'select'], true)) {
            return $submittedValue;
        }

        $options = array_column(StringUtil::deserialize($objFormField->options, true), null, 'value');
        $optionsSelected = [];

        foreach ((array) $submittedValue as $submittedValueChunk) {
            $optionsSelected[$submittedValueChunk] = [
                'label' => $options[$submittedValueChunk]['label'] ?? $submittedValueChunk,
                'value' => $submittedValueChunk,
            ];
        }

        return serialize($optionsSelected);
    }
}

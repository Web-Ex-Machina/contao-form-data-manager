<?php

declare(strict_types=1);

/**
 * SMARTGEAR for Contao Open Source CMS
 * Copyright (c) 2015-2023 Web ex Machina
 *
 * @category ContaoBundle
 * @package  Web-Ex-Machina/contao-smartgear
 * @author   Web ex Machina <contact@webexmachina.fr>
 * @link     https://github.com/Web-Ex-Machina/contao-smartgear/
 */

namespace WEM\ContaoFormDataManagerBundle\EventListener;

use Contao\Form;
use Contao\FormFieldModel;
use Contao\Model;
use Contao\PageModel;
use Exception;

use Symfony\Component\HttpFoundation\Request;
use WEM\ContaoFormDataManagerBundle\Classes\FormUtil;
use WEM\ContaoFormDataManagerBundle\Model\FormField;
use WEM\ContaoFormDataManagerBundle\Model\FormStorage;
use WEM\ContaoFormDataManagerBundle\Model\FormStorageData;
use WEM\UtilsBundle\Classes\StringUtil;

//TODO PAS FINI DU TOUT, faire une BDD et tester si je peux faire un form et le recup a la zob.
class ProcessFormDataListener
{

    protected $routingCandidates;

    public function __construct($routingCandidates) {

        $this->routingCandidates = $routingCandidates;
    }

    /**
     * Handle form submission and store the form data in the form storage.
     *
     * @param array $submittedData The submitted form data.
     * @param array $formData The form data.
     * @param array|null $files The uploaded files.
     * @param array $labels The labels for the form fields.
     * @param Form $form The form object.
     *
     * @throws Exception If the form field is not found in the form.
     */
    public function __invoke(
        array $submittedData,
        array $formData,
        ?array $files,
        array $labels,
        Form $form
    ): void {

        if ($form->getModel()->storeViaFormDataManager) {
            if (FormUtil::isFormConfigurationCompliantForFormDataManager($form->getModel()->id)) {
                $objFormStorage = new FormStorage();

                $objFormStorage->tstamp = time();
                $objFormStorage->createdAt = time();
                $objFormStorage->pid = $form->getModel()->id;
                $objFormStorage->status = FormStorage::STATUS_UNREAD;
                $objFormStorage->token = REQUEST_TOKEN;
                $objFormStorage->completion_percentage = $this->calculateCompletionPercentage($submittedData, $files ?? [], $form);
                $objFormStorage->delay_to_first_interaction = $this->calculateDelayToFirstInteraction($submittedData['fdm[first_appearance]'], $submittedData['fdm[first_interaction]']);
                $objFormStorage->delay_to_submission = $this->calculateDelayToSubmission($submittedData['fdm[first_interaction]'], $form);
                $objFormStorage->current_page = (int) $submittedData['fdm[current_page]'];
                $objFormStorage->current_page_url = $submittedData['fdm[current_page_url]'];
                $objFormStorage->referer_page = $this->getRefererPageId($submittedData['fdm[referer_page_url]']) ?? 0;
                $objFormStorage->referer_page_url = $submittedData['fdm[referer_page_url]'];
                $objFormStorage->save();

                if (\array_key_exists('email', $submittedData)) {
                    $objFormStorage->sender = $submittedData['email'];
                    $objFormStorage->save();
                    $this->storeFieldValue('email', $submittedData['email'], $objFormStorage);
                    unset($submittedData['email']);
                }

                unset($submittedData['fdm[first_appearance]'], $submittedData['fdm[first_interaction]'], $submittedData['fdm[current_page]'], $submittedData['fdm[current_page_url]'], $submittedData['fdm[referer_page_url]']);

                // empty fields are transmitted
                foreach ($submittedData as $fieldName => $value) {
                    $this->storeFieldValue($fieldName, $value, $objFormStorage);
                }
                $this->storeFilesValues($files ?? [], $objFormStorage);
            }
        }

    }

    /**
     * Get the ID of the referring page.
     *
     * @param string $url The URL of the referring page.
     * @return int|null The ID of the referring page, or null if not found.
     */
    protected function getRefererPageId(string $url): ?int
    {
        $refererPageId = null;
        try {
            $refererPages = $this->routingCandidates->getCandidates(Request::create($url));
            if (\count($refererPages) > 0) {
                $objPage = PageModel::findByAlias($refererPages[0]);
                if ($objPage) {
                    $refererPageId = $objPage->id;
                }
            }
        } catch (Exception $e) {
        }

        return $refererPageId ? (int) $refererPageId : $refererPageId;
    }

    /**
     * Calculate the delay in milliseconds between the first appearance and first interaction.
     *
     * @param string $firstAppearanceMs The timestamp of the first appearance in milliseconds.
     * @param string $firstInteractionMs The timestamp of the first interaction in milliseconds.
     * @return int The delay in milliseconds.
     */
    protected function calculateDelayToFirstInteraction(string $firstAppearanceMs, string $firstInteractionMs): int
    {
        return (int) $firstInteractionMs - (int) $firstAppearanceMs;
    }

    /**
     * Calculate the delay to submission.
     *
     * @param string $firstInteractionMs The timestamp in milliseconds of the first interaction.
     * @param Form $form The form object.
     * @return int The delay to submission in milliseconds.
     */
    protected function calculateDelayToSubmission(string $firstInteractionMs, Form $form): int
    {
        return (int) ((int) (microtime(true) * 1000) - (int) $firstInteractionMs);
    }

    /**
     * Calculate the completion percentage of a form based on submitted data.
     *
     * @param array $submittedData The submitted data from the form.
     * @param array $files The submitted files from the form.
     * @param Form $form The form object.
     * @return float The completion percentage of the form.
     */
    protected function calculateCompletionPercentage(array $submittedData, array $files, Form $form): float
    {
        $formFields = FormFieldModel::findPublishedByPid($form->getModel()->id);
        $fieldsTotal = $formFields->count();
        $fieldsCompleted = 0;
        if ($formFields) {
            while ($formFields->next()) {
                $formField = $formFields->current();
                if (\in_array($formField->type, ['captcha', 'submit'], true)) {
                    --$fieldsTotal;
                    continue;
                }
                if ((\array_key_exists($formField->name, $submittedData) || \array_key_exists($formField->name, $files))
                    && !empty($submittedData[$formField->name])
                ) {
                    ++$fieldsCompleted;
                }
            }
        }

        return $fieldsCompleted * 100 / $fieldsTotal;
    }

    /**
     * Store the value of a form field in the form storage.
     *
     * @param string $fieldName The name of the form field.
     * @param mixed $value The value of the form field.
     * @param FormStorage $objFormStorage The form storage object.
     * @return FormStorageData The stored form storage data.
     * @throws Exception If the form field is not found in the form.
     */
    protected function storeFieldValue(string $fieldName, $value, FormStorage $objFormStorage): FormStorageData
    {
        $objFormField = FormField::findItems(['name' => $fieldName, 'pid' => $objFormStorage->pid], 1);
        if (!$objFormField) {
            throw new Exception(sprintf('Unable to find field "%s" in form "%s"', $fieldName, $objFormStorage->getRelated('pid')->name));
        }

        $objFormStorageData = new FormStorageData();
        $objFormStorageData->tstamp = time();
        $objFormStorageData->createdAt = time();
        $objFormStorageData->pid = $objFormStorage->id;
        $objFormStorageData->field = $objFormField->id;
        $objFormStorageData->field_label = $objFormField->label;
        $objFormStorageData->field_name = $objFormField->name;
        $objFormStorageData->field_type = $objFormField->type;
        $objFormStorageData->value = $this->formatValueToStore($value, $objFormField->current());
        $objFormStorageData->contains_personal_data = $objFormField->contains_personal_data;
        $objFormStorageData->save();

        return $objFormStorageData;
    }

    /**
     * Retrieve and store the values of file fields in the form storage
     *
     * @param array $files The files array containing the uploaded files
     * @param FormStorage $objFormStorage The form storage object
     * @return array The stored file values
     */
    protected function storeFilesValues(array $files, FormStorage $objFormStorage): array
    {
        $formStorageDatas = [];
        // empty file fields aren't transmitted
        $formFieldsFile = FormField::findItems(['type' => 'upload', 'pid' => $objFormStorage->pid]);
        if (!$formFieldsFile) {
            return $formStorageDatas;
        }
        while ($formFieldsFile->next()) {
            // if (\array_key_exists($formFieldsFile->name, $files)) {
            $formStorageDatas[] = $this->storeFileValue($formFieldsFile->name, $files[$formFieldsFile->name] ?? [], $objFormStorage);
            // }
        }

        return $formStorageDatas;
    }

    /**
     * Stores the file value in the database and returns the created FormStorageData object.
     *
     * @param string $fieldName The name of the field
     * @param array $fileData The data of the uploaded file
     * @param FormStorage $objFormStorage The form storage object
     * @return FormStorageData|null The created FormStorageData object, or null if no file uploaded
     * @throws Exception If the field cannot be found in the form
     */
    protected function storeFileValue(string $fieldName, array $fileData, FormStorage $objFormStorage): ?FormStorageData
    {
        $objFormField = FormField::findItems(['name' => $fieldName, 'pid' => $objFormStorage->pid], 1);
        if (!$objFormField) {
            throw new Exception(sprintf('Unable to find field "%s" in form "%s"', $fieldName, $objFormStorage->getRelated('pid')->name));
        }

        $value = '';
        if (empty($fileData)) {
            $value = FormStorageData::NO_FILE_UPLOADED;
        } elseif ($objFormField->storeFile) {
            $value = $fileData['uuid'];
        } else {
            $value = FormStorageData::FILE_UPLOADED_BUT_NOT_STORED;
        }

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
     * Formats the submitted value to store in the database based on the form field type.
     *
     * @param mixed $submittedValue The value submitted by the user
     * @param Model $objFormField The form field object
     * @return mixed The formatted value to store
     */
    protected function formatValueToStore($submittedValue, Model $objFormField)
    {
        $value = $submittedValue;
        switch ($objFormField->type) {
            case 'radio':
            case 'checkbox':
            case 'select':
                $options = StringUtil::deserialize($objFormField->options);
                $options2 = [];
                foreach ($options as $option) {
                    $options2[$option['value']] = $option;
                }
                $options = $options2;

                if (!\is_array($submittedValue)) {
                    $submittedValue = [$submittedValue];
                }
                $optionsSelected = [];
                foreach ($submittedValue as $submittedValueChunk) {
                    $optionsSelected[$submittedValueChunk] = ['label' => $options[$submittedValueChunk]['label'], 'value' => $submittedValueChunk];
                }
                $value = serialize($optionsSelected);
                break;
        }

        return $value;
    }
}

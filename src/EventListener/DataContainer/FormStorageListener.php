<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\EventListener\DataContainer;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\CoreBundle\DataContainer\DataContainerOperation;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Date;
use Contao\FormModel;
use Contao\Model\Collection;
use Contao\Validator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\ContaoFormDataManagerBundle\Controller\Backend\ExportController;
use WEM\ContaoFormDataManagerBundle\Model\FormStorage;
use WEM\ContaoFormDataManagerBundle\Model\FormStorageData;
use WEM\UtilsBundle\Classes\DateUtil;

class FormStorageListener
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack,
    ) {
    }

    #[AsCallback(table: 'tl_wem_form_storage', target: 'list.label.label')]
    public function listItems(array $row): array
    {
        $objFormStorageDataEmail = FormStorageData::findItems([
            'pid' => $row['id'],
            'field_label' => 'Email',
        ], 1);

        return [
            FormModel::findById($row['pid'])?->title ?? $row['pid'],
            Date::parse(Config::get('datimFormat'), (int) $row['tstamp']),
            $this->translator->trans('tl_wem_form_storage.status.' . $row['status'], [], 'contao_tl_wem_form_storage'),
            $row['sender'] ?: ($objFormStorageDataEmail?->value ?? 'NR'),
        ];
    }

    /**
     * Exports the submissions of the current form, or every submission outside of a form context.
     */
    #[AsCallback(table: 'tl_wem_form_storage', target: 'list.global_operations.export_all.button')]
    public function exportAllButton(DataContainerOperation $operation): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $formId = $request?->query->get('do') === 'form' ? $request->query->getInt('id') : 0;

        $operation->setUrl($this->urlGenerator->generate(ExportController::ROUTE_ALL, [
            'form' => $formId ?: null,
        ]));
    }

    #[AsCallback(table: 'tl_wem_form_storage', target: 'fields.form_storage_data.input_field')]
    public function showData(DataContainer $dc): string
    {
        $formStorageDatas = FormStorageData::findItems([
            'pid' => $dc->id,
        ]);
        $arrFormStorageDatas = [];

        foreach ($formStorageDatas ?? [] as $formStorageData) {
            $arrFormStorageDatas[$formStorageData->id] = [
                ...$formStorageData->row(),
                'raw_value' => $formStorageData->value,
                'value' => $formStorageData->getValueAsStringFormatted(),
                'is_uuid' => Validator::isStringUuid((string) $formStorageData->value),
            ];
        }

        $GLOBALS['TL_CSS']['fdm_backend'] = 'bundles/contaoformdatamanager/backend/backend.css|static';

        $objTemplate = new BackendTemplate('be_wem_form_storage_data');
        $objTemplate->arrFormStorageDatas = $arrFormStorageDatas;

        return $objTemplate->parse();
    }

    #[AsCallback(table: 'tl_wem_form_storage', target: 'config.onshow')]
    public function onShow(array $modalData, array $recordData, DataContainer $dc): array
    {
        $table = FormStorage::getTable();

        $modalData[$table][0][$this->getShowKey('delay_to_submission')] = DateUtil::humanReadableDuration((int) $recordData['delay_to_submission']);
        $modalData[$table][0][$this->getShowKey('delay_to_first_interaction')] = DateUtil::humanReadableDuration((int) $recordData['delay_to_first_interaction']);
        $modalData[$table][0][$this->getShowKey('completion_percentage')] = $recordData['completion_percentage'] . '%';

        $formStorageDatas = FormStorageData::findItems([
            'pid' => $dc->id,
        ]);

        if ($formStorageDatas instanceof Collection) {
            $modalData[FormStorageData::getTable()] = [
                0 => [],
            ];

            foreach ($formStorageDatas as $formStorageData) {
                $modalData[FormStorageData::getTable()][0][$formStorageData->field_label] = $formStorageData->getValueAsString();
            }
        }

        return $modalData;
    }

    #[AsCallback(table: 'tl_wem_form_storage', target: 'fields.delay_to_first_interaction.load')]
    #[AsCallback(table: 'tl_wem_form_storage', target: 'fields.delay_to_submission.load')]
    public function formatDuration(mixed $value): string
    {
        return DateUtil::humanReadableDuration((int) $value);
    }

    private function getShowKey(string $field): string
    {
        return \sprintf('%s <small>%s</small>', $this->translator->trans('tl_wem_form_storage.' . $field . '.0', [], 'contao_tl_wem_form_storage'), $field);
    }
}

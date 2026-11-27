<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\Service;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Contao\Model\Collection;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\ContaoFormDataManagerBundle\Model\FormStorage;
use WEM\ContaoFormDataManagerBundle\Model\FormStorageData;

/**
 * Builds the CSV export of form submissions.
 */
class CsvExporter
{
    private const array FIXED_COLUMNS = [
        'form' => 'pid',
        'submission_date' => 'createdAt',
        'status' => 'status',
        'sender' => 'sender',
        'completion_percentage' => 'completion_percentage',
        'delay_to_first_interaction' => 'delay_to_first_interaction',
        'delay_to_submission' => 'delay_to_submission',
    ];

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @param Collection<FormStorage> $rows
     */
    public function createResponse(Collection $rows, string $filename): Response
    {
        $response = new Response(
            mb_convert_encoding(StringUtil::decodeEntities($this->export($rows)), 'UTF-16LE', 'UTF-8'),
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/csv; charset=utf-16le',
            ],
        );

        $filename = str_replace(['/', '\\'], '_', $filename);

        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename,
            $this->asciiFallback($filename),
        ));

        return $response;
    }

    public function formatDate(int|string $timestamp): string
    {
        $this->framework->initialize();

        return Date::parse(Config::get('datimFormat'), (int) $timestamp);
    }

    /**
     * @param Collection<FormStorage> $rows
     */
    private function export(Collection $rows): string
    {
        $headers = $this->buildHeaders($rows);

        $csv = [implode(';', $headers)];

        foreach ($rows as $row) {
            $csv[] = implode(';', $this->buildRow($row, $headers));
        }

        return implode("\n", $csv);
    }

    /**
     * @param Collection<FormStorage> $rows
     *
     * @return array<string, string>
     */
    private function buildHeaders(Collection $rows): array
    {
        $headers = [];

        foreach (self::FIXED_COLUMNS as $key => $field) {
            $headers[$key] = $this->translator->trans('tl_wem_form_storage.' . $field . '.0', [], 'contao_tl_wem_form_storage');
        }

        foreach ($rows as $row) {
            // Find data and create the appropriate columns
            foreach (FormStorageData::findItems([
                'pid' => $row->id,
            ]) ?? [] as $formStorageData) {
                $headers[$formStorageData->field_name] = $formStorageData->field_name;
            }
        }

        return $headers;
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array<string, string>
     */
    private function buildRow(FormStorage $formStorage, array $headers): array
    {
        $row = array_fill_keys(
            array_keys($headers),
            $this->translator->trans('WEM.FDM.EXPORT.fieldNotPresentInForm', [], 'contao_default'),
        );

        $row['form'] = (string) $formStorage->getRelated('pid')?->title;
        $row['submission_date'] = $this->formatDate($formStorage->createdAt);
        $row['status'] = $this->translator->trans('tl_wem_form_storage.status.' . $formStorage->status, [], 'contao_tl_wem_form_storage');
        $row['sender'] = (string) $formStorage->getSender();
        $row['completion_percentage'] = (string) $formStorage->completion_percentage;
        $row['delay_to_first_interaction'] = (string) $formStorage->delay_to_first_interaction;
        $row['delay_to_submission'] = (string) $formStorage->delay_to_submission;

        foreach (FormStorageData::findItems([
            'pid' => $formStorage->id,
        ]) ?? [] as $formStorageData) {
            $row[$formStorageData->field_name] = '"' . StringUtil::decodeEntities($formStorageData->getValueAsString()) . '"';
        }

        return $row;
    }

    private function asciiFallback(string $filename): string
    {
        return preg_replace('/[^\x20-\x7E]|[\/\\\\%"]/', '_', $filename) ?? 'export.csv';
    }
}

<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\Controller\Backend;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\FormModel;
use Contao\Model\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use WEM\ContaoFormDataManagerBundle\Model\FormStorage;
use WEM\ContaoFormDataManagerBundle\Service\CsvExporter;

#[Route('%contao.backend.route_prefix%/form-data-manager/export', defaults: [
    '_scope' => 'backend',
])]
class ExportController extends AbstractController
{
    public const string ROUTE_SINGLE = 'wem_form_data_manager_export';

    public const string ROUTE_ALL = 'wem_form_data_manager_export_all';

    public function __construct(
        private readonly CsvExporter $exporter
    ) {
    }

    #[Route('/{id}', name: self::ROUTE_SINGLE, requirements: [
        'id' => '\d+',
    ], methods: ['GET'])]
    public function single(int $id): Response
    {
        $this->initializeContaoFramework();
        $this->denyAccessUnlessGrantedModule();

        $rows = $this->findRows([
            'id' => $id,
        ]);
        $formStorage = $rows->first()->current();

        return $this->exporter->createResponse($rows, \sprintf(
            'contact_%s_%s_%s.csv',
            $formStorage->getRelated('pid')?->title,
            $formStorage->getSender(),
            $this->exporter->formatDate($formStorage->createdAt),
        ));
    }

    #[Route('/all/{form}', name: self::ROUTE_ALL, requirements: [
        'form' => '\d+',
    ], defaults: [
        'form' => null,
    ], methods: ['GET'])]
    public function all(int|null $form = null): Response
    {
        $this->initializeContaoFramework();
        $this->denyAccessUnlessGrantedModule();

        if ($form === null) {
            return $this->exporter->createResponse($this->findRows([]), 'contacts.csv');
        }

        $formModel = $this->getContaoAdapter(FormModel::class)->findById($form) ?? throw new NotFoundHttpException('Form not found');

        return $this->exporter->createResponse($this->findRows([
            'pid' => $form,
        ]), \sprintf('contacts_%s.csv', $formModel->title));
    }

    /**
     * @return Collection<FormStorage>
     */
    private function findRows(array $criteria): Collection
    {
        return FormStorage::findItems($criteria, 0, 0, [
            'order' => 'createdAt DESC',
        ]) ?? throw new NotFoundHttpException('No submission found');
    }

    private function denyAccessUnlessGrantedModule(): void
    {
        if (
            ! $this->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'form')
            && ! $this->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'wem_form_data_manager')
        ) {
            throw new AccessDeniedException('Not enough permissions to export the form submissions.');
        }
    }
}

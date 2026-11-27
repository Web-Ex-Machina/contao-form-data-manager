<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\EventListener\PersonalDataManager;

use Contao\Config;
use Contao\Date;
use Contao\File;
use Contao\FilesModel;
use Contao\MemberGroupModel;
use Contao\Model;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Validator;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\ContaoFormDataManagerBundle\Classes\FormUtil;
use WEM\ContaoFormDataManagerBundle\Model\FormStorage;
use WEM\ContaoFormDataManagerBundle\Model\FormStorageData;
use WEM\PersonalDataManagerBundle\Model\PersonalData;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManagerUi;
use WEM\UtilsBundle\Classes\Files as FileUtil;

/**
 * Registered on the $GLOBALS['WEM_HOOKS'] of the Personal Data Manager UI.
 */
class UiListener
{
    private const string CSS_PATH = 'bundles/contaoformdatamanager/css/frontend.css';

    private const array HIDDEN_MEMBER_FIELDS = ['id', 'tstamp', 'password', 'dateAdded', 'lastLogin', 'loginAttempts', 'locked', 'session', 'secret', 'backupCodes', 'trustedTokenVersion', 'currentLogin'];

    private const array HIDDEN_FORM_STORAGE_FIELDS = ['id', 'tstamp', 'status', 'token', 'completion_percentage', 'delay_to_submission', 'delay_to_first_interaction', 'note'];

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly PersonalDataManagerUi $personalDataManagerUi,
    ) {
    }

    public function sortData(array $sorted, Collection|null $personalDatas): array
    {
        if (! isset($sorted[FormStorageData::getTable()])) {
            return $sorted;
        }

        $sorted2 = $sorted;
        $sorted2[FormStorage::getTable()] ??= [];

        foreach ($sorted[FormStorageData::getTable()] as $id => $idDatas) {
            /** @var FormStorage $objFormStorage */
            $objFormStorage = FormStorageData::findOneBy('id', $idDatas['personalDatas'][0]->pid)->getRelated('pid');

            if (! \array_key_exists($objFormStorage->id, $sorted2[FormStorage::getTable()])) {
                $sorted2[FormStorage::getTable()][$objFormStorage->id] = [
                    'originalModel' => $objFormStorage,
                    'personalDatas' => $this->getPersonalDataForFormStorage($objFormStorage),
                ];
            }

            unset($sorted2[FormStorageData::getTable()][$id]);
        }

        return $sorted2;
    }

    public function renderSingleItemTitle(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel, string $buffer): string
    {
        return match ($ptable) {
            'tl_member' => 'Member',
            FormStorage::getTable() => \sprintf('%s %s', 'Formulaire', FormStorage::findOneBy('id', $pid)->getRelated('pid')->title),
            default => $buffer,
        };
    }

    public function renderSingleItemBodyOriginalModelSingle(int $pid, string $ptable, string $email, string $field, mixed $value, array $personalDatas, Model $originalModel, string $buffer): string
    {
        $hiddenFields = match ($ptable) {
            'tl_member' => self::HIDDEN_MEMBER_FIELDS,
            FormStorage::getTable() => self::HIDDEN_FORM_STORAGE_FIELDS,
            default => [],
        };

        return \in_array($field, $hiddenFields, true) ? '' : $buffer;
    }

    public function renderSingleItemBodyOriginalModelSingleFieldValue(int $pid, string $ptable, string $email, string $field, mixed $value, array $personalDatas, Model $originalModel, string $buffer): string
    {
        if ($buffer === '') {
            return $this->renderNotFilled();
        }

        switch ($ptable) {
            case 'tl_member':
                switch ($field) {
                    case 'login':
                        return \sprintf('<input type="checkbox" readonly %s />', $value ? 'checked' : '');

                    case 'groups':
                        $buffer = '<ul>';

                        foreach (StringUtil::deserialize($value, true) as $groupId) {
                            $buffer .= \sprintf('<li>- %s</li>', MemberGroupModel::findById($groupId)?->name ?? $this->translator->trans('WEM.FDM.PDMUI.elementUnknown', [], 'contao_default'));
                        }

                        return $buffer . '</ul>';
                }

                break;

            case FormStorage::getTable():
                switch ($field) {
                    case 'pid':
                        return (string) FormStorage::findOneBy('id', $pid)->getRelated('pid')?->title;

                    case 'createdAt':
                        return Date::parse(Config::get('datimFormat'), (int) $value);

                    case 'current_page':
                    case 'referer_page':
                        return ($value ? PageModel::findById($value)?->title : null) ?? $buffer;
                }

                break;
        }

        return $buffer;
    }

    public function renderSingleItemBodyPersonalDataSingleFieldValue(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel, string $buffer): string
    {
        switch ($ptable) {
            case 'tl_member':
                if ($personalData->field === 'dateOfBirth') {
                    return $buffer !== '' ? Date::parse(Config::get('dateFormat'), (int) $buffer) : $buffer;
                }

                return $buffer !== '' ? $buffer : $this->renderNotFilled();

            case FormStorage::getTable():
                return $this->formatPersonalDataValue($pid, $ptable, $email, $personalData, $personalDatas, $originalModel);

            case FormStorageData::getTable():
                $value = $this->formatPersonalDataValue($pid, $ptable, $email, $personalData, $personalDatas, $originalModel);

                if (FormStorageData::findByPk($pid)?->field_type === 'upload' && Validator::isStringUuid($value)) {
                    return FilesModel::findByUuid($value)?->name ?? $this->translator->trans('WEM.FDM.PDMUI.fileNotFound', [], 'contao_default');
                }

                return $value;

            default:
                return $buffer !== '' ? $buffer : $this->renderNotFilled();
        }
    }

    public function renderSingleItemBodyPersonalDataSingleFieldLabel(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel, string $buffer): string
    {
        if (\in_array($ptable, [FormStorage::getTable(), FormStorageData::getTable()], true)) {
            return $personalData->field_label ?? $buffer;
        }

        return $buffer;
    }

    public function renderSingleItemBodyPersonalDataSingle(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel, string $buffer): string
    {
        if (FormStorage::getTable() === $ptable) {
            return $this->personalDataManagerUi->formatSingleItemBodyPersonalDataSingle((int) $personalData->pid, $personalData->ptable, $email, $personalData, $personalDatas, $originalModel);
        }

        return $buffer;
    }

    public function buildSingleItemBodyPersonalDataSingleButtons(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel, File|null $file, array $buttons): array
    {
        if (! $file instanceof File) {
            return $buttons;
        }

        $GLOBALS['TL_CSS']['fdm_pdm'] = self::CSS_PATH . '|static';

        if (FileUtil::isDisplayableInBrowser($file)) {
            $buttons['show'] = $this->renderFileButton($file, 'show', 'WEM.FDM.PDMUI.buttonShowFile');
        }

        $buttons['download'] = $this->renderFileButton($file, 'download', 'WEM.FDM.PDMUI.buttonDownloadFile');

        return $buttons;
    }

    /**
     * Gathers all the personal data attached to a form storage.
     *
     * @return list<Model>
     */
    private function getPersonalDataForFormStorage(FormStorage $objFormStorage): array
    {
        $arrPersonalDatas = [];

        foreach (FormStorageData::findItems([
            'pid' => $objFormStorage->id,
        ]) ?? [] as $formStorageData) {
            $objPersonalData = PersonalData::findOneByPidAndPtableAndField((int) $formStorageData->id, FormStorageData::getTable(), 'value');

            if (! $objPersonalData instanceof Collection) {
                continue;
            }

            $objPersonalData = $objPersonalData->current();
            $objPersonalData->setRow([
                ...$objPersonalData->row(),
                'field_label' => $formStorageData->field_label,
            ]);
            $arrPersonalDatas[] = $objPersonalData;
        }

        return $arrPersonalDatas;
    }

    private function formatPersonalDataValue(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        return FormUtil::getFormStorageDataValueAsString(
            $this->personalDataManagerUi->formatSingleItemBodyPersonalDataSingleFieldValue($pid, $ptable, $email, $personalData, $personalDatas, $originalModel),
        );
    }

    private function renderFileButton(File $file, string $action, string $label): string
    {
        return \sprintf(
            '<br /><a href="%1$s" class="pdm-button pdm-button_%2$s_file pdm-item__personal_data_single__button_%2$s_file" target="_blank" data-path="%3$s">%4$s</a>',
            $this->personalDataManagerUi->getUrl(),
            $action,
            $file->path,
            $this->translator->trans($label, [], 'contao_default'),
        );
    }

    private function renderNotFilled(): string
    {
        return \sprintf('<i>%s</i>', $this->translator->trans('WEM.FDM.PDMUI.notFilled', [], 'contao_default'));
    }
}

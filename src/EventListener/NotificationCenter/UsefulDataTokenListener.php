<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\EventListener\NotificationCenter;

use Contao\FormFieldModel;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Terminal42\NotificationCenterBundle\Event\CreateParcelEvent;
use Terminal42\NotificationCenterBundle\Event\GetTokenDefinitionsForNotificationTypeEvent;
use Terminal42\NotificationCenterBundle\NotificationType\FormGeneratorNotificationType;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\FormConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\HtmlTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;

/**
 * Adds the ##useful_data## tokens to the form generator notifications: the submitted values, without the
 * technical fields added by the Form Data Manager.
 */
class UsefulDataTokenListener
{
    private const array TOKENS = [
        'useful_data' => HtmlTokenDefinition::class,
        'useful_data_text' => TextTokenDefinition::class,
        'useful_data_filled' => HtmlTokenDefinition::class,
        'useful_data_filled_text' => TextTokenDefinition::class,
    ];

    public function __construct(
        private readonly TokenDefinitionFactoryInterface $tokenDefinitionFactory
    ) {
    }

    #[AsEventListener]
    public function onGetTokenDefinitions(GetTokenDefinitionsForNotificationTypeEvent $event): void
    {
        if ($event->getNotificationType()->getName() !== FormGeneratorNotificationType::NAME) {
            return;
        }

        foreach (self::TOKENS as $token => $definitionClass) {
            $event->addTokenDefinition($this->tokenDefinitionFactory->create($definitionClass, $token, 'form.' . $token));
        }
    }

    #[AsEventListener]
    public function onCreateParcel(CreateParcelEvent $event): void
    {
        $parcel = $event->getParcel();

        if (! $parcel->hasStamps([FormConfigStamp::class, TokenCollectionStamp::class])) {
            return;
        }

        $formId = $parcel->getStamp(FormConfigStamp::class)->formConfig->getId();
        $tokenCollection = $parcel->getStamp(TokenCollectionStamp::class)->tokenCollection;

        $usefulData = $this->getUsefulData($formId, $tokenCollection);
        $usefulDataFilled = array_filter($usefulData, static fn (mixed $value): bool => ! empty($value));

        $values = [
            'useful_data' => $this->format($usefulData, '<br />'),
            'useful_data_text' => $this->format($usefulData, "\n"),
            'useful_data_filled' => $this->format($usefulDataFilled, '<br />'),
            'useful_data_filled_text' => $this->format($usefulDataFilled, "\n"),
        ];

        foreach (self::TOKENS as $token => $definitionClass) {
            $tokenCollection->replaceToken(
                $this->tokenDefinitionFactory->create($definitionClass, $token, 'form.' . $token)->createToken($token, $values[$token]),
            );
        }
    }

    /**
     * @return array<string, mixed> The submitted values indexed by their label
     */
    private function getUsefulData(int $formId, TokenCollection $tokenCollection): array
    {
        $usefulData = [];

        foreach ($tokenCollection->toKeyValue() as $name => $value) {
            if (! str_starts_with((string) $name, 'form_')) {
                continue;
            }

            $fieldName = substr((string) $name, 5);
            $objFormField = FormFieldModel::findOneBy(['pid = ?', 'name = ?'], [$formId, $fieldName]);

            if (! $objFormField instanceof FormFieldModel || $objFormField->is_technical_field) {
                continue;
            }

            $label = $tokenCollection->getByName('formlabel_' . $fieldName)?->getValue() ?? $fieldName;
            $usefulData[(string) $label] = \is_array($value) ? implode(', ', $value) : $value;
        }

        return $usefulData;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function format(array $data, string $separator): string
    {
        $buffer = '';

        foreach ($data as $label => $value) {
            $buffer .= \sprintf('%s: %s', $label, $value) . $separator;
        }

        return $buffer;
    }
}

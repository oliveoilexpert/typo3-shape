<?php

declare(strict_types=1);

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\AfterFlexFormDataStructureParsedEvent;

final readonly class FlexformConfigurationModifier
{
	#[AsEventListener]
	public function __invoke(AfterFlexFormDataStructureParsedEvent $event): void
	{
		$identifier = $event->getIdentifier();
		if (
			$identifier['type'] === 'tca'
			&& $identifier['tableName'] === 'tx_shape_finisher'
			&& $identifier['dataStructureKey'] === 'UBOS\Shape\Domain\Finisher\SendEmailFinisher'
		) {
			$dataStructure = $event->getDataStructure();
			$dataStructure['sheets']['mail']['ROOT']['el']['template']['config']['items'] = [];
			$mailTemplates = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['shape']['finishers']['sendEmail']['templates'];
			foreach ($mailTemplates as $template => $config) {
				$dataStructure['sheets']['mail']['ROOT']['el']['template']['config']['items'][] = [
					'label' => $config['label'],
					'value' => $template,
					'group' => $config['group'] ?? null,
					'icon' => $config['icon'] ?? null,
				];
				if ($config['fields'] ?? false) {
					foreach ($config['fields'] as $field => $fieldConfig) {
						$dataStructure['sheets']['content']['ROOT']['el'][$field] = $fieldConfig;
					}
				}
			}
			$event->setDataStructure($dataStructure);
		}

	}
}
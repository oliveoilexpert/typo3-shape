<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\FormRuntime;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use UBOS\Shape\Domain;

class FinisherContext
{
	public function __construct(
		public readonly FormRuntime $runtime,
		public ?ResponseInterface   $response = null,
		public array                $finishedActionArguments = [],
	) {}

	public function executeFinisher(?Core\Domain\Record $record = null, string $className = '', array $settings = []): void
	{
		if ($record) {
			$settings = Core\Utility\GeneralUtility::makeInstance(Core\Service\FlexFormService::class)
				->convertFlexFormContentToArray($record->getRawRecord()->get('settings'));
			$className = $record->get('type');
		}
		$finisher = new $className(
			$this,
			$settings,
		);
		if (!($finisher instanceof Domain\Finisher\AbstractFinisher)) {
			return;
		}
		$finisher->execute();
	}
}
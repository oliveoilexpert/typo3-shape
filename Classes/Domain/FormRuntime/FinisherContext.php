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

	public function executeFinisher(
		?Core\Domain\Record $record = null,
		string $finisherClassName = '',
		array $settings = [],
	): void
	{
		if ($record) {
			$finisherClassName = $record->get('type');
			$settings = Core\Utility\GeneralUtility::makeInstance(Core\Service\FlexFormService::class)
				->convertFlexFormContentToArray($record->getRawRecord()->get('settings'));
		}
		if (!class_exists($finisherClassName)) {
			throw new \InvalidArgumentException('Argument "finisherClassName" must the name of a class that exists.', 1741369248);
		}
		$finisher = new $finisherClassName(
			$this,
			$settings,
			$record
		);
		if (!($finisher instanceof Domain\Finisher\AbstractFinisher)) {
			throw new \InvalidArgumentException('Argument "finisherClassName" must the name of a class that extends UBOS\Shape\Domain\Finisher\AbstractFinisher.', 1741369249);
			return;
		}
		$finisher->execute();
	}
}
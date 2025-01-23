<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;

class SaveToAnyTableFinisher extends AbstractFinisher
{
	public function execute(): ?ResponseInterface
	{
		$this->settings = array_merge([
			'table' => '',
			'storagePage' => '',
			'mapping' => [],
		], $this->settings);
		if (!$this->settings['table']) {
			return null;
		}
		$queryBuilder = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable($this->settings['table']);
		$values = [
			'pid' => (int)($this->settings['storagePage'] ?: $this->contentRecord->getPid() ?? $this->formRecord->getPid()),
		];

		foreach ($this->settings['mapping'] as $column => $field) {
			$values[$column] = $this->formValues[$field] ?? '';
		}
		$queryBuilder->insert($this->settings['table'])
			->values($values)
			->executeQuery();
		return null;
	}
}
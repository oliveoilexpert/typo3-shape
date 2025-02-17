<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;

class SaveToDatabaseFinisher extends AbstractFinisher
{
	public function execute(): void
	{
		$this->settings = array_merge([
			'table' => '',
			'storagePage' => '',
			'mapping' => [],
		], $this->settings);
		if (!$this->settings['table']) {
			return;
		}
		$queryBuilder = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable($this->settings['table']);
		$values = [
			'pid' => (int)($this->settings['storagePage'] ?: $this->getPlugin()->getPid() ?? $this->getForm()->getPid()),
		];

		foreach ($this->settings['mapping'] as $column => $field) {
			if (!$field) continue;
			$value = $this->getFormValues()[$field] ?? $field;
			if (is_array($value)) {
				try {
					$value = implode(',', $value);
				} catch (\Throwable $e) {
					$value = json_encode($value);
				}
			}
			$values[$column] = $value;
		}
		$queryBuilder->insert($this->settings['table'])
			->values($values)
			->executeStatement();
	}
}
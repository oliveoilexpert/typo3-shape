<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;

class SaveToDatabaseFinisher extends AbstractFinisher
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
			'pid' => (int)($this->settings['storagePage'] ?: $this->context->plugin->getPid() ?? $this->context->form->getPid()),
		];

		foreach ($this->settings['mapping'] as $column => $field) {
			if (!$field) continue;
			$values[$column] = $this->context->session->values[$field] ?? '';
		}
		$queryBuilder->insert($this->settings['table'])
			->values($values)
			->executeStatement();
		return null;
	}
}
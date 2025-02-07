<?php

declare(strict_types=1);

namespace UBOS\Shape\Validation;

use TYPO3\CMS\Core;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class UniqueInTableValidator extends AbstractValidator
{
	protected $supportedOptions = [
		'table' => ['', 'Name of the table', 'string', true],
		'column' => ['', 'Name of the column to look for value in', 'string', true],
		'where' => [[], 'Additional where clauses', 'array', false],

	];

	public function isValid(mixed $value): void
	{
		$pool = Core\Utility\GeneralUtility::makeInstance(Core\Database\ConnectionPool::class);
		$query = $pool->getQueryBuilderForTable($this->options['table']);
		$count = $query
			->count('uid')
			->from($this->options['table'])
			->setMaxResults(1)
			->where(
				$this->options['column'] . ' = ' . $query->createNamedParameter($value),
				...$this->options['where']
			)
			->executeQuery()->fetchOne();
		if ($count) {
			$this->addError(
				'LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:validator.unique_in_table.error',
				// todo: find a better error code
				1221565130
			);
		}
	}
}

<?php

declare(strict_types=1);

namespace UBOS\Shape\Validation;

use TYPO3\CMS\Core;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class UniqueInSubmissionsValidator extends AbstractValidator
{
	protected $supportedOptions = [
		'fieldName' => ['', 'Name of the field to look for value in', 'string', true],
		'pluginUid' => [0, 'Plugin parent of submissions to look for value in', 'integer', false],
		'formUid' => [0, 'Form parent of submissions to look for value in', 'integer', false],
	];

	public function isValid(mixed $value): void
	{
		$pool = Core\Utility\GeneralUtility::makeInstance(Core\Database\ConnectionPool::class);
		$query = $pool->getQueryBuilderForTable('tx_shape_form_submission');
		$where = [
			'form_values->"$.' . $this->options['fieldName'] .'"' . ' = ' . $query->createNamedParameter($value),
		];
		if ($this->options['pluginUid']) {
			$where[] = $query->expr()->eq('plugin', $query->createNamedParameter($this->options['pluginUid']));
		}
		if ($this->options['formUid']) {
			$where[] = $query->expr()->eq('form', $query->createNamedParameter($this->options['formUid']));
		}
		$count = $query
			->count('uid')
			->from('tx_shape_form_submission')
			->setMaxResults(1)
			->where(...$where)
			->executeQuery()->fetchOne();
		if ($count) {
			$this->addError(
				'LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:validator.unique_in_submission.error',
				// todo: find a better error code
				1221565130
			);
		}
	}
}

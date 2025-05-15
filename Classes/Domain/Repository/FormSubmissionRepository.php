<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Repository;

use TYPO3\CMS\Core;

class FormSubmissionRepository extends AbstractRecordRepository
{
	public function getTableName(): string
	{
		return 'tx_shape_form_submission';
	}

	protected string|false $languageColumn = false;

	public function isUniqueValue(string $fieldName, mixed $value, int $pluginUid = 0, int $formUid = 0): bool
	{
		$builder = $this->getQueryBuilder();
		$where = [
			'form_values->"$.' . $fieldName .'"' . ' = ' . $builder->createNamedParameter($value),
		];
		if ($pluginUid) {
			$where[] = $builder->expr()->eq('plugin', $builder->createNamedParameter($pluginUid));
		}
		if ($formUid) {
			$where[] = $builder->expr()->eq('form', $builder->createNamedParameter($formUid));
		}
		$count = $this->countWhere(...$where);
		return !$count;
	}
}
<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Repository;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core;

#[Autoconfigure(public: true, shared: false)]
class GenericRepository extends AbstractRecordRepository
{
	protected string $tableName;

	public function getTableName(): string
	{
		return $this->tableName;
	}

	public function forTable(
		string       $tableName,
		string|false $hiddenColumn = 'hidden',
		string|false $deletedColumn = 'deleted',
		string|false $languageColumn = 'sys_language_uid',
		string|false $localizationParentColumn = 'l10n_parent',
	): self
	{
		$this->tableName = $tableName;
		$this->hiddenColumn = $hiddenColumn;
		$this->deletedColumn = $deletedColumn;
		$this->languageColumn = $languageColumn;
		$this->localizationParentColumn = $localizationParentColumn;
		return $this;
	}
}
<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Repository;

use TYPO3\CMS\Core;

class GenericRepository extends AbstractRecordRepository
{
	public function getTableName(): string
	{
		return $this->tableName;
	}

	public function __construct(
		protected string $tableName,
		protected string $hiddenColumn = 'hidden',
		protected string $deletedColumn = 'deleted',
		protected string $languageColumn = 'sys_language_uid',
		protected string $localizationParentColumn = 'l10n_parent',
		protected ?Core\Database\ConnectionPool $connection = null,
		protected ?Core\Domain\RecordFactory $recordFactory = null,
	)
	{
		parent::__construct($connection, $recordFactory);
	}
}
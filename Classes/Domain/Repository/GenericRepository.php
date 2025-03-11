<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Repository;

use TYPO3\CMS\Core;

class GenericRepository extends AbstractRecordRepository
{
	public string $tableName = '';
	public function getTableName(): string
	{
		return $this->tableName;
	}
}
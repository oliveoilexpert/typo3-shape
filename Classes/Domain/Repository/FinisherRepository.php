<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Repository;

use TYPO3\CMS\Core;

class FinisherRepository extends AbstractRecordRepository
{
	public function getTableName(): string
	{
		return 'tx_shape_finisher';
	}

	public function findByContentParent(int $contentParentUid, bool $asRecord = true): array
	{
		$queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->getTableName());
		$where = [
			$queryBuilder->expr()->eq('hidden', 0),
			$queryBuilder->expr()->eq('deleted', 0),
			$queryBuilder->expr()->eq('content_parent', $contentParentUid),
		];
		$rows = $queryBuilder
			->select('*')->from($this->getTableName())
			->where(...$where)
			->executeQuery()->fetchAllAssociative();
		if ($asRecord) {
			return $this->toRecords($rows);
		}
		return $rows;
	}

}


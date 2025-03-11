<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Repository;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log;
use TYPO3\CMS\Core;

abstract class AbstractRecordRepository implements Log\LoggerAwareInterface
{
	use Log\LoggerAwareTrait;
	protected ?int $languageId = null;

	public function __construct(
		protected readonly EventDispatcherInterface $eventDispatcher,
		protected readonly Core\Database\ConnectionPool $connectionPool,
		protected readonly Core\Domain\RecordFactory $recordFactory,
	)
	{
	}

	abstract public function getTableName(): string;

	public function setLanguageId(int $languageId): void
	{
		$this->languageId = $languageId;
	}

	public function findByUid(int $uid, bool $asRecord = true): Core\Domain\Record|array|null
	{
		$queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->getTableName());
		$where = [
			$queryBuilder->expr()->eq('hidden', 0),
			$queryBuilder->expr()->eq('deleted', 0),
		];
		if ($this->languageId !== null) {
			$where[] = $queryBuilder->expr()->eq('sys_language_uid', $this->languageId);
			$where[] = $queryBuilder->expr()->logicalOr(
				$queryBuilder->expr()->eq('l10n_parent', $uid),
				$queryBuilder->expr()->eq('uid', $uid),
			);
		} else {
			$where[] = $queryBuilder->expr()->eq('uid', $uid);
		}
		$rows = $queryBuilder
			->select('*')->from($this->getTableName())
			->where(...$where)
			->executeQuery()->fetchAllAssociative();
		if ($asRecord) {
			return $this->toRecords($rows)[0];
		}
		return $rows[0] ?? null;
	}

	public function findAll(bool $asRecord): array
	{
		$queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->getTableName());
		$where = [
			$queryBuilder->expr()->eq('hidden', 0),
			$queryBuilder->expr()->eq('deleted', 0),
		];
		if ($this->languageId !== null) {
			$where[] = $queryBuilder->expr()->eq('sys_language_uid', $this->languageId);
		}
		$rows = $queryBuilder
			->select('*')->from($this->getTableName())
			->where(...$where)
			->executeQuery()->fetchAllAssociative();
		if ($asRecord) {
			return $this->toRecords($rows);
		}
		return $rows;
	}

	protected function toRecords(?array $rows, bool $resolved = true): array
	{
		$records = [];
		if (!$rows) {
			return $records;
		}
		foreach ($rows as $row) {
			if ($resolved) {
				$records[] = $this->recordFactory->createResolvedRecordFromDatabaseRow($this->getTableName(), $row);
			} else {
				$records[] = $this->recordFactory->createRecordFromDatabaseRow($this->getTableName(), $row);
			}
		}
		return $records;
	}
}
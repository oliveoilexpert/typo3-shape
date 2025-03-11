<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Repository;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

abstract class AbstractRecordRepository implements Log\LoggerAwareInterface
{
	use Log\LoggerAwareTrait;

	public function __construct(
		protected ?Core\Database\ConnectionPool $connection = null,
		protected ?Core\Domain\RecordFactory $recordFactory = null,
	)
	{
		$this->connection = $connection ?? Core\Utility\GeneralUtility::makeInstance(Core\Database\ConnectionPool::class);
		$this->recordFactory = $recordFactory ?? Core\Utility\GeneralUtility::makeInstance(Core\Domain\RecordFactory::class);
	}

	abstract public function getTableName(): string;

	protected ?int $languageId = null;
	protected string $hiddenColumn = 'hidden';
	protected string $deletedColumn = 'deleted';
	protected string $languageColumn = 'sys_language_uid';
	protected string $localizationParentColumn = 'l10n_parent';

	public function isLanguageAware(): bool
	{
		return $this->languageColumn && $this->languageId !== null;
	}

	public function setLanguageId(int $languageId): void
	{
		$this->languageId = $languageId;
	}

	protected function getQueryBuilder(): QueryBuilder
	{
		return $this->connection->getQueryBuilderForTable($this->getTableName());
	}

	public function create(array $data): int
	{
		$builder = $this->getQueryBuilder();
		$builder
			->insert($this->getTableName())
			->values($data)
			->executeStatement();
		return (int)$builder->getConnection()->lastInsertId();
	}

	public function findAll(bool $asRecord = false): array
	{
		return $this->findWhere([], $asRecord);
	}

	public function findBy(string $column, mixed $value, bool $asRecord = false): array
	{
		$builder = $this->getQueryBuilder();
		$where = [
			$builder->expr()->eq($column, $builder->createNamedParameter($value)),
		];
		return $this->findWhere($builder, $where, $asRecord);
	}

	public function findByUid(int $uid, bool $asRecord = false): Core\Domain\Record|array|null
	{
		$builder = $this->getQueryBuilder();
		$where = [];
		if ($this->isLanguageAware()) {
			$where[] = $builder->expr()->or(
				$builder->expr()->eq($this->localizationParentColumn, $uid),
				$builder->expr()->eq('uid', $uid),
			);
		} else {
			$where[] = $builder->expr()->eq('uid', $uid);
		}
		return $this->findWhere($builder, $where, $asRecord)[0] ?? null;
	}

	public function updateBy(string $column, mixed $value, array $data): void
	{
		$builder = $this->getQueryBuilder();
		$where = [
			$builder->expr()->eq($column, $builder->createNamedParameter($value))
		];
		$this->updateWhere($builder, $where, $data);
	}

	public function updateByUid(int $uid, array $data): void
	{
		$this->updateBy('uid', $uid, $data);
	}

	public function deleteBy(string $column, mixed $value): void
	{
		$builder = $this->getQueryBuilder();
		$where = [
			$builder->expr()->eq($column, $builder->createNamedParameter($value))
		];
		$this->deleteWhere($builder, $where);
	}

	public function deleteByUid(int $uid): void
	{
		$this->deleteBy('uid', $uid);
	}

	public function countBy(string $column, mixed $value): int
	{
		$builder = $this->getQueryBuilder();
		$where = [
			$builder->expr()->eq($column, $builder->createNamedParameter($value))
		];
		return $this->countWhere($builder, $where);
	}

	protected function findWhere(QueryBuilder $builder, array $where, bool $asRecord = false): array
	{
		if ($this->hiddenColumn) {
			$where[] = $builder->expr()->eq($this->hiddenColumn, 0);
		}
		if ($this->deletedColumn) {
			$where[] = $builder->expr()->eq($this->deletedColumn, 0);
		}
		if ($this->isLanguageAware()) {
			$where[] = $builder->expr()->eq($this->languageColumn, $this->languageId);
		}
		$rows = $builder
			->select('*')
			->from($this->getTableName())
			->where(...$where)
			->executeQuery()->fetchAllAssociative();
		if ($asRecord) {
			return $this->toRecords($rows);
		}
		return $rows;
	}

	protected function updateWhere(QueryBuilder $builder, array $where, array $data): void
	{
		$builder
			->update($this->getTableName())
			->where(...$where);
		foreach ($data as $column => $value) {
			$builder->set($column, $value);
		}
		$builder->executeStatement();
	}

	protected function deleteWhere(QueryBuilder $builder, array $where): void
	{
		$builder
			->delete($this->getTableName())
			->where(...$where)
			->executeStatement();
	}

	protected function countWhere(QueryBuilder $builder, array $where): int
	{
		return (int)$builder
			->count('uid')
			->from($this->getTableName())
			->where(...$where)
			->executeQuery()->fetchOne();
	}

	protected function toRecords(?array $rows, bool $resolved = true): array
	{
		$records = [];
		if (!$rows) {
			return $records;
		}
		foreach ($rows as $row) {
			if (!$row) {
				$records[] = $row;
			}
			if ($resolved) {
				$records[] = $this->recordFactory->createResolvedRecordFromDatabaseRow($this->getTableName(), $row);
			} else {
				$records[] = $this->recordFactory->createRecordFromDatabaseRow($this->getTableName(), $row);
			}
		}
		return $records;
	}
}
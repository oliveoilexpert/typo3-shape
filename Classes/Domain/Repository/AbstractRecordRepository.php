<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Repository;

use Psr\Log;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
abstract class AbstractRecordRepository implements Log\LoggerAwareInterface
{
	use Log\LoggerAwareTrait;

	protected ?Core\Database\ConnectionPool $connectionPool = null;
	protected ?Core\Domain\RecordFactory $recordFactory = null;
	public function injectConnectionPool(Core\Database\ConnectionPool $connectionPool): void
	{
		$this->connectionPool = $connectionPool;
	}
	public function injectRecordFactory(Core\Domain\RecordFactory $recordFactory): void
	{
		$this->recordFactory = $recordFactory;
	}

	abstract public function getTableName(): string;

	protected ?int $languageId = null;
	protected string|false $hiddenColumn = 'hidden';
	protected string|false $deletedColumn = 'deleted';
	protected string|false $languageColumn = 'sys_language_uid';
	protected string|false $localizationParentColumn = 'l10n_parent';

	public function isLanguageAware(): bool
	{
		return $this->languageColumn && $this->languageId !== null;
	}

	public function setLanguageId(int $languageId): void
	{
		$this->languageId = $languageId;
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

	public function findAll(
		bool $excludeHidden = true,
		bool $excludeDeleted = true,
		bool $currentLanguage = true,
		bool $asRecord = false,
	): array
	{
		$builder = $this->getQueryBuilder();
		return $this->findWhere($builder, [], $excludeHidden, $excludeDeleted, $currentLanguage, $asRecord);
	}

	public function findBy(
		string $column,
		mixed $value,
		bool $excludeHidden = true,
		bool $excludeDeleted = true,
		bool $currentLanguage = true,
		bool $asRecord = false
	): array
	{
		$builder = $this->getQueryBuilder();
		$where = [
			$builder->expr()->eq($column, $builder->createNamedParameter($value)),
		];
		return $this->findWhere($builder, $where, $excludeHidden, $excludeDeleted, $currentLanguage, $asRecord);
	}

	public function findByUid(
		int $uid,
		bool $excludeHidden = true,
		bool $excludeDeleted = true,
		bool $currentLanguage = true,
		bool $asRecord = false
	): Core\Domain\Record|array|null
	{
		$builder = $this->getQueryBuilder();
		$where = [];
		if ($currentLanguage && $this->isLanguageAware() && $this->localizationParentColumn) {
			$where[] = $builder->expr()->or(
				$builder->expr()->eq($this->localizationParentColumn, $uid),
				$builder->expr()->eq('uid', $uid),
			);
		} else {
			$where[] = $builder->expr()->eq('uid', $uid);
		}
		return $this->findWhere($builder, $where, $excludeHidden, $excludeDeleted, $currentLanguage, $asRecord)[0] ?? null;
	}

	public function countBy(
		string $column,
		mixed $value,
		bool $excludeHidden = true,
		bool $excludeDeleted = true,
		bool $currentLanguage = true,
	): int
	{
		$builder = $this->getQueryBuilder();
		$where = [
			$builder->expr()->eq($column, $builder->createNamedParameter($value))
		];
		return $this->countWhere($builder, $where, $excludeHidden, $excludeDeleted, $currentLanguage);
	}

	public function updateBy(
		string $column,
		mixed $value,
		array $data,
		bool $excludeDeleted = true,
	): void
	{
		$builder = $this->getQueryBuilder();
		$where = [
			$builder->expr()->eq($column, $builder->createNamedParameter($value))
		];
		$this->updateWhere($builder, $where, $data, $excludeDeleted);
	}

	public function updateByUid(
		int $uid,
		array $data,
		bool $excludeDeleted = true,
	): void
	{
		$this->updateBy('uid', $uid, $data, $excludeDeleted);
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

	protected function findWhere(
		QueryBuilder $builder,
		array $where,
		bool $excludeHidden = true,
		bool $excludeDeleted = true,
		bool $currentLanguage = true,
		bool $asRecord = false,
	): array
	{
		$where = $this->addDefaultClauses($builder, $where, $excludeHidden, $excludeDeleted, $currentLanguage);
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

	protected function countWhere(
		QueryBuilder $builder,
		array $where,
		bool $excludeHidden = true,
		bool $excludeDeleted = true,
		bool $currentLanguage = true,
	): int
	{
		$where = $this->addDefaultClauses($builder, $where, $excludeHidden, $excludeDeleted, $currentLanguage);
		return (int)$builder
			->count('uid')
			->from($this->getTableName())
			->where(...$where)
			->executeQuery()->fetchOne();
	}

	protected function updateWhere(
		QueryBuilder $builder,
		array $where,
		array $data,
		bool $excludeDeleted = true,
	): void
	{
		$where = $this->addDefaultClauses($builder, $where, false, $excludeDeleted, false);
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

	protected function getQueryBuilder(): QueryBuilder
	{
		return $this->connectionPool->getQueryBuilderForTable($this->getTableName());
	}

	protected function addDefaultClauses(
		QueryBuilder $builder,
		array $where,
		bool $excludeHidden = true,
		bool $excludeDeleted = true,
		bool $currentLanguage = true,
	): array
	{
		if ($excludeHidden && $this->hiddenColumn) {
			$where[] = $builder->expr()->eq($this->hiddenColumn, 0);
		}
		if ($excludeDeleted && $this->deletedColumn) {
			$where[] = $builder->expr()->eq($this->deletedColumn, 0);
		}
		if ($currentLanguage && $this->isLanguageAware()) {
			$where[] = $builder->expr()->eq($this->languageColumn, $this->languageId);
		}
		return $where;
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
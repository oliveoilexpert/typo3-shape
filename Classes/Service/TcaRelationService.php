<?php

namespace UBOS\Shape\Service;

use TYPO3\CMS\Core;

class TcaRelationService
{
	public function __construct(
		protected Core\Database\ConnectionPool $connectionPool
	)
	{
	}

	public function mirrorCSVRelation(
		string $originTable,
		string $originColumn,
		string $targetTable,
		string $targetColumn,
		bool $emptyTargetColumn = true): void
	{
		$originQuery = $this->connectionPool->getQueryBuilderForTable($originTable);
		$targetQuery = $this->connectionPool->getQueryBuilderForTable($targetTable);

		// if destructive, empty "fields" column in tx_shape_form_page
		if ($emptyTargetColumn) {
			$targetQuery
				->update($targetTable)
				->set($targetColumn, '')
				->executeQuery();
		}
		// find all pages
		$targetRows = $targetQuery
			->select('uid', $targetColumn)
			->from($targetTable)
			->executeQuery()
			->fetchAllAssociative();
		// for each page, find all fields that have page uid in page_parents and add that field uid to the page fields
		foreach ($targetRows as $row) {
			$targetUid = $row['uid'];
			$oppositeRows = $originQuery
				->select('uid')
				->from($originTable)
				->where(
					$originQuery->expr()->inSet($originColumn, (string)$targetUid)
				)
				->executeQuery()
				->fetchAllAssociative();
			if (empty($oppositeRows)) {
				continue;
			}
			$oppositeUids = array_map(function ($oRow) {
				return $oRow['uid'];
			}, $oppositeRows);
			$oppositeUids = implode(',', $oppositeUids);
			$targetQuery
				->update($targetTable)
				->set($targetColumn, $oppositeUids)
				->where(
					$targetQuery->expr()->eq('uid', $targetUid)
				)
				->executeQuery();
		}
	}

	public function mirrorCurrentPageFieldRelations(): void
	{
		$fieldsType = $GLOBALS['TCA']['tx_shape_form_page']['columns']['fields']['config']['type'];
		if (in_array($fieldsType, ['select', 'group'])) {
			$this->mirrorCSVRelation(
				'tx_shape_form_page',
				'fields',
				'tx_shape_field',
				'page_parents'
			);
		} elseif ($fieldsType === 'inline') {
			$this->mirrorCSVRelation(
				'tx_shape_field',
				'page_parents',
				'tx_shape_form_page',
				'fields'
			);
		}
	}

	public function mirrorCurrentFormFinisherRelations(): void
	{
		$finishersType = $GLOBALS['TCA']['tx_shape_form']['columns']['finishers']['config']['type'];
		if (in_array($finishersType, ['select', 'group'])) {
			$this->mirrorCSVRelation(
				'tx_shape_form',
				'finishers',
				'tx_shape_finisher',
				'form_parents'
			);
		} elseif ($finishersType === 'inline') {
			$this->mirrorCSVRelation(
				'tx_shape_finisher',
				'form_parents',
				'tx_shape_form',
				'finishers'
			);
		}
	}
}
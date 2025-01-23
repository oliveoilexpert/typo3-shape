<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Repository;

use TYPO3\CMS\Core;

class FormRepository extends AbstractRecordRepository
{
	public function getTableName(): string
	{
		return 'tx_shape_form';
	}
}
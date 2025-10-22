<?php

declare(strict_types=1);

namespace UBOS\Shape\Repository;

class EmailConsentRepository extends AbstractRecordRepository
{
	public function getTableName(): string
	{
		return 'tx_shape_email_consent';
	}
}
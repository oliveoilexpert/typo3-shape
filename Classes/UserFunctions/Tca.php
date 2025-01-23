<?php

namespace UBOS\Shape\UserFunctions;

use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSlug;

class Tca
{

	public function getFormattedFieldLabel(array &$params): void
	{
		$row = $params['row'];
		$params['title'] = $row['label'] . ' <small>[' . ($row['type'][0] ?? $row['type']) . ']</small>';
	}

	public function getFieldIdentifierPrefix(array $parameters, TcaSlug $reference): string
	{
		return "";
	}

}

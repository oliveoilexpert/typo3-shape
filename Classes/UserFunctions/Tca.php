<?php

namespace UBOS\Shape\UserFunctions;

use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSlug;
use TYPO3\CMS\Core\Utility\DebugUtility;

class Tca
{

	public function getFormattedFieldLabel(array &$params): void
	{
		if (!isset($params['row'])) {
			return;
		}
		$row = $params['row'];
		$label = $row['label'];
		if (is_array($label)) {
			$label = $label[0] ?? '';
		}
		$type = $row['type'];
		if (is_array($type)) {
			$type = $type[0] ?? '';
		}
		$params['title'] = $label . ' <small>[' . $type . ']</small>';
	}

	public function getFieldIdentifierPrefix(array $parameters, TcaSlug $reference): string
	{
		return "";
	}

}

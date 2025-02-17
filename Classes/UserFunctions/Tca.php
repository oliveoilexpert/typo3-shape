<?php

namespace UBOS\Shape\UserFunctions;

use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSlug;
use TYPO3\CMS\Core\Utility\DebugUtility;

class Tca
{

	/**
	 * Provide label for inline field records
	 */
	public function getFormattedFieldLabel(array &$params): void
	{
		if (!isset($params['row'])) {
			return;
		}
		$row = $params['row'];
		$label = $row['label'];
		if (!$row['label'] && !$row['type']) {
			return;
		}
		if (is_array($label)) {
			$label = $label[0] ?? '';
		}
		$type = $row['type'];
		if (is_array($type)) {
			$type = $type[0] ?? '';
		}
		$params['title'] = "{$label}<small>[{$type}]</small>";
	}

	/**
	 * Provide empty slug prefix
	 */
	public function getEmptySlugPrefix(array $parameters, TcaSlug $reference): string
	{
		return "";
	}

}

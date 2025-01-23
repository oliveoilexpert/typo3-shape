<?php

namespace UBOS\Shape\Utility;

class TcaUtility
{
	public static function selectItemHelper(array $item): array
	{
		if (self::getVersion() < 12) {
			return [
				$item[0] ?? $item['label'],
				$item[1] ?? $item['value'],
				$item[2] ?? $item['icon'] ??  '',
				$item[3] ?? $item['group'] ?? '',
			];
		}
		return [
			'label' => $item[0] ?? $item['label'],
			'value' => $item[1] ?? $item['value'],
			'icon' => $item[2] ?? $item['icon'] ??  '',
			'group' => $item[3] ?? $item['group'] ?? '',
		];
	}

	public static function selectItemsHelper(array $items): array
	{
		return array_map(function($item) {
			return self::selectItemHelper($item);
		}, $items);
	}
}
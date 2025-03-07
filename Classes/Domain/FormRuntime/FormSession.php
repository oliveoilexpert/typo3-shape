<?php

namespace UBOS\Shape\Domain\FormRuntime;

use TYPO3\CMS\Core;

class FormSession
{
	public function __construct(
		protected string $id = '',
		public array $values = [],
		public int $returnPageIndex = 1,
	)
	{}

	const SECRET = '__session';

	public function getId(): string
	{
		$this->id = $this->id ?: Core\Utility\GeneralUtility::makeInstance(Core\Crypto\Random::class)->generateRandomHexString(40);
		return $this->id;
	}

	public static function serialize(FormSession $session): string
	{
		$hashService = Core\Utility\GeneralUtility::makeInstance(Core\Crypto\HashService::class);
		return $hashService->appendHmac(
			base64_encode(serialize($session)),
			FormSession::SECRET
		);
	}
	public static function validateAndUnserialize(string $serializedSessionWithHmac): bool|FormSession
	{
		$hashService = Core\Utility\GeneralUtility::makeInstance(Core\Crypto\HashService::class);
		try {
			$serializedSession = $hashService->validateAndStripHmac(
				$serializedSessionWithHmac,
				FormSession::SECRET
			);
		} catch (\Exception $e) {
			throw $e;
		}
		return unserialize(base64_decode($serializedSession));
	}
}
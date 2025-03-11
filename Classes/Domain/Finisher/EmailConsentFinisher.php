<?php

namespace UBOS\Shape\Domain\Finisher;

use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;
use UBOS\Shape\Domain;
use UBOS\Shape\Utility\TemplateVariableParser;

class EmailConsentFinisher extends AbstractFinisher
{
	protected string $tableName = 'tx_shape_email_consent';
	protected array $settings = [
		'subject' => '',
		'body' => '',
		'template' => 'Finisher/EmailConsent',
		'consentPage' => '',
		'senderAddress' => '',
		'senderName' => '',
		'recipientAddress' => '',
		'validSeconds' => 86400,
		'storagePage' => 0,
	];

	public function execute(): void
	{
		$recipient = $this->parseWithValues($this->settings['recipientAddress']);
		if (!$recipient || !$this->settings['subject'] || !$this->settings['consentPage']) {
			return;
		}
		$consentPage = $this->settings['consentPage'] ?? $this->getPluginSettings()['consentPage'];
		$storagePage = (int)($this->settings['storagePage'] ?: $this->getPlugin()->getPid() ?? $this->getForm()->getPid());
		$formValues = $this->getFormValues();
		$timestamp = time();
		$serializedSession = Domain\FormRuntime\FormSession::serialize($this->getRuntime()->session);
		$consentData = [
			'crdate' => $timestamp,
			'tstamp' => $timestamp,
			'pid' => $storagePage,
			'state' => 'pending',
			'session' => $serializedSession,
			'form' => $this->getForm()->getUid(),
			'plugin' => $this->getPlugin()->getUid(),
			'email' => $recipient,
			'valid_until' => $timestamp + $this->settings['validSeconds'],
		];

		$hashService = GeneralUtility::makeInstance(Core\Crypto\HashService::class);
		$consentData['validation_hash'] = $hashService->hmac(
			$consentData['session'] . '_' . $consentData['crdate'],
			$consentData['email']
		);

		$consentRepository = new Domain\Repository\EmailConsentRepository();
		$consentUid = $consentRepository->create($consentData);

		$email = new Core\Mail\FluidEmail($this->getView()->getRenderingContext()->getTemplatePaths());
		$senderAddress = new Address(
			$this->settings['senderAddress'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'],
			$this->settings['senderName'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']
		);
		$subject = $this->parseWithValues($this->settings['subject']);
		$template = $this->settings['template'];
		$format = Core\Mail\FluidEmail::FORMAT_BOTH;

		$uriBuilder = GeneralUtility::makeInstance(Extbase\Mvc\Web\Routing\UriBuilder::class);
		$approveLink = $uriBuilder
			->setTargetPageUid($consentPage)
			->setRequest($this->getRequest())
			->setCreateAbsoluteUri(true)
			->uriFor(
				'approve',
				['uid' => $consentUid, 'hash' => $consentData['validation_hash']],
				'Consent',
				'shape',
				'Consent'
			);
		$variables = [
			'formValues' => $formValues,
			'settings' => $this->settings,
			'runtime' => $this->getRuntime(),
			'approveLink' => $approveLink,
			'parsed' => [
				'body' => $this->parseWithValues($this->settings['body'])
			]
		];
		$email
			->from($senderAddress)
			->to($recipient)
			->subject($subject)
			->setRequest($this->getRequest())
			->format($format)
			->setTemplate($template)
			->assignMultiple($variables);

		GeneralUtility::makeInstance(Core\Mail\MailerInterface::class)->send($email);
	}

	protected function parseWithValues(string $string): string
	{
		return TemplateVariableParser::parse($string, $this->getFormValues());
	}
}
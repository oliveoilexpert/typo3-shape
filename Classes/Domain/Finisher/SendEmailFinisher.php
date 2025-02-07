<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core;
use TYPO3\CMS\Fluid;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;

class SendEmailFinisher extends AbstractFinisher
{
	public string $templateName = 'SendEmail';
	public string $subjectFallback = 'TYPO3 send email via shape form';
	public string $mailFormat = Core\Mail\FluidEmail::FORMAT_BOTH;

	public function execute(): ?ResponseInterface
	{
		$this->settings = array_merge([
			'mailSubject' => '',
			'mailBody' => '',
			'mailTemplate' => '',
			'senderAddress' => '',
			'senderName' => '',
			'recipientAddress' => '',
			'recipientAddressField' => '',
		], $this->settings);

		$email = new Core\Mail\FluidEmail();
		$email
			->from($this->resolveSenderAddress())
			->to(...$this->resolveRecipientAddresses())
			->subject($this->settings['mailSubject'] ?: $this->subjectFallback)
			->setRequest($this->request)
			->format($this->mailFormat)
			->setTemplate($this->settings['mailTemplate'] ?: $this->templateName)
			->assignMultiple([
				'formValues' => $this->formValues,
				'form' => $this->form,
				'settings' => $this->settings,
				'interpolatedMailBody' => $this->interpolateStringWithFormValues($this->settings['mailBody']),
			]);
		GeneralUtility::makeInstance(Core\Mail\MailerInterface::class)->send($email);
		return null;
	}

	protected function resolveSenderAddress(): Address
	{
		return new Address(
			$this->settings['senderAddress'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'],
			$this->settings['senderName'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']
		);
	}

	protected function resolveRecipientAddresses(): array
	{
		$addresses = [];
		foreach (GeneralUtility::trimExplode(',', $this->settings['recipientAddress'], true) as $address) {
			$addresses[] = $address;
		}
		if ($this->settings['recipientAddressField'] && isset($this->formValues[$this->settings['recipientAddressField']])) {
			$addresses[] = $this->formValues[$this->settings['recipientAddressField']];
		}
		return $addresses;
	}

	protected function interpolateStringWithFormValues(string $string): string
	{
		foreach ($this->formValues as $key => $value) {
			if (is_array($value)) {
				$value = implode(', ', $value);
			}
			$string = str_replace('{' . $key . '}', $value ?? '', $string);
		}
		return $string;
	}
}
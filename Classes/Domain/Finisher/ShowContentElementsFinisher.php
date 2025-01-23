<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;

class ShowContentElementsFinisher extends AbstractFinisher
{
	public function execute(): ?ResponseInterface
	{
		$this->view->assign('contentElementUids', explode(',', $this->settings['contentElements']));
		$html = $this->view->render('Finisher/ShowContentElements');
		$response = new Core\Http\Response(null, 200, [], '');
		$stream = new Core\Http\Stream('php://temp', 'r+');
		$stream->write($html);
		return $response->withHeader('Content-Type', 'text/html; charset=utf-8')->withBody($stream);
	}
}
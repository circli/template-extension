<?php

namespace Circli\Extensions\Template;

use Aura\Payload_Interface\PayloadInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PathTemplateResponder extends TemplateResponder
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload
    ): ResponseInterface {
        $path = ltrim($request->getUri()->getPath(), '/');
        $this->templateFile = $path;
        return parent::__invoke($request, $response, $payload);
    }
}

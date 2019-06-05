<?php

namespace Circli\Extensions\Template\Responder;

use Aura\Payload_Interface\PayloadInterface;
use Aura\Payload_Interface\PayloadStatus;
use Blueprint\TemplateInterface;
use Circli\Core\PayloadStatusToHttpStatus;
use Circli\Extensions\Template\Events\PostRenderEvent;
use Circli\Extensions\Template\Events\PreRenderEvent;
use Polus\Adr\Interfaces\ResponderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class TemplateResponder implements ResponderInterface
{
    protected $payloadStatus;
    /** @var TemplateInterface */
    protected $templateEngine;
    protected $attributeMap;

    protected $templateFile;

    /** @var ResponseFactoryInterface */
    protected $responseFactory;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        PayloadStatusToHttpStatus $payloadStatus,
        TemplateInterface $engine,
        ResponseFactoryInterface $responseFactory,
        EventDispatcherInterface $eventManager
    ) {
        $this->payloadStatus = $payloadStatus;
        $this->templateEngine = $engine;
        $this->responseFactory = $responseFactory;
        $this->eventDispatcher = $eventManager;
    }

    protected function _render(string $templateFile, array $data): string
    {
        return $this->templateEngine->render($templateFile);
    }

    public function render(string $templateFile, array $data, ServerRequestInterface $request): ResponseInterface
    {
        $this->templateEngine->assign($data);
        $status = 200;

        $this->eventDispatcher->dispatch(new PreRenderEvent($this->templateEngine, $templateFile, $status, $request));
        $html = $this->_render($templateFile, $data);
        $response = $this->responseFactory->createResponse($status);
        $response = $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        $response->getBody()->write($html);

        $this->eventDispatcher->dispatch(new PostRenderEvent($html, $status, $response, $request));

        return $response;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload
    ): ResponseInterface {
        if ($this->templateFile === null) {
            throw new \DomainException('Must specify a template');
        }
        if ($payload->getStatus() === PayloadStatus::NOT_FOUND) {
            return $this->responseFactory->createResponse(404);
        }

        return $this->render($this->templateFile, $payload->getOutput() ?? [], $request);
    }
}

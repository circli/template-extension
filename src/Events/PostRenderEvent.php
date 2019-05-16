<?php

namespace Circli\Extensions\Template\Events;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostRenderEvent
{
    /** @var string */
    protected $html;
    /** @var int */
    protected $status;
    /** @var ResponseInterface */
    protected $response;
    /** @var ServerRequestInterface */
    protected $request;

    public function __construct(string $html, int $status, ResponseInterface $response, ServerRequestInterface $request)
    {
        $this->html = $html;
        $this->status = $status;
        $this->response = $response;
        $this->request = $request;
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
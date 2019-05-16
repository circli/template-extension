<?php

namespace Circli\Extensions\Template\Events;

use Blueprint\TemplateInterface;
use Psr\Http\Message\ServerRequestInterface;

class PreRenderEvent
{
    /** @var TemplateInterface */
    protected $template;
    /** @var string */
    protected $file;
    /** @var int */
    protected $status;
    /** @var ServerRequestInterface */
    protected $request;

    public function __construct(TemplateInterface $template, string $file, int $status, ServerRequestInterface $request)
    {
        $this->template = $template;
        $this->file = $file;
        $this->status = $status;
        $this->request = $request;
    }

    public function getTemplate(): TemplateInterface
    {
        return $this->template;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
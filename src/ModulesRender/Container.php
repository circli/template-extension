<?php declare(strict_types=1);

namespace Circli\Extensions\Template\ModulesRender;

use Blueprint\TemplateInterface;

class Container
{
    /** @var Renderer[] */
    private $renderer = [];
    /** @var TemplateInterface */
    private $template;
    /** @var array */
    private $data;

    public function __construct(TemplateInterface $template, array $data = [])
    {
        $this->template = $template;
        $this->data = $data;
    }

    public function addRenderer(string $module, Renderer $renderer): void
    {
        $this->renderer[$module] = $renderer;
    }

    public function haveModule(string $module): bool
    {
        return isset($this->renderer[$module]);
    }

    public function get(string $module): Renderer
    {
        if (isset($this->renderer[$module])) {
            $this->renderer[$module]->setData($this->data);
            $this->renderer[$module]->setTemplate($this->template);
            return $this->renderer[$module];
        }

        throw new RendererNotFound('Renderer not found for module: ' . $module);
    }

    public function render()
    {
        $return = [];
        foreach ($this->renderer as $renderer) {
            $renderer->setTemplate($this->template);
            $renderer->setData($this->data);
            $return[] = $renderer->render();
        }

        return implode("\n", $return);
    }
}
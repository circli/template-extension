<?php declare(strict_types=1);

namespace Circli\Extensions\Template\ModulesRender;

class Renderer
{
    /** @var string */
    private $tpl;
    /** @var array */
    private $data = [];
    /** @var \Blueprint\TemplateInterface */
    private $template;

    public function __construct(string $tpl)
    {
        $this->tpl = $tpl;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function setTemplate(\Blueprint\TemplateInterface $template): void
    {
        $this->template = $template;
    }

    public function render(): string
    {
        if ($this->template) {
            if ($this->data) {
                $this->template->assign($this->data);
            }
            return $this->template->render($this->tpl);
        }
        return '';
    }
}

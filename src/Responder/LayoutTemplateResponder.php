<?php declare(strict_types=1);

namespace Circli\Extensions\Template\Responder;

use Blueprint\Layout;
use Circli\Core\PayloadStatusToHttpStatus;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class LayoutTemplateResponder extends TemplateResponder
{
    /** @var Layout */
    private $layoutEngine;

    protected $layout;

    public function __construct(
        PayloadStatusToHttpStatus $payloadStatus,
        Layout $layout,
        ResponseFactoryInterface $responseFactory,
        EventDispatcherInterface $eventManager
    ) {
        parent::__construct($payloadStatus, $layout->getContent(), $responseFactory, $eventManager);
        $this->layoutEngine = $layout;
    }

    protected function _render(string $templateFile, array $data): string
    {
        $content = $this->layoutEngine->getContent();
        if (method_exists($content, 'setTemplate')) {
            $content->setTemplate($templateFile);
        }

        if ($this->layout || isset($data['layout'])) {
            $this->layoutEngine->setTemplate('layout/' . ($data['layout'] ?? $this->layout) . '.php');
        }

        return $this->layoutEngine->render();
    }
}

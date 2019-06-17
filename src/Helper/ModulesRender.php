<?php declare(strict_types=1);

namespace Circli\Extensions\Template\Helper;

use Blueprint\Exception\TemplateNotFoundException;
use Blueprint\Helper\AbstractHelper;
use Circli\Contracts\PathContainer;
use Circli\Extensions\Template\ModulesRender\Container;
use Circli\Extensions\Template\ModulesRender\Renderer;
use Circli\Extensions\Template\TemplateFinder;

class ModulesRender extends AbstractHelper
{
    /** @var PathContainer */
    private $pathContainer;
    /** @var TemplateFinder */
    private $templateFinder;

    public function __construct(PathContainer $pathContainer, TemplateFinder $templateFinder)
    {
        $this->pathContainer = $pathContainer;
        $this->templateFinder = $templateFinder;
    }

    public function getName(): string
    {
        return 'modulesRender';
    }

    public function run(array $args)
    {
        $container = new Container($this->getTemplate(), $args[1] ?? []);
        $tpl = $args[0];

        $config = $this->pathContainer->loadConfigFile('provides');
        if (!isset($config['templates']['ns'])) {
            return $container;
        }

        foreach ($config['templates']['ns'] as $ns) {
            try {
                $haveTpl = $this->templateFinder->findTemplate($ns . ':' . $tpl);
                if (!$haveTpl) {
                    continue;
                }
                $container->addRenderer($ns, new Renderer($ns . ':' . $tpl));
            }
            catch (TemplateNotFoundException $e) {
            }
        }
        return $container;
    }
}
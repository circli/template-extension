<?php

namespace Circli\Extensions\Template;

use Blueprint\ActusFinder;
use Blueprint\Helper\ResolverInterface;
use Blueprint\Layout;
use Circli\Contracts\ExtensionInterface;
use Circli\Contracts\PathContainer;
use function DI\autowire;
use function DI\create;
use function DI\factory;
use function DI\get;
use Blueprint\Assets\Finder;
use Blueprint\Assets\JsonManifest;
use Blueprint\DefaultFinder;
use Blueprint\Extended;
use Blueprint\FinderInterface;
use Blueprint\Helper\Resolver;
use Blueprint\Helper\ResolverList;
use Blueprint\TemplateInterface;
use Psr\Container\ContainerInterface;
use Actus\Path;

class Extension implements ExtensionInterface
{
    /**
     * @var PathContainer
     */
    protected $paths;

    public function __construct(PathContainer $paths)
    {
        $this->paths = $paths;
    }

    public function configure(): array
    {
        $config = $this->paths->loadConfigFile('template');

        return [
            //Template finder
            FinderInterface::class => factory(function (ContainerInterface $container, $config) {
                    $templatePaths = new Path();
                    foreach ($config['actur_templates'] as $ns => $path) {
                        $templatePaths->set($ns, $path);
                    }

                    return new TemplateFinder($templatePaths, $config['template_paths']);
            })->parameter('config', $config),
            JsonManifest::class => create(JsonManifest::class)->constructor($config['asset_path'] . '/assets.json'),
            Path::class => factory(function (ContainerInterface $container, $config) {
                $actus = new Path();
                $actus->setRoot($this->paths->getBasePath());
                $actus->set('svg', $config['asset_path'] . '/svg/');
                $actus->set('images', $config['asset_path'] . '/images/');
                $actus->set('style', $config['asset_path'] . '/styles/');
                $actus->set('script', $config['asset_path'] . '/scripts/');
                return $actus;
            })->parameter('config', $config),
            Finder::class => create(Finder::class)->constructor(
                get(JsonManifest::class),
                get(Path::class)
            ),
            ResolverInterface::class => factory(function (ContainerInterface $container, $config) {
                $resolver = new Resolver(static function ($cls) use ($container) {
                    return $container->get($cls);
                });
                $resolver->addNs('Blueprint\DesignHelper');

                if (isset($config['template']['resolvers']) && \is_array($config['template']['resolvers'])) {
                    foreach ($config['template']['resolvers'] as $resolverNs) {
                        $resolver->addNs($resolverNs);
                    }
                }

                return $resolver;
            })->parameter('config', $config),
            Layout::class => static function(ContainerInterface $container) {
                $resolver = $container->get(ResolverInterface::class);
                $content = $container->get(TemplateInterface::class);
                $layout = new Layout(
                    $container->get(FinderInterface::class),
                    new ResolverList([$resolver], true)
                );
                $layout->setContent($content);
                $layout->setTemplate('layout/default.php');
                return $layout;
            },
            TemplateInterface::class => static function(ContainerInterface $container) {
                $resolver = $container->get(ResolverInterface::class);
                $template = new Extended(
                    $container->get(FinderInterface::class),
                    new ResolverList([$resolver], true)
                );
                $template->addResolver($resolver);

                return $template;
            }
        ];
    }
}
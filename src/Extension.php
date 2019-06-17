<?php

namespace Circli\Extensions\Template;

use Blueprint\Helper\ResolverInterface;
use Blueprint\Layout;
use Circli\Contracts\ExtensionInterface;
use Circli\Contracts\PathContainer;
use function DI\create;
use function DI\factory;
use function DI\get;
use Blueprint\Assets\Finder as AssetFinder;
use Blueprint\Assets\FinderInterface as AssetFinderInterface;
use Blueprint\Assets\JsonManifest;
use Blueprint\Extended;
use Blueprint\FinderInterface;
use Blueprint\Helper\Resolver;
use Blueprint\Helper\ResolverList;
use Blueprint\TemplateInterface;
use Psr\Container\ContainerInterface;
use Actus\Path;

final class Extension implements ExtensionInterface
{
    /** @var PathContainer */
    protected $paths;

    public function __construct(PathContainer $paths)
    {
        $this->paths = $paths;
    }

    public function configure(): array
    {
        $config = $this->paths->loadConfigFile('template');
        $assets = $this->paths->loadConfigFile('assets');

        return [
            //Template finder
            TemplateFinder::class => factory(function (ContainerInterface $container, $config) {
                $templatePaths = new Path();
                foreach ($config['actus_templates'] as $ns => $path) {
                    $templatePaths->set($ns, $path);
                }

                return new TemplateFinder($templatePaths, $config['template_paths']);
            })->parameter('config', $config),
            FinderInterface::class => get(TemplateFinder::class),
            JsonManifest::class => create(JsonManifest::class)->constructor($config['asset_path'] . '/assets.json'),
            Path::class => factory(static function (ContainerInterface $container, $config, $assets, $basePath) {
                $actus = new Path();
                $actus->setRoot($basePath . '/public');
                $actus->set('svg', $config['asset_path'] . '/svg/');
                $actus->set('images', $config['asset_path'] . '/images/');
                $actus->set('style', $config['asset_path'] . '/styles/');
                $actus->set('script', $config['asset_path'] . '/scripts/');

                if ($assets && is_array($assets) && count($assets)) {
                    foreach ($assets as $name => $types) {
                        if (in_array('svg', $types, true)) {
                            $actus->set('svg', $config['asset_path'] . '/svg/' . $name . '/', Path::MOD_APPEND);
                        }
                        if (in_array('images', $types, true)) {
                            $actus->set('images', $config['asset_path'] . '/images/' . $name . '/', Path::MOD_APPEND);
                        }
                    }
                }

                return $actus;
            })
                ->parameter('config', $config)
                ->parameter('assets', $assets)
                ->parameter('basePath', $this->paths->getBasePath()),
            AssetFinderInterface::class => create(AssetFinder::class)->constructor(
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
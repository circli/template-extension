<?php

namespace Circli\Extensions\Template;

use Circli\Contracts\ExtensionInterface;
use Circli\Contracts\PathContainer;
use function DI\create;
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
            FinderInterface::class => create(DefaultFinder::class)->constructor($config['template_paths']),
            JsonManifest::class => create(JsonManifest::class)->constructor($config['asset_path'] . '/assets.json'),
            Path::class => function() use($config) {
                $actus = new Path();
                $actus->setRoot($this->paths->getBasePath());
                $actus->set('svg', $config['asset_path'] . '/svg/');
                $actus->set('images', $config['asset_path'] . '/images/');
                $actus->set('style', $config['asset_path'] . '/styles/');
                $actus->set('script', $config['asset_path'] . '/scripts/');
                return $actus;
            },
            Finder::class => create(Finder::class)->constructor(
                get(JsonManifest::class),
                get(Path::class)
            ),
            TemplateInterface::class => function(ContainerInterface $container) {
                $resolver = new Resolver(function ($cls) use ($container) {
                    return $container->get($cls);
                });
                $resolver->addNs('Blueprint\DesignHelper');
                $resolver->addNs('Artemis\TemplateHelper');

                $template = new Extended(
                    $container->get(Finder::class),
                    new ResolverList([$resolver], true)
                );
                $template->addResolver($resolver);

                return $template;
            }
        ];
    }
}
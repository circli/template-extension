<?php declare(strict_types=1);

namespace Circli\Extensions\Template;

use Actus\Path;
use Blueprint\DefaultFinder;
use Blueprint\Exception\TemplateNotFoundException;

final class TemplateFinder extends DefaultFinder
{
    private $actus;
    private $cache = [];

    public function __construct(Path $actus, array $paths = [])
    {
        parent::__construct($paths);
        $this->actus = $actus;
    }

    public function getPathResolver(): Path
    {
        return $this->actus;
    }

    /**
     * @param string $file
     * @param null|string $type
     * @return string
     * @throws TemplateNotFoundException
     */
    public function findTemplate(string $file, ?string $type = null): string
    {
        $cacheKey = $file . $type;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        if (strpos($file, ':')) {
            try {
                return $this->cache[$cacheKey] = parent::findTemplate(str_replace(':', '/', $file), $type);
            }
            catch (TemplateNotFoundException $e) {
            }

            try {
                $cleanFile = $this->cleanFilename($file, $type);
                $tpl = $this->actus->get($cleanFile);
                if ($tpl) {
                    return $this->cache[$cacheKey] = $tpl;
                }
            }
            catch (\Exception $e) {
            }
            throw new TemplateNotFoundException($file . ':' . $type);
        }
        return $this->cache[$cacheKey] = parent::findTemplate($file, $type);
    }
}
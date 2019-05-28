<?php declare(strict_types=1);

namespace Circli\Extensions\Template;

use Actus\Path;
use Blueprint\DefaultFinder;
use Blueprint\Exception\TemplateNotFoundException;

final class TemplateFinder extends DefaultFinder
{
    private $actus;

    public function __construct(Path $actus, array $paths = [])
    {
        parent::__construct($paths);
        $this->actus = $actus;
    }

    /**
     * @param string $file
     * @param null|string $type
     * @return string
     * @throws TemplateNotFoundException
     */
    public function findTemplate(string $file, ?string $type = null): string
    {
        //todo cache result

        if (strpos($file, ':')) {
            try {
                return parent::findTemplate(str_replace(':', '/', $file), $type);
            }
            catch (TemplateNotFoundException $e) {
            }

            try {
                $cleanFile = $this->cleanFilename($file, $type);
                $tpl = $this->actus->get($cleanFile);
                if ($tpl) {
                    return $tpl;
                }
            }
            catch (\Exception $e) {
            }
            throw new TemplateNotFoundException($file . ':' . $type);
        }
        return parent::findTemplate($file, $type);
    }
}
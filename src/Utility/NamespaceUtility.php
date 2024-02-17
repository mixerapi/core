<?php
declare(strict_types=1);

namespace MixerApi\Core\Utility;

use Cake\Collection\Collection;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Kcs\ClassFinder\Finder\ComposerFinder;
use RuntimeException;

class NamespaceUtility
{
    /**
     * Finds classes using the $namespace argument and returns an array of namespaces as strings.
     *
     * @param string|null $namespace A namespace such as `App\Controller`, if null, the `App.namespace` config is used.
     * @param array<string> $paths A list of absolute paths to load classes from
     * @return array<string>
     */
    public static function findClasses(?string $namespace = null, array $paths = []): array
    {
        $namespace = $namespace ?? Configure::read('App.namespace');
        if (str_starts_with($namespace, '\\')) {
            $namespace = substr($namespace, 1, strlen($namespace));
        }
        if (str_ends_with($namespace, '\\')) {
            $namespace = substr($namespace, 0, strlen($namespace) - 1);
        }

        if (empty($paths)) {
            $paths = [APP];

            /** @var \Cake\Core\BasePlugin $plugin */
            foreach (Plugin::getCollection() as $plugin) {
                if (str_contains('/vendor/', $plugin->getClassPath())) {
                    continue;
                }
                $paths[] = $plugin->getClassPath();
            }
        }

        $finder = (new ComposerFinder())
            ->inNamespace($namespace)
            ->in($paths);
        $classes = [];
        foreach ($finder as $className => $reflector) {
            $classes[] = $className;
        }

        return array_map(function (string $namespace) {
            if (!str_starts_with($namespace, '\\')) {
                return '\\' . $namespace;
            }

            return $namespace;
        }, $classes);
    }

    /**
     * Performs a non-recursive search for the classes shortname in the given namespace
     *
     * @param string $namespace The namespace to search in
     * @param string $shortName The short name of the class
     * @return string
     * @throws \RuntimeException
     */
    public static function findClass(string $namespace, string $shortName): string
    {
        $classes = NamespaceUtility::findClasses($namespace);

        $results = (new Collection($classes))->filter(function ($class) use ($shortName) {
            $pieces = explode('\\', $class);

            return end($pieces) == $shortName;
        });

        if (!$results->count()) {
            throw new RuntimeException("Class not found for `$shortName` in `$namespace`");
        }

        return $results->first();
    }
}

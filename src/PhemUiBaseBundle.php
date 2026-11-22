<?php

declare(strict_types=1);

namespace Phem\Ui\Base;

use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Adds the assets of the package to AssetMapper, as `@phem/ui-base`, and turns on the Tailwind
 * merger of phem, so that a `className` of the caller replaces a class of the same group.
 *
 * The components need no bundle. The app adds the sources of the package to its Tailwind
 * build with `@source "../../vendor/phem/ui-base/src";`.
 */
class PhemUiBaseBundle extends AbstractBundle
{
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $this->prependClassMerger($builder);
        $this->prependAssets($builder);
    }

    /**
     * The merger reads the theme of the package, so it knows `rounded-control`, `rounded-popup` and
     * `shadow-popup`. The `css` option of the app replaces this file: its entry imports the theme.
     */
    private function prependClassMerger(ContainerBuilder $builder): void
    {
        if (!$builder->hasExtension('phem')) {
            return;
        }

        // The app chose its own merger: phem refuses the two options together.
        foreach ($builder->getExtensionConfig('phem') as $config) {
            if (null !== ($config['class_merger']['service'] ?? null)) {
                return;
            }
        }

        $builder->prependExtensionConfig('phem', [
            'class_merger' => [
                'tailwind' => [
                    'enabled' => true,
                    'css' => $this->getPath().'/assets/theme.css',
                ],
            ],
        ]);
    }

    private function prependAssets(ContainerBuilder $builder): void
    {
        // Git keeps no empty folder: a package with no assets has no assets/ folder.
        if (!interface_exists(AssetMapperInterface::class) || !is_dir($this->getPath().'/assets')) {
            return;
        }

        $builder->prependExtensionConfig('framework', [
            'asset_mapper' => [
                'paths' => [$this->getPath().'/assets' => '@phem/ui-base'],
            ],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Phem\Ui\Base\Tests\Ui;

use Phem\PhemBundle;
use Phem\Ui\Base\PhemUiBaseBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * The bundle turns on the Tailwind merger of phem. The config of the app wins.
 */
final class ClassMergerTest extends TestCase
{
    public function testTheBundleTurnsOnTheMergerWithTheTheme(): void
    {
        $merger = self::classMerger([]);

        self::assertTrue($merger['tailwind']['enabled']);
        self::assertSame(realpath(\dirname(__DIR__, 2).'/assets/theme.css'), realpath($merger['tailwind']['css']));
    }

    public function testTheCssOfTheAppReplacesTheTheme(): void
    {
        $css = \dirname(__DIR__, 2).'/demo/assets/styles/app.css';
        $merger = self::classMerger(['class_merger' => ['tailwind' => ['css' => $css]]]);

        self::assertTrue($merger['tailwind']['enabled']);
        self::assertSame($css, $merger['tailwind']['css']);
    }

    public function testTheAppCanTurnOffTheMerger(): void
    {
        $merger = self::classMerger(['class_merger' => ['tailwind' => false]]);

        self::assertFalse($merger['tailwind']['enabled']);
    }

    public function testTheMergerServiceOfTheAppWins(): void
    {
        $merger = self::classMerger(['class_merger' => ['service' => 'app.class_merger']]);

        self::assertFalse($merger['tailwind']['enabled']);
        self::assertSame('app.class_merger', $merger['service']);
    }

    /**
     * @param array<string, mixed> $app the phem config of the app
     *
     * @return array<string, mixed> the processed `class_merger` config
     */
    private static function classMerger(array $app): array
    {
        $builder = new ContainerBuilder();
        $builder->setParameter('kernel.environment', 'test');
        $builder->setParameter('kernel.build_dir', sys_get_temp_dir());

        $phem = (new PhemBundle())->getContainerExtension();
        self::assertInstanceOf(ConfigurationExtensionInterface::class, $phem);
        $builder->registerExtension($phem);
        $builder->loadFromExtension('phem', $app);

        $bundle = (new PhemUiBaseBundle())->getContainerExtension();
        self::assertInstanceOf(PrependExtensionInterface::class, $bundle);
        $bundle->prepend($builder);

        $configuration = $phem->getConfiguration([], $builder);
        self::assertInstanceOf(ConfigurationInterface::class, $configuration);
        $config = (new Processor())->processConfiguration($configuration, $builder->getExtensionConfig('phem'));

        return $config['class_merger'];
    }
}

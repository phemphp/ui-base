<?php

declare(strict_types=1);

namespace Phem\Ui\Base\Tests\Ui;

use Phem\Html;
use Phem\Test\InteractsWithPhem;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Each part has a `data-slot`: the kebab case of its name, with no final `Root` (`DialogPopup` is
 * `dialog-popup`, `SwitchRoot` is `switch`). App CSS and a parent (`has-data-[slot=dialog-title]:`)
 * use it to style a part. The icons, and the parts that render a `<template>` (MenuPayload,
 * PopoverPayload, PreviewCardPayload, ComboboxChipTemplate), have no slot.
 */
final class SlotTest extends KernelTestCase
{
    use InteractsWithPhem;

    private const string APP = 'Phem\Ui\Base\Tests\App\\';

    public function testEachRootOfEachPartHasTheSlotOfItsName(): void
    {
        $wrong = [];
        foreach (glob(\dirname(__DIR__, 2).'/src/*.phem') as $file) {
            $blocks = preg_split('/(?=#\[Component\b)/', (string) file_get_contents($file));

            foreach (\array_slice($blocks, 1) as $block) {
                preg_match('/(?:function|class)\s+(\w+)/', $block, $name);

                // The shared icons are glyphs, not parts.
                if (str_ends_with($name[1], 'Icon') && !\in_array($name[1], ['NavigationMenuIcon', 'SelectIcon', 'ComboboxIcon'], true)) {
                    continue;
                }

                // A `<template>` does not show: a payload has no slot.
                if (preg_match('/return <(template|PopoverPayload)\b/', $block)) {
                    continue;
                }

                $slot = self::slot($name[1]);

                // A subclass names its slot with the dataSlot() method.
                if (preg_match("/function dataSlot\(\): string\s*\{\s*return '([a-z-]+)';/", $block, $method)) {
                    if ($slot !== $method[1]) {
                        $wrong[] = $name[1].': '.$method[1];
                    }

                    if (!str_contains($block, 'return <')) {
                        continue;
                    }
                }

                // Each root: the first attribute after the tag.
                preg_match_all('/return\s*\(?\s*<[A-Za-z][\w.]*\s+(?:data-slot=(?:"([a-z-]+)"|\{\{ slot \}\}))?/', $block, $roots, \PREG_SET_ORDER);

                if ([] === $roots) {
                    $wrong[] = $name[1].': no root';
                }

                foreach ($roots as $root) {
                    $value = $root[1] ?? null;
                    $dynamic = str_contains($root[0], '{{ slot }}');

                    if (!$dynamic && $slot !== $value) {
                        $wrong[] = $name[1].': '.($value ?? 'no slot');
                    }
                }
            }
        }

        self::assertSame([], $wrong, 'These parts have no data-slot, or another name.');
    }

    /** The kebab case of the name, with no final `Root`: `OTPFieldInput` is `otp-field-input`, `SwitchRoot` is `switch`. */
    private static function slot(string $name): string
    {
        $name = preg_replace('/(?<=.)Root$/', '', $name) ?? $name;

        return strtolower((string) preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z0-9])([A-Z])/'], '$1-$2', $name));
    }

    public function testTheSlotIsTheNameOfThePart(): void
    {
        $this->mount('Phem\Ui\Base\Button', ['children' => new Html('Save')])
            ->assertSelectorExists('button[data-slot="button"]');

        $this->mount(self::APP.'MenuDemo')
            ->assertSelectorExists('[data-slot="menu"] [data-slot="menu-popup"] [data-slot="menu-item"]')
            ->assertSelectorExists('[data-slot="menu-submenu"] [data-slot="menu-submenu-trigger"]');
    }

    public function testASubclassHasItsOwnSlot(): void
    {
        $this->mount(self::APP.'ContextMenuDemo')
            ->assertSelectorExists('[data-slot="context-menu"] [data-slot="context-menu-trigger"]')
            ->assertSelectorNotExists('[data-slot="menu"]');

        $this->mount(self::APP.'ComboboxSelectionDemo')
            ->assertSelectorExists('[data-slot="autocomplete"] [data-slot="combobox-input"]');
    }

    public function testAPartThatRendersAnotherPartHasItsOwnSlot(): void
    {
        $this->mount(self::APP.'AlertDialogDemo')
            ->assertSelectorExists('[data-slot="alert-dialog"] [data-slot="alert-dialog-trigger"]')
            ->assertSelectorExists('[data-slot="alert-dialog-popup"] [data-slot="alert-dialog-title"]')
            ->assertSelectorNotExists('[data-slot="dialog-popup"]');

        $this->mount(self::APP.'DrawerDemo')
            ->assertSelectorExists('[data-slot="drawer-title"]')
            ->assertSelectorNotExists('[data-slot="dialog-title"]');
    }

    public function testACallerSlotReplacesTheSlot(): void
    {
        $this->mount('Phem\Ui\Base\Button', ['data-slot' => 'save', 'children' => new Html('Save')])
            ->assertSelectorExists('button[data-slot="save"]')
            ->assertSelectorNotExists('[data-slot="button"]');
    }
}

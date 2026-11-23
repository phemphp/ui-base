<?php

declare(strict_types=1);

namespace App\Tests;

use App\Ui\Gallery;
use Playwright\Page\PageInterface;

use function Playwright\Testing\expect;

/**
 * The parts follow the theme. `<html data-palette="shadcn">` turns on the second theme of the demo
 * (the shadcn theme of app.css): each part then takes its colors, its radius and its height.
 */
final class ThemeTest extends DemoTestCase
{
    /** The color of a token, as the browser computes it: `probe('--input')`. */
    private const string PROBE = <<<'JS'
        (value) => {
            const el = document.createElement('div')
            el.style.backgroundColor = value.startsWith('--') ? `var(${value})` : value
            document.body.append(el)
            const color = getComputedStyle(el).backgroundColor
            el.remove()

            return color
        }
        JS;

    /** The tokens of the colors of the theme. */
    private const array TOKENS = [
        '--background', '--foreground', '--popover', '--popover-foreground', '--primary', '--primary-foreground',
        '--muted', '--muted-foreground', '--accent', '--accent-foreground', '--destructive', '--border', '--input',
        '--ring', '--highlight', '--highlight-foreground', '--separator', '--overlay', '--drawer-overlay',
        '--link', '--match', '--disabled-foreground', '--disabled-border',
    ];

    /**
     * The background and the border colors of each part in the previews that are not a color of
     * the theme. A part with a fixed color class of its example (`border-neutral-950`) does not
     * count: the example sets it, not the package.
     */
    private const string OUTSIDE = <<<'JS'
        (allowed) => {
            const fixed = /(^|\s)([^\s:]+:)*(bg|border(-[xytrblse])?|text|fill|stroke|outline)-(black|white|\[#|(slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)-)/

            return [...document.querySelectorAll('[data-demo="preview"] [data-slot]')]
                .filter((el) => !fixed.test(el.getAttribute('class') ?? ''))
                .flatMap((el) => {
                    const style = getComputedStyle(el)
                    const colors = [['background', style.backgroundColor]]
                    for (const side of ['Top', 'Right', 'Bottom', 'Left']) {
                        if ('0px' !== style[`border${side}Width`] && 'none' !== style[`border${side}Style`]) {
                            colors.push([`border-${side.toLowerCase()}`, style[`border${side}Color`]])
                        }
                    }

                    return colors
                        .filter(([, color]) => !allowed.includes(color))
                        .map(([property, color]) => `${el.dataset.slot} ${property}: ${color}`)
                })
        }
        JS;

    public function testTheSecondThemeChangesTheTokens(): void
    {
        $page = $this->visitComponent('button');
        $base = $page->evaluate(self::PROBE, '--input');

        self::palette($page, 'shadcn');

        self::assertNotSame($base, $page->evaluate(self::PROBE, '--input'));
    }

    public function testAButtonFollowsTheTheme(): void
    {
        $page = $this->visitComponent('button');
        $button = $page->locator('[data-demo="preview"] [data-slot="button"]')->first();

        foreach (['light', 'dark'] as $theme) {
            self::palette($page, 'shadcn');
            $page->evaluate('(theme) => document.documentElement.dataset.theme = theme', $theme);

            $style = $button->evaluate('el => [getComputedStyle(el).backgroundColor, getComputedStyle(el).borderTopColor, getComputedStyle(el).borderTopLeftRadius, el.offsetHeight]');

            self::assertSame([$page->evaluate(self::PROBE, '--background'), $page->evaluate(self::PROBE, '--input'), '8px', 36], $style, $theme);
        }
    }

    /**
     * The palette picker of the sidebar turns on the second theme. The choice stays after a
     * reload: `palette_script()` gives `<html>` the stored palette, and the picker shows it.
     */
    public function testThePalettePickerChangesTheTheme(): void
    {
        $page = $this->visitComponent('button');
        $html = $page->locator('html');
        $picker = $page->getByRole('combobox', ['name' => 'Theme']);
        $base = $page->evaluate(self::PROBE, '--input');

        expect($html)->toHaveAttribute('data-palette', 'base');

        $picker->selectOption('shadcn');

        expect($html)->toHaveAttribute('data-palette', 'shadcn');
        self::assertNotSame($base, $page->evaluate(self::PROBE, '--input'));

        $page->reload();

        expect($html)->toHaveAttribute('data-palette', 'shadcn');
        expect($picker)->toHaveValue('shadcn');

        $picker->selectOption('base');

        expect($html)->toHaveAttribute('data-palette', 'base');
        self::assertSame($base, $page->evaluate(self::PROBE, '--input'));
    }

    /**
     * A test palette gives each token a color of its own hue. A fixed color of a part (an ink that
     * is equal to `--foreground` in a theme) is then never equal to a color of the theme.
     */
    public function testEachPartFollowsTheTheme(): void
    {
        $rules = [];
        foreach (self::TOKENS as $index => $token) {
            $rules[] = \sprintf('%s: oklch(0.6 0.15 %d);', $token, 10 + $index * 15);
        }

        $css = ':root[data-palette="test"] { '.implode(' ', $rules).' }';

        $outside = [];
        foreach (array_keys(Gallery::components()) as $slug) {
            $page = $this->visitComponent($slug);
            $page->evaluate('(css) => document.head.append(Object.assign(document.createElement("style"), { textContent: css }))', $css);
            self::palette($page, 'test');

            foreach ($page->evaluate(self::OUTSIDE, self::allowed($page)) as $color) {
                $outside[] = $slug.': '.$color;
            }
        }

        self::assertSame([], array_values(array_unique($outside)), 'These parts have a color that is not a color of the theme.');
    }

    /** Turns on a palette. The transitions stop, so the parts have their colors at once. */
    private static function palette(PageInterface $page, string $palette): void
    {
        $page->evaluate(<<<'JS'
            (palette) => {
                if (!document.getElementById('no-transitions')) {
                    const style = document.createElement('style')
                    style.id = 'no-transitions'
                    style.textContent = '*, *::before, *::after, ::backdrop { transition: none !important; }'
                    document.head.append(style)
                }

                document.documentElement.dataset.palette = palette
            }
            JS, $palette);
    }

    /**
     * The colors of the theme on the page, and transparent. `--accent-active` is a mix of
     * `--accent` and `--foreground`.
     *
     * @return list<string>
     */
    private static function allowed(PageInterface $page): array
    {
        $colors = ['rgba(0, 0, 0, 0)', $page->evaluate(self::PROBE, 'color-mix(in oklab, var(--accent), var(--foreground) 10%)')];
        foreach (self::TOKENS as $token) {
            $colors[] = $page->evaluate(self::PROBE, $token);
        }

        return array_values(array_unique($colors));
    }
}

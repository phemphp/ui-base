<?php

declare(strict_types=1);

namespace App\Tests;

use function Playwright\Testing\expect;

final class DisclosureTest extends DemoTestCase
{
    // Fires `beforematch` on the panel, and counts the height animations that its open starts.
    private const FOUND_ANIMATIONS = <<<'JS'
        panel => {
            panel.dispatchEvent(new Event('beforematch', { bubbles: true }));
            getComputedStyle(panel).height;

            return panel.getAnimations().filter((animation) => animation.transitionProperty === 'height').length;
        }
        JS;

    public function testAccordionOpensOneItemAtATime(): void
    {
        $page = $this->visitComponent('accordion');
        $hero = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]');

        $what = $hero->getByRole('button', ['name' => 'What is Base UI?']);
        $start = $hero->getByRole('button', ['name' => 'How do I get started?']);
        $answer = $hero->locator('text=Base UI is a library of high-quality');

        expect($answer)->toBeHidden();
        $what->click();
        expect($what)->toHaveAttribute('aria-expanded', 'true');
        expect($what)->toHaveAttribute('data-panel-open', '');
        expect($answer)->toBeVisible();

        // As in Base UI v1.9.0, the arrow keys do not move the focus: Tab goes to the next trigger.
        expect($hero->locator('h3[data-phem-accordion-target="header"]')->first())->toHaveAttribute('data-open', '');
        $page->keyboard()->press('ArrowDown');
        expect($what)->toBeFocused();
        $page->keyboard()->press('Tab');
        expect($start)->toBeFocused();
        $page->keyboard()->press('Enter');
        expect($start)->toHaveAttribute('aria-expanded', 'true');
        expect($what)->toHaveAttribute('aria-expanded', 'false');
        expect($answer)->toBeHidden();
    }

    public function testAccordionWithRovingFocusMovesWithTheArrowKeysHomeAndEnd(): void
    {
        $page = $this->visitComponent('accordion');
        $hero = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]');
        $triggers = $hero->locator('[data-slot="accordion-trigger"]');

        // The demo of Base UI has no `rovingFocus`: the value turns it on.
        $hero->locator('[data-controller~="phem-accordion"]')->first()->evaluate('el => el.setAttribute("data-phem-accordion-roving-focus-value", "true")');

        $triggers->first()->focus();
        $page->keyboard()->press('ArrowDown');
        expect($triggers->nth(1))->toBeFocused();
        $page->keyboard()->press('End');
        expect($triggers->last())->toBeFocused();
        $page->keyboard()->press('Home');
        expect($triggers->first())->toBeFocused();
    }

    public function testAccordionOpensMultiplePanels(): void
    {
        $page = $this->visitComponent('accordion');
        $example = $page->getByRole('region', ['name' => 'Open multiple panels'])->locator('[data-demo="preview"]');

        $example->getByRole('button', ['name' => 'What is Base UI?'])->click();
        $example->getByRole('button', ['name' => 'How do I get started?'])->click();
        expect($example->locator('text=Base UI is a library of high-quality'))->toBeVisible();
        expect($example->locator('text=Head to the “Quick start” guide'))->toBeVisible();
    }

    public function testAccordionHidesTheClosedPanelsUntilFound(): void
    {
        $page = $this->visitComponent('accordion');
        $example = $page->getByRole('region', ['name' => 'Hidden until found'])->locator('[data-demo="preview"]');
        $panel = $example->locator('text=restocking fee')->locator('xpath=ancestor::*[@data-phem-accordion-target="panel"]');

        expect($panel)->toHaveAttribute('hidden', 'until-found');

        // The search of the browser fires `beforematch` on the panel with the text, and opens it
        // with no height animation: the browser scrolls to the text.
        self::assertSame(0, $panel->evaluate(self::FOUND_ANIMATIONS));
        $trigger = $example->getByRole('button', ['name' => 'What is your return policy?']);
        expect($trigger)->toHaveAttribute('aria-expanded', 'true');
        expect($example->locator('text=restocking fee'))->toBeVisible();

        // The closed panel stays in the layout (`content-visibility: hidden`), with the height 0
        // after the end of the close.
        $trigger->click();
        expect($panel)->toHaveAttribute('hidden', 'until-found');
        $page->evaluate('() => new Promise((resolve) => setTimeout(resolve, 400))');
        self::assertSame(0, $panel->evaluate('panel => Math.round(panel.getBoundingClientRect().height)'));
    }

    public function testAccordionShowsTheSourceOfEachDemo(): void
    {
        $page = $this->visitComponent('accordion');
        $page->context()->grantPermissions(['clipboard-read', 'clipboard-write']);

        $sources = $page->locator('[data-demo="source"]');
        expect($sources)->toHaveCount(3);

        $source = $page->getByRole('region', ['name' => 'Open multiple panels'])->locator('[data-demo="source"]');
        // The classes first, then the functions, each in the alphabetical order.
        $code = $source->locator('code');
        expect($code)->toContainText("use Phem\\Node;\nuse Phem\\Ui\\Base\\{Accordion, AccordionItem};\nuse function Phem\\Ui\\Base\\{AccordionHeader, AccordionPanel, AccordionTrigger};");
        expect($code)->toContainText('<Accordion id="multiple" multiple>');
        expect($code)->not()->toContainText('DemoPage');

        // A long code shows its first lines. "Show code" shows all of it, and "Hide code" hides it again.
        $clipped = 'box => box.scrollHeight > box.clientHeight';
        self::assertTrue($source->locator('[data-phem-clipboard-target="source"]')->evaluate($clipped));
        $source->getByText('Show code')->click();
        expect($source->getByRole('checkbox', ['name' => 'Show code']))->toBeChecked();
        self::assertFalse($source->locator('[data-phem-clipboard-target="source"]')->evaluate($clipped));
        $source->getByText('Hide code')->click();
        self::assertTrue($source->locator('[data-phem-clipboard-target="source"]')->evaluate($clipped));

        $source->getByRole('button', ['name' => 'Copy code'])->click();
        expect($source->getByRole('status'))->toHaveText('Copied');
        self::assertStringStartsWith('<?phem', $page->evaluate('() => navigator.clipboard.readText()'));
    }

    public function testCollapsible(): void
    {
        $page = $this->visitComponent('collapsible');
        $hero = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]');

        $trigger = $hero->getByRole('button', ['name' => 'Recovery keys']);
        expect($hero->getByText('alien-bean-pasta'))->toBeHidden();

        $trigger->click();
        expect($trigger)->toHaveAttribute('aria-expanded', 'true');
        expect($hero->getByText('alien-bean-pasta'))->toBeVisible();

        $trigger->click();
        expect($trigger)->toHaveAttribute('aria-expanded', 'false');
        expect($hero->getByText('alien-bean-pasta'))->toBeHidden();
    }

    public function testCollapsibleOpensWithTheKeyboardAndMeasuresItsPanel(): void
    {
        $page = $this->visitComponent('collapsible');
        $hero = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]');
        $trigger = $hero->getByRole('button', ['name' => 'Recovery keys']);
        $panel = $hero->locator('[data-slot="collapsible-panel"]');

        $trigger->focus();
        $page->keyboard()->press('Enter');
        expect($trigger)->toHaveAttribute('aria-expanded', 'true');
        expect($panel)->toBeVisible();

        // The height variable of the animation is `auto` when the panel is open, as in Base UI.
        $panel->evaluate('el => Promise.all(el.getAnimations().map((animation) => animation.finished))');
        self::assertSame('auto', $panel->evaluate('el => el.style.getPropertyValue("--collapsible-panel-height")'));

        $page->keyboard()->press('Space');
        expect($trigger)->toHaveAttribute('aria-expanded', 'false');
        expect($panel)->toBeHidden();
    }

    public function testCollapsibleHidesItsPanelUntilFound(): void
    {
        $page = $this->visitComponent('collapsible');
        $example = $page->getByRole('region', ['name' => 'Hidden until found'])->locator('[data-demo="preview"]');
        $panel = $example->locator('[data-phem-collapsible-target="panel"]');

        expect($panel)->toHaveAttribute('hidden', 'until-found');

        // The search of the browser fires `beforematch` on the panel with the text, and opens it
        // with no height animation: the browser scrolls to the text.
        self::assertSame(0, $panel->evaluate(self::FOUND_ANIMATIONS));
        $trigger = $example->getByRole('button', ['name' => 'Shipping details']);
        expect($trigger)->toHaveAttribute('aria-expanded', 'true');
        expect($example->locator('text=Standard shipping takes'))->toBeVisible();

        // The closed panel stays in the layout (`content-visibility: hidden`), with the height 0
        // after the end of the close.
        $trigger->click();
        expect($panel)->toHaveAttribute('hidden', 'until-found');
        $page->evaluate('() => new Promise((resolve) => setTimeout(resolve, 400))');
        self::assertSame(0, $panel->evaluate('panel => Math.round(panel.getBoundingClientRect().height)'));
    }

    public function testTabs(): void
    {
        $page = $this->visitComponent('tabs');
        $hero = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]');

        $overview = $hero->getByRole('tab', ['name' => 'Overview']);
        $projects = $hero->getByRole('tab', ['name' => 'Projects']);

        expect($overview)->toHaveAttribute('aria-selected', 'true');
        expect($hero->getByRole('tabpanel'))->toHaveText('Workspace stats and activity.');

        $projects->click();
        expect($projects)->toHaveAttribute('aria-selected', 'true');
        expect($projects)->toHaveAttribute('data-active', '');
        expect($overview)->toHaveAttribute('aria-selected', 'false');
        expect($hero->getByRole('tabpanel'))->toHaveText('Milestones and deadlines.');

        // The arrow keys move the focus, and Enter selects.
        $page->keyboard()->press('ArrowRight');
        expect($hero->getByRole('tab', ['name' => 'Account']))->toBeFocused();
        $page->keyboard()->press('Enter');
        expect($hero->getByRole('tabpanel'))->toHaveText('Profile and preferences.');
    }

    public function testTheTabsIndicatorHasThePlaceOfTheSelectedTab(): void
    {
        $page = $this->visitComponent('tabs');
        $hero = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]');
        $indicator = $hero->locator('[data-slot="tabs-indicator"]');
        $place = 'el => [el.getBoundingClientRect().left - el.closest("[data-slot=tabs-list]").getBoundingClientRect().left + "px", el.getBoundingClientRect().width + "px"]';
        $variables = 'el => [getComputedStyle(el).getPropertyValue("--active-tab-left").trim(), getComputedStyle(el).getPropertyValue("--active-tab-width").trim()]';

        foreach (['Overview', 'Account'] as $name) {
            $tab = $hero->getByRole('tab', ['name' => $name]);
            $tab->click();

            expect($tab)->toHaveAttribute('aria-selected', 'true');
            self::assertSame($tab->evaluate($place), $indicator->evaluate($variables), $name);
        }
    }

    public function testTabsAnimateTheirPanels(): void
    {
        $page = $this->visitComponent('tabs');
        $example = $page->getByRole('region', ['name' => 'Animated panels'])->locator('[data-demo="preview"]');

        $example->getByRole('tab', ['name' => 'Account'])->click();
        expect($example->getByRole('tabpanel'))->toHaveText('Profile and preferences.');
        expect($example->locator('[data-phem-tabs-target="panel"][data-activation-direction="right"]')->first())->toBeAttached();
    }

    public function testTabsHiddenUntilFound(): void
    {
        $page = $this->visitComponent('tabs');
        $example = $page->getByRole('region', ['name' => 'Hidden until found'])->locator('[data-demo="preview"]');

        // The panels that are not selected stay searchable.
        $panel = $example->locator('[data-phem-tabs-target="panel"][data-value="projects"]');
        expect($panel)->toHaveAttribute('hidden', 'until-found');

        // The search of the browser finds "deadlines": the browser sends beforematch to the panel.
        $panel->dispatchEvent('beforematch');
        expect($example->getByRole('tab', ['name' => 'Projects']))->toHaveAttribute('aria-selected', 'true');
        expect($panel)->toBeVisible();
        expect($example->locator('[data-phem-tabs-target="panel"][data-value="overview"]'))->toHaveAttribute('hidden', 'until-found');
    }
}

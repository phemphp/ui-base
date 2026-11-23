<?php

declare(strict_types=1);

namespace Phem\Ui\Base\Tests\Ui;

use Phem\Test\InteractsWithPhem;
use Phem\Ui\Base\ValueFormat;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DisplayTest extends KernelTestCase
{
    use InteractsWithPhem;

    public function testTheProgressShowsItsValue(): void
    {
        $this->mount('Phem\Ui\Base\Tests\App\ProgressDemo')
            ->assertSelectorExists('#export[role="progressbar"][aria-valuenow="20"][aria-labelledby="export-label"][data-progressing]')
            ->assertSelectorExists('#export-label[data-progressing]')
            ->assertSelectorTextContains('#export span[aria-hidden]', '20%')
            ->assertSelectorExists('#export div[style*="width: 20%"]');
    }

    public function testANullProgressIsIndeterminate(): void
    {
        $this->mount('Phem\Ui\Base\Tests\App\ProgressDemo', ['value' => null])
            ->assertSelectorExists('#export[data-indeterminate]:not([aria-valuenow])');
    }

    public function testAFullProgressIsComplete(): void
    {
        $this->mount('Phem\Ui\Base\Tests\App\ProgressDemo', ['value' => 100.0])
            ->assertSelectorExists('#export[data-complete]');
    }

    public function testTheFormatAndTheLocaleChangeTheTextOfTheValue(): void
    {
        // A decimal: the value, with the separators of the locale.
        $this->mount('Phem\Ui\Base\Meter', ['value' => 1234.5, 'max' => 2000, 'format' => ['style' => 'decimal'], 'locale' => 'de-DE'])
            ->assertSelectorExists('[role="meter"][aria-valuetext="1.234,5"]');

        // The same in markup.
        $this->mount('Phem\Ui\Base\Tests\App\MeterFormatDemo')
            ->assertSelectorExists('[role="meter"][aria-valuetext="1.234,5"]');

        // A currency.
        $this->mount('Phem\Ui\Base\Meter', ['value' => 1234.5, 'max' => 2000, 'format' => ['style' => 'currency', 'currency' => 'EUR', 'maximumFractionDigits' => 0], 'locale' => 'en-US'])
            ->assertSelectorExists('[role="meter"][aria-valuetext="€1,235"]');

        // A locale alone: the percent of the range, in the format of the locale.
        $this->mount('Phem\Ui\Base\Progress', ['value' => 24, 'locale' => 'de-DE'])
            ->assertSelectorExists("[role=\"progressbar\"][aria-valuetext=\"24\u{a0}%\"]");
    }

    public function testAFormatNeedsAKnownStyle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "style" of a format must be "percent", "decimal" or "currency", "unit" given.');

        (new ValueFormat(['style' => 'unit']))->format(1, 1);
    }

    public function testTheMeterShowsItsValue(): void
    {
        $this->mount('Phem\Ui\Base\Tests\App\MeterDemo')
            ->assertSelectorExists('#storage[role="meter"][aria-valuenow="24"][aria-valuetext="24%"]')
            ->assertSelectorExists('#storage div[style*="width: 24%"]');
    }

    public function testADisabledFieldsetDisablesItsFields(): void
    {
        // The native `disabled` of the fieldset disables each control in it, and the fieldset
        // gets `data-disabled` for the styles.
        $this->mount('Phem\Ui\Base\Tests\App\FieldsetDisabledDemo')
            ->assertSelectorExists('fieldset[disabled][data-disabled] input[name="company"]')
            ->assertSelectorExists('fieldset[disabled][data-disabled] input[name="tax"]');
    }

    public function testTheFieldsetHasALegendAndASeparator(): void
    {
        $this->mount('Phem\Ui\Base\Tests\App\FieldsetDemo')
            ->assertSelectorTextContains('fieldset legend', 'Billing details')
            // A legend is not a flex item: the fieldset gives it the margin of its gap.
            ->assertSelectorExists('fieldset[class~="[--fieldset-gap:1rem]"][class~="gap-[var(--fieldset-gap)]"][class~="[&>legend]:mb-[var(--fieldset-gap)]"]')
            ->assertSelectorExists('fieldset input[name="company"]')
            ->assertSelectorExists('[role="separator"][aria-orientation="vertical"][data-orientation="vertical"]');
    }
}

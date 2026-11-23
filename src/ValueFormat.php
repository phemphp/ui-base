<?php

declare(strict_types=1);

namespace Phem\Ui\Base;

/**
 * The text of the value of a Meter or a Progress: the `format` and `locale` props of Base UI. The
 * options are the options of `Intl.NumberFormat` that PHP `NumberFormatter` has: `style`
 * (`percent`, the default, `decimal` or `currency`), `currency`, `minimumFractionDigits` and
 * `maximumFractionDigits`.
 *
 * With no options and no locale, the text is the rounded percent (`24%`), and the intl extension
 * of PHP is not necessary.
 */
final class ValueFormat
{
    /**
     * @param array{style?: string, currency?: string, minimumFractionDigits?: int, maximumFractionDigits?: int} $options
     */
    public function __construct(
        private readonly array $options = [],
        private readonly ?string $locale = null,
    ) {
    }

    /** The text of a value. `$percent` is the place of the value in its range, from 0 to 100. */
    public function format(float $value, float $percent): string
    {
        if ([] === $this->options && null === $this->locale) {
            return round($percent).'%';
        }

        if (!class_exists(\NumberFormatter::class)) {
            throw new \LogicException('The "format" and "locale" props of a Meter and a Progress need the intl extension of PHP.');
        }

        $style = $this->options['style'] ?? 'percent';
        $formatter = new \NumberFormatter($this->locale ?? \Locale::getDefault(), match ($style) {
            'percent' => \NumberFormatter::PERCENT,
            'decimal' => \NumberFormatter::DECIMAL,
            'currency' => \NumberFormatter::CURRENCY,
            default => throw new \InvalidArgumentException(\sprintf('The "style" of a format must be "percent", "decimal" or "currency", "%s" given.', $style)),
        });

        // The rounding of `Intl.NumberFormat` (`halfExpand`): 1234.5 is 1235. ICU rounds to the even.
        $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFUP);

        if (isset($this->options['minimumFractionDigits'])) {
            $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $this->options['minimumFractionDigits']);
        }

        if (isset($this->options['maximumFractionDigits'])) {
            $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $this->options['maximumFractionDigits']);
        }

        // A percent is the place in the range, as the default text. The other styles show the value.
        if ('percent' === $style) {
            return (string) $formatter->format($percent / 100);
        }

        if ('currency' === $style) {
            return (string) $formatter->formatCurrency($value, $this->options['currency'] ?? throw new \InvalidArgumentException('A "currency" format needs a "currency" code, for example "EUR".'));
        }

        return (string) $formatter->format($value);
    }
}

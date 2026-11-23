# Changelog

## Unreleased

## 0.5.0

- `Meter` and `Progress` take the `format` and `locale` props of Base UI. The `format` options are the options of `Intl.NumberFormat` that PHP has: `style` (`percent`, the default, `decimal` or `currency`), `currency`, `minimumFractionDigits` and `maximumFractionDigits`. `<Meter value={{ 1234.5 }} max={{ 2000 }} format={{ {style: 'decimal'} }} locale="de-DE">` has the text `1.234,5`. With no `format` and no `locale`, the text is the rounded percent (`24%`), as before. The props need the intl extension of PHP (`ext-intl`, a suggestion of the package).

- The parts that are a button render the `Button` component: `DialogTrigger`, `DialogClose`, `PopoverTrigger`, `PopoverClose`, `MenuTrigger`, `SelectTrigger`, `CollapsibleTrigger`, `ToastAction` and `ToastClose` (and the AlertDialog and Drawer parts that render them). One replacement of `Button` (`#[Component(replaces: Button::class)]`) now changes all of them. A trigger in a `Toolbar` or a `Menubar` keeps the look of the bar. `Style::BUTTON` and `Style::BUTTON_LOOK` are removed: `Button` has the classes. A trigger is now in a `<phem-c>` element (`display: contents`), as each part that renders another part, so a CSS child selector (`#menu > button`) does not match it: use a descendant selector.

- The last fixed colors of the parts are theme tokens, and the parts have no `dark:` class. The backdrop of `Drawer` uses `--drawer-overlay` (default: `--overlay`, and `black / 70%` in dark mode), the link of `Toolbar` uses `--link` (default: `blue-700`, `blue-500` in dark mode), and the default `markClassName` of `ComboboxHighlight` is `bg-transparent font-bold text-match` with `--match` (default: `blue-800`, `blue-500` in dark mode). The cursor of `NumberFieldScrubAreaCursor` takes `--foreground` and `--background`: in dark mode it is white with a dark outline (before: black in the two modes).

- Each part has a `data-slot`: the kebab case of its name, with no final `Root` (`<DialogPopup>` renders `data-slot="dialog-popup"`, and `<SwitchRoot>` renders `data-slot="switch"`). App CSS (`[data-slot="dialog-popup"]`) and a parent (`has-data-[slot=dialog-description]:`) can then style a part without a `className`. A part that renders another part keeps its own name (`alert-dialog-popup`, `drawer-title`), and `Autocomplete`, `ContextMenu` and `MenuSubmenu` have their own names. A `data-slot` of the caller replaces it. The icons have no slot, and nor do `MenuPayload`, `PopoverPayload`, `PreviewCardPayload` and `ComboboxChipTemplate`: they render a `<template>`, which does not show. See the README.

- A theme can set the height of the controls with the `--control-height` token (default: `2rem`, the `h-8` of before). The controls use `h-control`, `size-control`, `w-control`, `min-w-control` and `min-h-control` in place of `h-8`, `size-8`, `w-8`, `min-w-8` and `min-h-8`: the button, the input, the number field, the select, the combobox, the toggle, the toolbar button, the trigger of a menu, a navigation menu, a collapsible and a tooltip, the action of a toast, and the filter of a menu. A tab is one pixel higher than a control (`h-[calc(var(--spacing-control)+1px)]`), and an input of an `OTPField` is a square of the control height plus `0.5rem` (before: `h-10 w-10`). The square buttons stay square. The examples use the same classes for their own buttons and inputs. For the shadcn height, set `--control-height: 2.25rem` (see the README). The padding and the text size of each control do not change.

- The joined controls take the radius of the theme on their outer corners. The input and the buttons of `NumberField` have `first:rounded-s-control last:rounded-e-control`, and a `bordered` `TooltipTrigger` has `first-of-type:rounded-s-control last-of-type:rounded-e-control`, so the `Tooltip` and the `TooltipPopup` next to it do not count. A row of joined parts is round on the outside, and a part alone is round on all its corners. Before, `--radius` did not change them. To change these corners, pass the same variants (`first:rounded-s-none`): a plain `rounded-none` does not replace them. The parts use the logical sides, so a right-to-left page rounds the correct corners: `NumberFieldDecrement` has `border-e-0` (before: `border-r-0`) and `NumberFieldIncrement` has `border-s-0` (before: `border-l-0`). The examples join the triggers with `border-s-0`.

- A theme can set the look of a disabled part with three new tokens: `--disabled-foreground` and `--disabled-border` (default: `--muted-foreground`), and `--disabled-opacity` (default: none, so the part has its full opacity). The parts use `data-disabled:text-disabled-foreground`, `data-disabled:border-disabled-border` and `data-disabled:opacity-(--disabled-opacity)` in place of the muted colors. The default look does not change. For the shadcn look (normal colors with `opacity-50`), set `--disabled-foreground: var(--foreground)`, `--disabled-border: var(--input)` and `--disabled-opacity: 0.5` (see the README).

- A `hover:` class of the caller replaces the hover color of a part. The parts use a plain `hover:` (`hover:bg-accent`) in place of a state chain (`hover:not-data-disabled:bg-accent`), so the Tailwind merger puts them in the same group: `<Button className="hover:bg-blue-700">` removes `hover:bg-accent`. A disabled part gets `pointer-events-none` (`data-disabled:`, and `aria-disabled:` on `Button`), so it has no hover color. This changes `Accordion`, `Button`, `Menu` (the Menubar trigger), `NumberField`, `Select` (in a toolbar), `Toggle`, `ToggleGroup`, `Toolbar` and the triggers that use `Style::BUTTON`.

## 0.4.0

- The bundle turns on the Tailwind merger of phem (`phem.class_merger.tailwind`), with the theme of the package (`assets/theme.css`) as its `css` option. A `className` of the caller now replaces a class of the same group of a part with no config: `<Button className="rounded-md">` removes `rounded-control`. The config of the app wins: its `css` option replaces the theme file, `tailwind: false` turns off the merger, and with `class_merger.service` the bundle does not turn it on.

## 0.3.0

- The package requires `tales-from-a-dev/tailwind-merge-php` (`^0.5`). Before, it was only a suggestion. The Tailwind merger of phem needs it, so that a `className` of the caller replaces a class of the same group of a part. Turn on `phem.class_merger.tailwind` in the app to use it.

## 0.2.0

- The package requires phem 0.8 (`phem/phem` `^0.8`). The components and the demo do not use the `#[Async]` event handlers that phem 0.8 removed, so no code changed.

## 0.1.0

- The first release, for phem 0.7. It has the 37 components of Base UI v1.9.0, styled with Tailwind CSS: the parts follow the Base UI anatomy and use the phem controllers.
- The examples of the package (`examples/`, namespace `Phem\Ui\Base\Examples`) are the demos of the gallery. They are in the Composer dist, so coding agents find them with `phemc components`, `phemc search` and the phem MCP server.
- The guide of the package (`README.md`) shows how to install the bundle, load the theme and use the components.
- The repository is standalone again: it requires `phem/phem` from Packagist and runs its commands in its own Docker images (`compose.yaml`).

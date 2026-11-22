# Changelog

## Unreleased

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

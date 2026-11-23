# TODO

The work that is still open in `phem/ui-base`. The bundle has no workarounds. A missing feature goes into phem core first, then the bundle uses it.

## Gaps of phem

A gap is a behavior of Base UI v1.9.0 that needs a change in a phem controller. Fix it in phem and in the bundle in the same change: the bundle does not work around it.

- **Submenu: the close after a move to a sibling item.** Measured again with phem 0.8: while a submenu is open, `phem-menu` waits `SUBMENU_GRACE` (150 ms, in `phem_menu_controller.js`) before it highlights a sibling item and closes the submenu, so the pointer can cross a sibling item on its way to the submenu. Base UI took about 60 ms. A shorter grace in phem is closer to Base UI, but the diagonal moves to a submenu (the e2e `MenuTest` of phem) must still work: decide the value in phem.
- **A theme picker in the demo.** The demo has a second theme (the shadcn theme of `demo/assets/styles/app.css`, on `<html data-palette="shadcn">`), and `demo/tests/ThemeTest.php` checks that each part follows it. phem has the picker in its next release: the `phem-palette` controller and `palette_script()` (phem `## Unreleased`). After that release, put `{{ palette_script(['base', 'shadcn']) }}` in the `<head>` of the demo, and a `phem-palette` picker next to the dark switch of the sidebar. The `base` palette needs no rules: it is the default of `assets/theme.css`.

These results are different from Base UI, and phem keeps them:

- **Toast: a new toast for the same message.** Base UI shows a new toast for each `add`, unless the `id` is the same. `phem-toast` does not show a toast with no `id` and the same type, title and message as a toast on the screen: it starts the time of that toast again. A toast with an `id` follows Base UI.
- **Toast: the promise and the custom data.** Base UI `toastManager.promise()` and `data` (any object, also functions) are JavaScript. In phem, a `data-phem-toast-show` button shows the loading toast at the click, the handler answers with a toast with the same `id`, and `data` fills the `ToastData` parts of the template.
- **Menu: `data-instant="trigger-change"`.** Base UI sets it when a menu with more than one trigger changes its trigger. A phem menu that moves to another trigger removes `data-instant`, so the CSS transitions of the move, of the size and of the content run (see the "Animating the Menu" example).

## Open differences in the bundle

These differences need no phem change. The hero demos show the first one, and do not use the others.

- **The two variants of Base UI.** base-ui.com shows the CSS Modules variant of each demo. The bundle copies the Tailwind variant. They are different in three places, and the bundle is equal to the Tailwind variant:
  - the curve of a transition: CSS `ease`, `ease-out` and `ease-in-out` on the page, the default curve of Tailwind in the bundle (the indicator of Tabs, Meter and Progress, and the popups);
  - the Menubar trigger: the page has a hover color and an active color, and no background with a keyboard focus;
  - the Tooltip trigger with a keyboard focus and an open tooltip: transparent on the page, `neutral-100` in the bundle.
- **Drawer: the iOS variants.** The bundle gives the popup a bleed of 3rem past its edge, and has the `DrawerViewport` part. It does not have the iOS variants of the Base UI classes (`supports-[-webkit-touch-callout:none]`, `--viewport-padding`).
- **Defaults:** the Positioner `align` of Base UI is `center` for a root menu, a select and a combobox. The bundle uses `start`. The hero demos set `start` or have the width of the anchor, so the look is equal.
- **Elements:** `FieldsetLegend` is a native `<legend>` (Base UI: a `<div>`), and it gets no `data-disabled`. The popup and the arrow of a tooltip are `<span>` elements (Base UI: `<div>`). An item of a menu is a `<button>` (Base UI: a `<div>`).
- **Font:** base-ui.com uses its own font ("Die Grotesk A"). The demo uses the system font, so a text is about 1% wider and the description of the OTP field has two lines.
- **Theme tokens:** one token has one value, so three colors of the Tailwind variant changed a little. The descriptions and the labels of Tabs use `--muted-foreground` (`neutral-500`, Base UI: `neutral-600`; in dark mode the Tabs labels are `neutral-400`, Base UI: `neutral-300`). The track of ScrollArea uses `--muted` (`neutral-200` / `neutral-800`, Base UI: `black/12` / `white/12`). The `active:` color is a mix of `--accent` and `--foreground`, not `neutral-200` / `neutral-700`.

## Browser tests of the demo

`make test-e2e` runs them with Playwright PHP and the Playwright Symfony bundle, with no web server. Each component has tests of its main behaviors and of the options of its demos.

Open work for the browser tests:

- **Dark mode.** Run a test of each overlay with `colorScheme: dark`, and compare with base-ui.com.
- **Visual check.** Add screenshot tests (`toHaveScreenshot`) for each demo, after the look is final.

## To build here

- **Dark mode check.** Compare each demo page with base-ui.com in dark mode.

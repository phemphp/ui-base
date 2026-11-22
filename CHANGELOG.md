# Changelog

## Unreleased

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

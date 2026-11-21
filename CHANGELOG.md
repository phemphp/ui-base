# Changelog

## Unreleased

## 0.1.0

- The first release, for phem 0.7. It has the 37 components of Base UI v1.9.0, styled with Tailwind CSS: the parts follow the Base UI anatomy and use the phem controllers.
- The examples of the package (`examples/`, namespace `Phem\Ui\Base\Examples`) are the demos of the gallery. They are in the Composer dist, so coding agents find them with `phemc components`, `phemc search` and the phem MCP server.
- The guide of the package (`README.md`) shows how to install the bundle, load the theme and use the components.
- The repository is standalone again: it requires `phem/phem` from Packagist and runs its commands in its own Docker images (`compose.yaml`).

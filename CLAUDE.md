# phem/ui-base

The Base UI components for phem (https://github.com/phemphp/phem), styled with Tailwind CSS, and
their demo (`demo/`). The package has no stable release yet, so the API can change: do not keep
backward compatibility, and do not add aliases or deprecation layers.

## phem

- The package requires the `phem/phem` release from Packagist. The commands use
  `vendor/bin/phemc`, the phemc binary of that release.
- The components use only the public API of phem: the components, the controllers and their
  documented attributes.
- A missing behavior goes into phem (a controller, a component), not into this package. Do not
  work around it here. Add it to `TODO.md`, and use it after the phem release that has it.

## Writing style

Write all prose (docs, docblocks, comments, changelog) in ASD-STE100 (Simplified Technical
English): short sentences, one idea in each sentence, active voice, approved plain words.

## Code style

Use blank lines to show the steps of a method, as phem does: a blank line after the guard clauses,
after a block, and before the last `return`.

In the demos (`demo/src/Ui/Demos/`) and the examples (`examples/`), write the `className` of each
element inline. Do not put classes in constants: the duplication is correct.

## Examples

The demos of the gallery are the examples of the package. Give each example a docblock: its title,
then the text of its section on base-ui.com.

## Changelog

Add an entry to `CHANGELOG.md` for each change that a user sees or can rely on. Put it at the top
of the `## Unreleased` section.

## Tests

Run the tests after each change, before you report the work as done:

- `make test`: `phemc check` of the components and the examples, the retrieval eval of the search
  (`tests/retrieval.yaml`), and the PHPUnit render tests.
- `make test-e2e`: the browser tests of the demo. Add `ARGS="--filter Dialog"` for one test. A
  failed test saves a screenshot and the HTML of the page in `demo/var/failures/`.

Report the result. If a test fails, show the output.

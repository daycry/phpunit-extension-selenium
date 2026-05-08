# Migration v1 → v2

The full migration guide lives at the project root in
[`UPGRADE-2.0.md`](../UPGRADE-2.0.md). This page links the most common cases
quickly.

## Quick lookup

Inside a test class that uses `SeleniumAware`, `$this->browser()` returns
the v2 facade:

| You had this in v1                          | Use this in v2                                                                 |
|---------------------------------------------|--------------------------------------------------------------------------------|
| `SeleniumDriver::getDriver()`               | `$this->selenium()->driver()`                                                  |
| `goToUrl($url)`                             | `$this->browser()->visit($url)`                                                |
| `clickElementBy($k, 'css')`                 | `$this->browser()->click(Locator::css($k))`                                    |
| `fillFieldBy($k, $v, 'name', 25)`           | `$this->browser()->type(Locator::name($k), $v, delayMs: 25)`                   |
| `waitElement($k, 'id')`                     | `$this->browser()->waitFor(Locator::id($k))`                                   |
| `waitPageLoaded('/dash')`                   | `$this->browser()->wait()->forUrl('/dash')->run()`                             |
| `getValueFromElement($k, 'id', 'value')`    | `$this->browser()->find(Locator::id($k))->attribute('value')`                  |
| `takeScreenshot($f)`                        | `$this->browser()->screenshot($f)`                                             |

Inside Page Objects, replace `$this->browser()` with the constructor-injected
property `$this->browser`.

## Step-by-step

See [`UPGRADE-2.0.md`](../UPGRADE-2.0.md#suggested-migration-order) for the
recommended order:

1. Bump `composer.json` to `^2.0`.
2. Run the existing suite — v1 traits still emit deprecations but work.
3. Migrate one pilot class.
4. Mechanical search-and-replace using the table above.
5. Add Feature tests against a real Grid in CI (see the
   [integration workflow](../.github/workflows/integration.yml) for a
   reference setup).

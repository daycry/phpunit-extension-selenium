# Contributing

Thanks for your interest in improving `daycry/phpunit-extension-selenium`.

## Local setup

```bash
composer install
composer ci
```

`composer ci` runs the same checks executed in CI:

| Step                         | Command                                            |
|------------------------------|----------------------------------------------------|
| Code style (PSR-12 + risky)  | `composer cs-check` (auto-fix: `composer cs-fix`)  |
| Static analysis              | `composer phpstan`                                 |
| Refactor opportunities       | `composer rector-check` (apply: `composer rector-fix`) |
| Unit tests                   | `composer test`                                    |
| Mutation testing (optional)  | `composer mutate`                                  |

The unit suite runs in seconds without a real browser; everything that
needs a Selenium hub lives under `tests/Feature` and is gated by
`@group integration` plus `SELENIUM_E2E=1`.

## Toolchain

| Tool               | Configuration                          | Used for                                   |
|--------------------|----------------------------------------|--------------------------------------------|
| PHPUnit            | `phpunit.xml.dist`                     | Unit + Integration + Feature suites        |
| PHPStan            | `phpstan.neon.dist`                    | Static analysis (level 6 + baseline)       |
| Rector             | `rector.php`                           | Automated refactors / type-inference       |
| PHP-CS-Fixer       | `.php-cs-fixer.dist.php`               | Code style enforcement                     |
| Infection          | `infection.json5`                      | Mutation testing (MSI ≥ 70 / 80)           |
| Deptrac            | `deptrac.yaml`                         | Architectural fitness rules per layer      |
| Renovate           | `renovate.json`                        | Dependency automation                      |

GitHub Actions workflows live in `.github/workflows/` (`ci.yml`,
`integration.yml`, `release.yml`).

## Workflow

1. Create a feature branch from `main`.
2. Keep commits focused. Use [Conventional Commits](https://www.conventionalcommits.org/)
   prefixes: `feat:`, `fix:`, `chore:`, `docs:`, `test:`, `refactor:`,
   `perf:`, `ci:`, `build:`. The release workflow uses commit messages to
   auto-generate the changelog.
3. Add or update tests for any behavioural change. Aim for ≥ 85% coverage
   on new code. Mutation testing must keep MSI ≥ 70 (covered MSI ≥ 80).
4. Run `composer ci` locally before opening the PR.
5. Update `CHANGELOG.md` under the `Unreleased` section.

## Architectural rules

The codebase enforces layering with Deptrac (`deptrac.yaml`). New code must
respect those rules; if a dependency is genuinely required, update the
ruleset and explain why in the PR.

A tour of the architecture is in
[`docs/architecture.md`](docs/architecture.md).

## Coding standards

- PHP 8.2+, `declare(strict_types=1)` in every file.
- PSR-12 enforced via `php-cs-fixer`, including risky migration rules
  (`@PHP82Migration:risky`, `@PSR12:risky`).
- Public APIs must carry full PHPDoc with parameter types, return type,
  thrown exceptions, and at least one `@example` for non-trivial methods.
- All new code should pass PHPStan at the configured level. Add a baseline
  entry only when an issue genuinely cannot be addressed in the change;
  mention the reason in the PR description.
- Prefer immutable readonly value objects for new types. Use enums for
  closed sets of options.

## Reporting bugs

Please use [GitHub issues](https://github.com/daycry/phpunit-extension-selenium/issues)
and include:

- PHP, PHPUnit and `php-webdriver` versions.
- Browser and Selenium server / Grid version.
- A minimal `phpunit.xml` snippet and reproduction test.
- Environment variables that override the default configuration.

## Security issues

See [`SECURITY.md`](SECURITY.md). Do not open public issues for security
vulnerabilities.

## License

By contributing you agree that your contributions are licensed under the
project [MIT license](LICENSE).

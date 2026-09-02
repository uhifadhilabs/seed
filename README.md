# uhifadhi/seed

The project template every uhifadhi installation grows from.

## The tree

Uhifadhi is structured like the thing it protects:

> **`uhifadhi/seed`** (planted once) → **`uhifadhi/trunk-module`** (the seam
> runtime every module registers with) → **branches** (the modules: patrol,
> incident, roster, map, team, widget, area…) → **`uhifadhi/canopy-module`**
> (the visible crown).

**This repository is the seed** — and only the seed. It is copied once, by
`composer create-project`, and then it is yours; it is never updated again.
Everything above it is a bundle, updated forever through composer.

That is the whole design constraint here: a line added to the seed is a line
every future installation is stuck with, so the seed stays boring on purpose.
It is a bare Symfony kernel plus the one thing that lets it grow — the
uhifadhilabs Flex recipe endpoint, which auto-wires a module bundle when you
install it.

## What is in it

- A minimal Symfony 8.1 kernel (`src/Kernel.php`, `public/index.php`,
  `bin/console`, `config/`) — the `symfony/skeleton` shape, pruned.
- The uhifadhilabs recipe endpoint, in `extra.symfony.endpoint`.
- A production `Dockerfile` (FrankenPHP) — the deploy shape ships with the seed
  because every installation needs one on day one.
- One smoke test: the kernel boots and a request completes.

## What is deliberately not in it

No authentication, no UI, theme or asset pipeline, no Doctrine, no entities or
fixtures, no modules, no deploy configs beyond the `Dockerfile`. None of that is
missing — each arrives later as its own ring of the tree, on its own release
cadence. Doctrine in particular is not a seed dependency: it arrives with the
first entity-bearing module, through that module's recipe.

## Plant it

```bash
composer create-project uhifadhi/seed my-installation
cd my-installation
composer test          # the smoke test: it boots
php -S localhost:8000 -t public
```

Visiting `/` on a fresh installation returns **404** — the seed ships no routes,
and in debug Symfony renders its own welcome page on that 404. That is the seed
working correctly, not a fault: routes arrive with the bundles you add.

## Grow it

Installing a module bundle is the whole extension mechanism. Because the recipe
endpoint is configured, composer wires the bundle up for you — registration,
config, routes:

```bash
composer require uhifadhi/<name>-module
```

**Status, honestly:** the trunk and canopy bundles named in the tree above do not
exist as published packages yet — the module seam currently lives inside the
[uhifadhi](https://github.com/uhifadhilabs/uhifadhi) host application, and is
being extracted. Until that extraction lands, a freshly planted seed is a bare
Symfony kernel and nothing more. This README will grow a real list of installable
rings as they are published; it will not list them before they are.

## Licence

**AGPL-3.0-or-later** — see [LICENSE](LICENSE). Use, modify and self-host freely;
if you offer a modified uhifadhi to users over a network, they are entitled to the
source of what they're running. Science is never paywalled.

i# uhifadhi/uhifadhi

The project template every uhifadhi installation grows from — the seed.

## The tree

Uhifadhi is structured like the thing it protects:

> **the seed — `uhifadhi/uhifadhi`** (planted once) → **`uhifadhi/trunk-module`** (the seam
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

The package is not on Packagist yet, so point composer at this repository until
it is:

```bash
composer create-project uhifadhi/uhifadhi my-installation dev-main \
  --repository='{"type":"vcs","url":"https://github.com/uhifadhilabs/uhifadhi"}' \
  --stability=dev
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

**Status, honestly:** the trunk is grown — a planted project installs
`uhifadhi/trunk-module` (the seam runtime and module catalogue) with its recipe,
boots, and reports an honestly empty catalogue. The canopy (layout and theme) is
being extracted next; module bundles join ring by ring, each proven by a real
`create-project` before the next begins. The
[uhifadhi host](https://github.com/uhifadhilabs/uhifadhi-host) remains the
reference application until the tree reaches parity.

## Licence

**AGPL-3.0-or-later** — see [LICENSE](LICENSE). Use, modify and self-host freely;
if you offer a modified uhifadhi to users over a network, they are entitled to the
source of what they're running. Science is never paywalled.

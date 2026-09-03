# uhifadhi/uhifadhi

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

No authentication, no UI, theme or asset pipeline, no entities or fixtures, no
modules, no deploy configs beyond the `Dockerfile`. None of that is missing —
each arrives later as its own ring of the tree, on its own release cadence.

Doctrine is the honest exception, and it is worth being precise about. It is
not a *direct* dependency of the seed: `composer.json` does not require it.
It is here because the trunk requires it, and the trunk requires it because the
trunk owns tables — so a planted project has doctrine-bundle, the ORM and
doctrine-migrations-bundle, and the two config files their recipes wrote. That
is the rule working, not an exception to it: capability arrives through a ring,
and the ring that adds tables brings what creates them. The seed's job is only
to have its `doctrine.yaml` name the right namespace when it does.

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

### Give it a database

The trunk is the seed's one ring, and it owns two tables — the catalogue and
the per-area install record. Point `DATABASE_URL` at a database in
`.env.local`:

```dotenv
DATABASE_URL="postgresql://app:app@127.0.0.1:5432/my_installation?serverVersion=17&charset=utf8"
```

Then **write your area entity before you touch the schema.** The trunk maps its
per-area row to an interface and you resolve it to your own class; that is not
an optional extra, because the association is `NOT NULL` and until it resolves
there is no schema to create at all:

```console
$ bin/console doctrine:migrations:diff
In MappingException.php line 72:
  Class 'Uhifadhi\Trunk\Entity\AreaInterface' does not exist
```

So, once — the whole of it:

```php
// src/Entity/AreaOfInterest.php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Uhifadhi\Trunk\Entity\AreaInterface;

#[ORM\Entity]
class AreaOfInterest implements AreaInterface
{
    #[ORM\Id] #[ORM\GeneratedValue] #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
```

and uncomment the block `config/packages/trunk.yaml` ends with, naming it:

```yaml
doctrine:
    orm:
        resolve_target_entities:
            Uhifadhi\Trunk\Entity\AreaInterface: App\Entity\AreaOfInterest
```

`App\Entity` is this project's root — the seed is a stock Symfony application,
and `Uhifadhi\` belongs to the platform's own packages (`Uhifadhi\Trunk\` on the
left-hand side above is the bundle's, not yours). So the mapping prefix the
stock doctrine-bundle recipe writes into `config/packages/doctrine.yaml` already
covers the entity, and the comment there says why the seed keeps the block
rather than leaving it to the recipe.

Then:

```bash
bin/console doctrine:database:create
bin/console doctrine:migrations:diff      # your history, your migration
bin/console doctrine:migrations:migrate
bin/console trunk:catalogue:seed
```

The trunk brings `doctrine/doctrine-migrations-bundle` with it — the ring that
adds tables brings the tool that creates them — but ships no migration versions
of its own: the tables are the bundle's, the history is yours.

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

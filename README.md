# uhifadhi/uhifadhi

The project skeleton every uhifadhi installation starts from.

## The architecture

**Uhifadhi is one skeleton and a set of modules.** The skeleton
(`uhifadhi/uhifadhi` — this repository) is copied once by
`composer create-project` and then it is yours; it is never updated again.
Everything else arrives as a module, updated forever through composer. A module
**registers with the seam** (`uhifadhi/seam-module`) and **renders in the shell**
(`uhifadhi/shell-module`); everything a deployment can do — patrols, incidents,
rosters — is a module.

That is the whole design constraint here: a line added to the skeleton is a line
every future installation is stuck with, so it stays boring on purpose. It is a
bare Symfony kernel, the seam, the shell, and the one thing that lets it grow —
the uhifadhilabs Flex recipe endpoint, which auto-wires a module when you
install it.

## What is in it

- A minimal Symfony 8.1 kernel (`src/Kernel.php`, `public/index.php`,
  `bin/console`, `config/`) — the `symfony/skeleton` shape, pruned.
- The seam (`uhifadhi/seam-module`) and the shell (`uhifadhi/shell-module`):
  the module catalogue every module registers with, and the page frames, theme
  and navigation seams every module renders into.
- The uhifadhilabs recipe endpoint, in `extra.symfony.endpoint`.
- A production `Dockerfile` (FrankenPHP) — the deploy shape ships with the
  skeleton because every installation needs one on day one.
- One smoke test: the kernel boots and a request completes.

## What is deliberately not in it

No authentication, no asset pipeline, no entities or fixtures, no modules, no
deploy configs beyond the `Dockerfile`. None of that is missing — each arrives
later as its own bundle, on its own release cadence.

Doctrine is the honest exception, and it is worth being precise about. It is
not a *direct* dependency of the skeleton: `composer.json` does not require it.
It is here because the seam requires it, and the seam requires it because the
seam owns tables — so a new project has doctrine-bundle, the ORM and
doctrine-migrations-bundle, and the two config files their recipes wrote. That
is the rule working, not an exception to it: capability arrives through a
bundle, and the bundle that adds tables brings what creates them. The skeleton's
job is only to have its `doctrine.yaml` name the right namespace when it does.

## Start it

```bash
composer create-project uhifadhi/uhifadhi my-installation
cd my-installation
composer test          # the smoke test: it boots
php -S localhost:8000 -t public
```

It serves on the first request: a branded, themed, navigable shell with an empty
sidebar, from the shell (`uhifadhi/shell-module`). There is no user, no security
bundle and no module yet, and none of that is a placeholder — it is what an
installation with nothing in it honestly looks like. Your first page extends one
of the shell's three frames and fills one block:

```twig
{# templates/home/index.html.twig #}
{% extends '@UhifadhiShell/page.html.twig' %}

{% block shell_page_title %}Nothing installed yet{% endblock %}

{% block shell_page %}
    <p>The first page of a new installation.</p>
{% endblock %}
```


### Give it a database

The seam (`uhifadhi/seam-module`) owns two tables — the catalogue and
the per-area install record. Point `DATABASE_URL` at a database in
`.env.local`:

```dotenv
# .env.local
DATABASE_URL="postgresql://app:app@127.0.0.1:5432/my_installation?serverVersion=17&charset=utf8"
```

Then **write your area entity before you touch the schema.** The seam maps its
per-area row to an interface and you resolve it to your own class; that is not
an optional extra, because the association is `NOT NULL` and until it resolves
there is no schema to create at all:

```console
$ bin/console doctrine:migrations:diff
In MappingException.php line 72:
  Class 'Uhifadhi\Seam\Entity\AreaInterface' does not exist
```

So, once — the whole of it:

```php
// src/Entity/AreaOfInterest.php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Uhifadhi\Seam\Entity\AreaInterface;

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

and uncomment the block `config/packages/seam.yaml` ends with, naming it:

```yaml
# config/packages/seam.yaml
doctrine:
    orm:
        resolve_target_entities:
            Uhifadhi\Seam\Entity\AreaInterface: App\Entity\AreaOfInterest
```

`App\Entity` is this project's root — the skeleton is a stock Symfony application,
and `Uhifadhi\` belongs to the platform's own packages (`Uhifadhi\Seam\` on the
left-hand side above is the bundle's, not yours). So the mapping prefix the
stock doctrine-bundle recipe writes into `config/packages/doctrine.yaml` already
covers the entity, and the comment there says why the skeleton keeps the block
rather than leaving it to the recipe.

Then:

```bash
bin/console doctrine:database:create
bin/console doctrine:migrations:diff      # your history, your migration
bin/console doctrine:migrations:migrate
bin/console seam:catalogue:seed
```

The seam brings `doctrine/doctrine-migrations-bundle` with it — the bundle that
adds tables brings the tool that creates them — but ships no migration versions
of its own: the tables are the bundle's, the history is yours.

## Grow it

Installing a module is the whole extension mechanism. Because the recipe
endpoint is configured, composer wires the bundle up for you — registration,
config, routes:

```bash
composer require uhifadhi/<name>-module
```

**Status, honestly:** the seam and the shell are both here. A new project
installs `uhifadhi/seam-module` and `uhifadhi/shell-module` with their recipes,
boots, and serves a welcome page at `/` — a branded, navigable shell that says
what the two installed packages are and what installing a module does.

That page, its controller and its route are all the shell's — but the address is
this application's, because `config/routes/shell.yaml` is one line of consent:

```yaml
shell:
    resource: '@UhifadhiShellBundle/config/routes/welcome.php'
```

The shell loads that resource nowhere; the import is what makes `/` answer. Edit
the file to point `/` at your own home screen, or delete it and the address is
yours again — nothing is left behind. `debug:router` shows what you are
replacing: a route named `welcome`.

**What is not here yet is capability.** Until a module is installed, an
installation is the seam, the shell and that one page — which is a working
installation, not a half-finished one. Modules join one at a time, each proven by
a real `create-project` before the next begins.

## Licence

**AGPL-3.0-or-later** — see [LICENSE](LICENSE). Use, modify and self-host freely;
if you offer a modified uhifadhi to users over a network, they are entitled to the
source of what they're running. Science is never paywalled.

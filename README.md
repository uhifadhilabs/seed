# uhifadhi/uhifadhi

The project skeleton every uhifadhi installation starts from.

## What it is

A bare Symfony kernel, the seam (`uhifadhi/seam-module`), the shell
(`uhifadhi/shell-module`) and the uhifadhilabs Flex recipe endpoint. It is
copied once by `composer create-project` and then it is yours; every capability
after that arrives as a module, installed with composer.

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
installation with nothing in it honestly looks like. `/` is open here in the
plainest sense: there is no firewall in this project to close it. Installing
identity (`uhifadhi/team-module`) is what changes that — its documented
`security.yaml` is default-closed, and from then on a visitor who is not signed
in is sent to `/login` from `/` and from everywhere else. Your first page extends one
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

Then **give it an area.** The seam maps its per-area row to an interface, and
until that interface resolves to a class there is no schema to create at all —
the association is `NOT NULL`, so every metadata walk stops:

```console
$ bin/console doctrine:migrations:diff
In MappingException.php line 72:
  Class 'Uhifadhi\Seam\Entity\AreaInterface' does not exist
```

**Whoever knows the answer states the resolution**, and for an area that is
`uhifadhi/area-module`:

```bash
composer require uhifadhi/area-module
```

It brings a real area — a name, a gazetted MultiPolygon boundary, a public uuid —
maps its own entity and prepends the resolution, the same way
`uhifadhi/team-module` answers the user contract. **You write no `doctrine.yaml`
line.** With both answer-modules installed, this project reaches
`doctrine:migrations:diff` with zero doctrine edits.

The area's boundary is a PostGIS column, so the database needs the extension
once:

```sql
CREATE EXTENSION IF NOT EXISTS postgis;
```

This used to be a hand-step: write your own `App\Entity\AreaOfInterest`, then
uncomment a block in `config/packages/seam.yaml`. It is gone, and the block with
it. **You write a `resolve_target_entities` line only to disagree** — an
installation whose areas are its own entity names that class in
`config/packages/doctrine.yaml` and wins, because prepended configuration loses
to the application's.

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

## Learn more

- [The architecture](docs/architecture.md) — one skeleton and a set of modules,
  what is in this repository, and what is deliberately not (including why
  Doctrine is here at all).
- [Maintaining the skeleton](docs/maintaining-the-skeleton.md) — `symfony.lock`
  as the recipe ledger, and the re-sync rule for the two recipes the skeleton
  ships with.

## Licence

**AGPL-3.0-or-later** — see [LICENSE](LICENSE). Use, modify and self-host freely;
if you offer a modified uhifadhi to users over a network, they are entitled to the
source of what they're running. Science is never paywalled.

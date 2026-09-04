# Maintaining the skeleton

`symfony.lock` is this repository's recipe ledger: for every installed package
it records which recipe version was applied, its hash and the files it owns.
The two recipes the skeleton ships with — `uhifadhi/seam-module` and
`uhifadhi/shell-module` — therefore have a rule: **change the recipe, re-sync
the ledger.**

```bash
composer recipes:update uhifadhi/seam-module
```

That goes for editing a recipe's bytes in the
[recipes](https://github.com/uhifadhilabs/recipes) repository, adding a recipe
version, and hand-editing a recipe-owned file here. Without it, `composer
recipes` reports "update available" with nothing to apply — on every fresh
installation, because the hash it compares no longer matches.

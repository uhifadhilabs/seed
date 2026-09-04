# Maintaining the skeleton

## The version rhythm

**A module ring's minor is the skeleton's minor.** When a bundle in the fleet
takes a minor — the shell absorbing the component vocabulary in 0.6, say — the
skeleton that raises its constraint to match takes one too, whether or not a
single line of the skeleton's own source moved. The skeleton's version is not a
count of its own edits; it is the name of the installation a
`create-project` produces, and that installation is a different one.

A patch here says "the same installation, fixed". Raising a constraint to a
minor never means that.

## The recipe ledger

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

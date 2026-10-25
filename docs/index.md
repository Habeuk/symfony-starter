# Documentation — NutriGuide

Index général de la documentation de développement du projet **NutriGuide**.

Ce dossier (`rootapp/docs/`) est la source de vérité pour le suivi du développement :
règles de code, documentation des fonctionnalités et documentation des tests.
Il s'adresse aux développeurs **et** à l'agent IA, qui doivent s'y référer avant de coder.

---

## 1. Par où commencer

| Ordre | Document | Rôle |
| :---: | --- | --- |
| 1 | [CONVENTIONS.md](CONVENTIONS.md) | Règles de code, conventions, obligations documentaires. **Lecture obligatoire avant toute modification.** |
| 2 | Référentiel fonctionnel (voir § 2) | Objectifs, vision et périmètre du produit. |
| 3 | [entites-module-01.md](entites-module-01.md) | Modèle de données du module 01 — **proposition à valider avant tout codage d'entité**. |
| 4 | `docs/features/<Domaine>/` (voir § 3) | Fiches techniques des fonctionnalités livrées. |
| 5 | `docs/tests/<Domaine>/<Type>/` (voir § 4) | Fiches de description des tests. |

---

## 2. Référentiel fonctionnel

> Emplacement : `/siteweb/nutribeguide/docs/fonctionnal/` — **hors** de `rootapp/`, en lecture seule.
> Chemin relatif depuis ce fichier : `../../docs/fonctionnal/`

- [Cahier des charges](../../docs/fonctionnal/cahier-des-charges.md)
- [Synthèse exécutive](../../docs/fonctionnal/synthese-executive.md)

### Modules fonctionnels

- [Module 01 — Scan calories](../../docs/fonctionnal/module-01-scan-calories.md)
- [Module 02 — Frigo](../../docs/fonctionnal/module-02-frigo.md)
- [Module 03 — Plan de la semaine](../../docs/fonctionnal/module-03-plan-semaine.md)
- [Module 04 — Budget](../../docs/fonctionnal/module-04-budget.md)
- [Module 05 — Santé](../../docs/fonctionnal/module-05-sante.md)
- [Module 06 — Famille](../../docs/fonctionnal/module-06-famille.md)
- [Module 07 — Communauté](../../docs/fonctionnal/module-07-communaute.md)

---

## 3. Documentation des fonctionnalités

> Emplacement : `docs/features/` — structure cible, **à créer au fil des livraisons**.

### Arborescence

```text
docs/features/
├── _TEMPLATE.md                  # Modèle de fiche technique
└── <Domaine>/
    ├── index.md                  # Index des fonctionnalités du domaine
    ├── CHANGELOG.md              # Historique des évolutions du domaine
    └── <sous-fonctionnalité>.md  # Fiche technique détaillée
```

### Convention

- Une fiche par sous-fonctionnalité, créée à partir de `docs/features/_TEMPLATE.md`.
- Un dossier `features/<Domaine>/` par domaine métier (ex. `User`, `Product`, `Nutrition`…).
- Chaque domaine possède son propre `index.md` et son propre `CHANGELOG.md`.

*Domaines documentés :* aucun pour le moment.

### Modèle de données

> Emplacement : `docs/` — un document par module fonctionnel, listant les entités **avant** leur codage.

| Document | Module | Statut |
| --- | :---: | --- |
| [entites-module-01.md](entites-module-01.md) | 01 — Scan & Calories | Proposition à valider |

---

## 4. Documentation des tests

> Emplacement : `docs/tests/` — structure cible, **à créer au fil des livraisons**.

### Arborescence

```text
docs/tests/
└── <Domaine>/
    └── <Type>/                          # Functional, Unit, Integration…
        └── <NomFichierTest>.md
```

### Correspondance fiche ↔ test

| Fiche de documentation | Fichier de test |
| --- | --- |
| `docs/tests/User/Functional/AnonymousPreInscriptionTest.md` | `tests/Functional/User/AnonymousPreInscriptionTest.php` |

> Le nom de la fiche reprend celui du fichier de test, **sans** l'extension `.php`.

---

## 5. Workflow d'une fonctionnalité

Toute fonctionnalité livrée implique **3 mises à jour documentaires obligatoires** :

1. **Fiche technique** — créer ou mettre à jour `docs/features/<Domaine>/<sous-fonctionnalité>.md`
   (modèle : `docs/features/_TEMPLATE.md`).
2. **Indexation** — mettre à jour `docs/features/<Domaine>/index.md`.
3. **Historique** — ajouter une entrée dans `docs/features/<Domaine>/CHANGELOG.md`,
   dans la section correspondante : `[Ajout]`, `[Modification]`, `[Correction]` ou `[Suppression]`.

Si la fonctionnalité est couverte par des tests, ajouter la fiche correspondante
dans `docs/tests/<Domaine>/<Type>/` (§ 4).

---

## 6. Structure actuelle du dossier

```text
rootapp/docs/
├── index.md                 # Ce fichier
├── CONVENTIONS.md           # Règles de code et conventions
└── entites-module-01.md     # Modèle de données du module 01 (proposition)
```

> Les dossiers `features/` et `tests/` seront créés lors de la première livraison concernée.

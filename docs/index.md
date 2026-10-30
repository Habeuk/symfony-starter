# Documentation du projet

Index général de la documentation de développement.

> **Documentation générique du starter.** Ce dossier est livré avec
> [`habeuk/symfony-starter`](../README.md) et ne doit contenir aucun contenu propre
> à un projet. À la création d'un projet, renseigner `<Projet>` ci-dessous et
> remplacer les éléments entre chevrons (`<...>`) par les valeurs du projet.
> L'organisation, elle, reste la même pour tous les projets issus du starter.

**Projet :** `<Projet>` — à renseigner à la création du projet.

Ce dossier (`docs/`) est la source de vérité pour le suivi du développement :
règles de code, documentation des fonctionnalités et documentation des tests.
Il s'adresse aux développeurs **et** à l'agent IA, qui doivent s'y référer avant de coder.

---

## 1. Par où commencer

| Ordre | Document | Rôle |
| :---: | --- | --- |
| 1 | [CONVENTIONS.md](CONVENTIONS.md) | Règles de code, conventions, obligations documentaires. **Lecture obligatoire avant toute modification.** |
| 2 | `docs/fonctionnel/` (voir § 2) | Objectifs, vision et périmètre du produit. |
| 3 | `docs/entites/` (voir § 3) | Modèle de données, **proposé et validé avant tout codage d'entité**. |
| 4 | `docs/features/<Domaine>/` (voir § 4) | Fiches techniques des fonctionnalités livrées. |
| 5 | `docs/tests/<Domaine>/<Type>/` (voir § 5) | Fiches de description des tests. |

---

## 2. Référentiel fonctionnel

> Emplacement conventionnel : `docs/fonctionnel/` — **à créer lors de la première
> livraison fonctionnelle**. Si le référentiel est maintenu hors du dépôt applicatif,
> remplacer ce paragraphe par son chemin.

Contenu attendu :

- un cahier des charges et une synthèse exécutive ;
- un document par module fonctionnel (`module-<NN>-<nom>.md`).

*Documents présents :* aucun pour le moment.

---

## 3. Modèle de données

> Emplacement : `docs/entites/` — **un document par module fonctionnel**, listant les
> entités **avant** leur codage. Modèle : [`_TEMPLATE.md`](entites/_TEMPLATE.md).

Chaque document est une **proposition soumise à validation** : aucune entité n'est
codée avant validation. Il précise, pour chaque entité, les champs, les contraintes,
les relations et les décisions à trancher.

*Documents présents :* aucun pour le moment.

---

## 4. Documentation des fonctionnalités

> Emplacement : `docs/features/` — un dossier par domaine, **créé au fil des livraisons**.

### Arborescence

```text
docs/features/
├── _TEMPLATE.md                  # Modèle de fiche technique (fourni)
└── <Domaine>/
    ├── index.md                  # Index des fonctionnalités du domaine
    ├── CHANGELOG.md              # Historique des évolutions du domaine
    └── <sous-fonctionnalité>.md  # Fiche technique détaillée
```

### Convention

- Une fiche par sous-fonctionnalité, créée à partir de [`_TEMPLATE.md`](features/_TEMPLATE.md).
- Un dossier `features/<Domaine>/` par domaine métier (ex. `User`, `Invoice`, `Catalog`…).
- Chaque domaine possède son propre `index.md` et son propre `CHANGELOG.md`.

*Domaines documentés :* aucun pour le moment.

---

## 5. Documentation des tests

> Emplacement : `docs/tests/` — un dossier par domaine et par type de test, **créé au fil des livraisons**.

### Arborescence

```text
docs/tests/
├── _TEMPLATE.md                      # Modèle de fiche de test (fourni)
└── <Domaine>/
    └── <Type>/                       # Functional, Unit, Integration…
        └── <NomFichierTest>.md
```

### Correspondance fiche ↔ test

| Fiche de documentation | Fichier de test |
| --- | --- |
| `docs/tests/Invoice/Functional/CreateInvoiceTest.md` | `tests/Functional/Invoice/CreateInvoiceTest.php` |

> Le nom de la fiche reprend celui du fichier de test, **sans** l'extension `.php`.
> Modèle : [`_TEMPLATE.md`](tests/_TEMPLATE.md).

---

## 6. Workflow d'une fonctionnalité

Toute fonctionnalité livrée implique **3 mises à jour documentaires obligatoires** :

1. **Fiche technique** — créer ou mettre à jour `docs/features/<Domaine>/<sous-fonctionnalité>.md`
   (modèle : `docs/features/_TEMPLATE.md`).
2. **Indexation** — mettre à jour `docs/features/<Domaine>/index.md`.
3. **Historique** — ajouter une entrée dans `docs/features/<Domaine>/CHANGELOG.md`,
   dans la section correspondante : `[Ajout]`, `[Modification]`, `[Correction]` ou `[Suppression]`.

Si la fonctionnalité est couverte par des tests, ajouter la fiche correspondante
dans `docs/tests/<Domaine>/<Type>/` (§ 5).

---

## 7. Structure du dossier

```text
docs/
├── index.md                 # Ce fichier
├── CONVENTIONS.md           # Règles de code et conventions
├── entites/
│   └── _TEMPLATE.md         # Modèle de proposition de modèle de données
├── features/
│   └── _TEMPLATE.md         # Modèle de fiche technique
├── tests/
│   └── _TEMPLATE.md         # Modèle de fiche de test
└── fonctionnel/             # À créer : référentiel fonctionnel
```

> Les dossiers `features/<Domaine>/`, `tests/<Domaine>/` et `fonctionnel/` sont créés
> lors de la première livraison concernée.

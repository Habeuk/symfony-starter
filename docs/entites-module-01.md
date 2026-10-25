# Proposition de modèle de données — Module 01 « Scan & Calories »

> **Statut : proposition à valider.** Aucune entité ne sera codée avant validation de ce document.
> Version : 0.1 — Date : 13/10/2026
> Référentiel fonctionnel : [`docs/fonctionnal/module-01-scan-calories.md`](../../docs/fonctionnal/module-01-scan-calories.md) *(lecture seule)*
> Conventions applicables : [CONVENTIONS.md](CONVENTIONS.md)

---

## 1. Origine de chaque besoin

Chaque entité décrite ci-dessous découle d'une exigence explicite du module 01.

| Réf. | Exigence fonctionnelle | § du module |
| :---: | --- | :---: |
| E1 | Reconnaître un produit emballé par son code-barres | §3.1 |
| E2 | Afficher calories + nutriments principaux (protéines, glucides, lipides, fibres) | §3.1, §5.1 |
| E3 | Afficher les allergènes, signalés visuellement | §3.1, §5.2 |
| E4 | Afficher la composition et la liste des ingrédients | §3.1, §3.4 |
| E5 | Afficher le niveau de transformation (classification NOVA) | §3.1, §5.2 |
| E6 | Afficher le Nutri-Score (A à E) | §3.1, §5.2 |
| E7 | Afficher les informations détaillées : vitamines, minéraux | §3.4 |
| E8 | Afficher la quantité pour 100 g **et** pour une portion | §5.1 |
| E9 | Afficher les additifs présents | §5.2 |
| E10 | Afficher photo, marque, origine, catégorie | §3.4, §5.1 |
| E11 | Rechercher un produit par nom, parcourir par catégories | §3.3 |
| E12 | Analyser une photo d'assiette → estimation (aliments, portions, calories) | §3.2 |
| E13 | Marquer explicitement qu'un résultat est une **estimation** | §7.2 |
| E14 | Conserver l'historique des scans si l'utilisateur a un compte | §4.2 |
| E15 | Permettre le scan **sans compte** | §4.1 |
| E16 | Sauvegarder les favoris | §4.2 |
| E17 | Tracer la source des données (fiabilité, rien d'inventé) | §7.1 |
| E18 | Contribution de produits / correction, **modérée avant publication** | §7.4 |
| E19 | Les scans d'un utilisateur lui appartiennent (confidentialité) | §7.5 |

---

## 2. Vue d'ensemble des entités proposées

| # | Entité | Priorité | Réf. | Rôle en une phrase |
| :---: | --- | :---: | :--- | --- |
| 1 | `Product` | 🟢 Noyau | E1–E11, E17 | Le produit scanné ou recherché ; pivot de tout NutriGuide. |
| 2 | `FoodFamily` | 🟢 Noyau | E11 | Catégorie hiérarchique de navigation des produits. |
| 3 | `Brand` | 🟢 Noyau | E10 | La marque d'un produit (« Danone », « Bjorg »…). |
| 4 | `NutritionFact` | 🟢 Noyau | E2, E7, E8 | Les valeurs nutritionnelles d'un produit. |
| 5 | `ProductPortion` | 🟢 Noyau | E8 | Une portion usuelle d'un produit (ex. « 1 yaourt = 125 g »). |
| 6 | `Allergen` | 🟢 Noyau | E3 | Référentiel des allergènes (14 allergènes UE). |
| 7 | `Additive` | 🟢 Noyau | E9 | Référentiel des additifs alimentaires (E-xxx). |
| 8 | `Scan` | 🟢 Noyau | E12–E15, E19 | Journal d'un scan : code-barres **ou** photo d'assiette. |
| 9 | `Favorite` | 🟡 Secondaire | E16 | Produit mis en favori par un utilisateur. |
| 10 | `ProductContribution` | 🟡 Secondaire | E18 | Proposition d'ajout ou de correction, soumise à modération. |

**Relations entre entités**

```text
FoodFamily (auto-référencée : parent/enfants)
    └─1..n─ Product

Brand
    └─1..n─ Product

Product
    ├─1..1─ NutritionFact
    ├─1..n─ ProductPortion
    ├─1..2─ Habeuk\FileUploaderBundle\Entity\Files   (image principale + image secondaire)
    ├─n..n─ Allergen          (via product_allergen, + niveau : contient / trace)
    ├─n..n─ Additive          (via product_additive)
    ├─1..n─ Scan              (un produit peut être scanné N fois)
    ├─1..n─ Favorite
    └─1..n─ ProductContribution

Scan
    ├─0..1─ Habeuk\FileUploaderBundle\Entity\Files   (photo d'assiette)
    └─0..1─ Product

User
    ├─1..n─ Scan
    ├─1..n─ Favorite
    └─1..n─ ProductContribution  (proposant + modérateur)
```

---

## 3. Détail des entités

### 3.1 `Product` 🟢

Le produit emballé, identifié par son code-barres. C'est **la** table centrale du projet.

| Champ | Type | Contraintes | Justification |
| --- | --- | --- | --- |
| `id` | int | PK, auto | Convention projet |
| `barcode` | string(14) | **unique**, nullable | E1 — EAN-8 / EAN-13 / UPC-A. Nullable : un produit peut être créé sans code (contribution). |
| `name` | string(255) | not null, indexé | E1, E11 |
| `brand` | `Brand` | nullable, `ON DELETE SET NULL` | E10 — marque du produit (voir §3.3). |
| `foodFamily` | `FoodFamily` | nullable, `ON DELETE SET NULL` | E11 — catégorie de navigation (voir §3.2). |
| `quantityLabel` | string(50) | nullable | E10 — « 125 g », « 1 L » |
| `origin` | string(150) | nullable | E10 — pays / région d'origine |
| `documentFile` | `Habeuk\FileUploaderBundle\Entity\Files` | nullable, `ON DELETE SET NULL` | E10 — visuel principal du produit, géré par `habeuk/file-uploader-bundle` (Vich + Liip) |
| `documentFileSecond` | `Habeuk\FileUploaderBundle\Entity\Files` | nullable, `ON DELETE SET NULL` | E10 — visuel secondaire (face arrière, tableau nutritionnel) |
| `nutriScore` | `NutriScoreEnum` | nullable | E6 — valeurs `A`–`E`, `UNKNOWN`, `NOT_APPLICABLE` |
| `novaGroup` | smallint | nullable, entre 1 et 4 | E5 — classification NOVA |
| `ingredientsText` | text | nullable | E4 — liste brute telle qu'imprimée sur l'emballage |
| `ingredients` | **jsonb** | nullable | E4 — liste ordonnée structurée `[{position, label, percentage}]` |
| `allergensText` | text | nullable | E3 — mention « peut contenir… » non structurée |
| `datasource` | `DataSourceEnum` | not null | E17 — `OPEN_FOOD_FACTS`, `CIQUAL`, `MANUFACTURER`, `USER_CONTRIBUTION`, `INTERNAL` |
| `externalId` | string(100) | nullable | E17 — identifiant dans la source (ex. code Open Food Facts) |
| `isVerified` | bool | défaut `false` | E17, E18 — publié après modération |
| `isActive` | bool | défaut `true` | Permet de retirer un produit sans le supprimer |
| `contributedBy` | `User` | nullable, `ON DELETE SET NULL` | E18 — traçabilité de la contribution |
| `createdAt` / `updatedAt` | `timestamptz` | lifecycle callbacks | Convention projet |

**Index** : `barcode` (unique partiel `WHERE barcode IS NOT NULL`), `name` (GIN trigram pour la recherche §3.3), `brand_id`, `foodFamily_id`.

---

### 3.2 `FoodFamily` 🟢

Catégorie de navigation (§3.3 « parcourir une base de produits classés par catégories »).

| Champ | Type | Contraintes | Justification |
| --- | --- | --- | --- |
| `id` | int | PK | |
| `name` | string(150) | not null, indexé | Libellé affiché |
| `parent` | `FoodFamily` | nullable, auto-référence | Hiérarchie : `Aliments > Produits laitiers > Yaourts` |
| `path` | string(500) | indexé GIN | Chemin matérialisé `aliments/produits-laitiers/yaourts` : évite les requêtes récursives |
| `position` | smallint | défaut 0 | Ordre d'affichage |
| `icon` | string(50) | nullable | Icône PrimeIcons, cohérent avec `User` |

**Note** : `path` est dénormalisé volontairement (performance de lecture). Il est recalculé par un listener lors du changement de `parent` ou de `name`.

---

### 3.3 `Brand` 🟢

La marque d'un produit. Entité à part entière (et non une chaîne) : elle porte l'identité commerciale, sera un axe de recherche et de comparaison, et évite les approximations de saisie (« Danone », « danone », « DANONE »).

| Champ | Type | Contraintes | Justification |
| --- | --- | --- | --- |
| `id` | int | PK | |
| `name` | string(150) | not null, unique | E10 — « Danone », « Bjorg » |
| `logo` | `Habeuk\FileUploaderBundle\Entity\Files` | nullable, `ON DELETE SET NULL` | E10 — logo de la marque |
| `description` | text | nullable | Présentation courte, affichée sur la fiche marque |
| `datasource` | `DataSourceEnum` | not null | E17 — même traçabilité que `Product` |
| `externalId` | string(100) | nullable | E17 — identifiant dans la source |
| `isActive` | bool | défaut `true` | Permet de retirer une marque sans la supprimer |

**Note** : la contrainte unique porte sur `name` ; la normalisation (casse, accents) se fait à l'écriture, pas à la lecture.

---

### 3.4 `NutritionFact` 🟢

Valeurs nutritionnelles, isolées de `Product` pour séparer l'identité du produit de ses mesures.

| Champ | Type | Unité | Justification |
| --- | --- | --- | --- |
| `id` | int | | |
| `product` | `Product` | 1-1, unique | |
| `energyKcal` | decimal(8,2) | kcal / 100 g | E2 |
| `energyKj` | decimal(8,2) | kJ / 100 g | Mention légale UE |
| `fat` | decimal(8,2) | g / 100 g | E2 |
| `saturatedFat` | decimal(8,2) | g / 100 g | E2, module Santé |
| `carbohydrates` | decimal(8,2) | g / 100 g | E2 |
| `sugars` | decimal(8,2) | g / 100 g | E2, module Santé (diabète) |
| `fibers` | decimal(8,2) | g / 100 g | E2 |
| `proteins` | decimal(8,2) | g / 100 g | E2 |
| `salt` | decimal(8,3) | g / 100 g | E2 |
| `sodium` | decimal(8,3) | g / 100 g | E2 |
| `micronutrients` | **jsonb** | | E7 — vitamines/minéraux : `{"vitamine_c": 12.5, "calcium": 120, ...}` |
| `updatedAt` | `timestamptz` | | |

> **Décision D2** : les macro-nutriments sont en colonnes typées (lisibles et interrogeables par le module Santé), les micro-nutriments en `jsonb` (liste ouverte, pas de schéma figé).

---

### 3.5 `ProductPortion` 🟢

Exigence E8 : les quantités doivent être affichées **pour 100 g et pour une portion**.

| Champ | Type | Contraintes | Justification |
| --- | --- | --- | --- |
| `id` | int | PK | |
| `product` | `Product` | not null | |
| `label` | string(100) | not null | « 1 pot », « 1 tranche », « 1 verre » |
| `weightGrams` | decimal(8,2) | not null | Poids de la portion, sert au calcul |
| `isDefault` | bool | défaut `false` | Portion mise en avant sur la fiche |
| `position` | smallint | défaut 0 | Ordre d'affichage |

**Contrainte** : une seule portion `isDefault = true` par produit (index unique partiel).

---

### 3.6 `Allergen` 🟢

Référentiel, pas une donnée produit. Les 14 allergènes à déclaration obligatoire (règlement UE 1169/2011).

| Champ | Type | Contraintes | Justification |
| --- | --- | --- | --- |
| `id` | int | PK | |
| `code` | string(30) | unique | `GLUTEN`, `CRUSTACES`, `OEUF`, `POISSON`, `ARACHIDE`, `SOJA`, `LAIT`, `FRUITS_A_COQUE`, `CELERI`, `MOUTARDE`, `SESAME`, `SULFITES`, `LUPIN`, `MOLLUSQUES` |
| `labelFr` | string(100) | not null | Affichage en français (CONVENTIONS §5) |
| `icon` | string(50) | nullable | E3 — signalement visuel |
| `description` | text | nullable | Aide à la compréhension |

**Table pivot `product_allergen`** :

| Colonne | Type | Justification |
| --- | --- | --- |
| `product_id` | FK | |
| `allergen_id` | FK | |
| `level` | `AllergenLevelEnum` | E3 — `CONTAINS` (contient) ou `MAY_CONTAIN` (traces éventuelles) |

---

### 3.7 `Additive` 🟢

Référentiel des additifs (E9).

| Champ | Type | Contraintes | Justification |
| --- | --- | --- | --- |
| `id` | int | PK | |
| `code` | string(10) | unique | `E100`, `E951`… |
| `nameFr` | string(150) | not null | « Diacétate de sodium » |
| `riskLevel` | `AdditiveRiskEnum` | not null, défaut `UNKNOWN` | `LOW`, `MODERATE`, `HIGH`, `UNKNOWN` — information, jamais de jugement (§7.3) |
| `description` | text | nullable | Rôle de l'additif |

**Table pivot `product_additive`** : `product_id`, `additive_id`.

---

### 3.8 `Scan` 🟢

Journal des scans. Couvre **deux natures de scan** : produit (code-barres) et assiette (photo) — voir décision D4.

| Champ | Type | Contraintes | Justification |
| --- | --- | --- | --- |
| `id` | int | PK | |
| `user` | `User` | **nullable**, `ON DELETE CASCADE` | E15 — scan sans compte ; E19 — le scan suit son propriétaire |
| `anonymousToken` | string(64) | nullable, indexé | E15 — permet la limite de scans/jour sans compte (§4.1) |
| `type` | `ScanTypeEnum` | not null | `BARCODE` ou `MEAL_PHOTO` |
| `product` | `Product` | nullable | Renseigné si `type = BARCODE` et produit reconnu |
| `barcode` | string(14) | nullable | Conservé même si le produit est inconnu (§8.2 : un scan enrichit la base) |
| `photoFile` | `Habeuk\FileUploaderBundle\Entity\Files` | nullable, `ON DELETE SET NULL` | E12 — photo de l'assiette, gérée par `habeuk/file-uploader-bundle` (upload Vich + variantes Liip) |
| `estimatedFoods` | **jsonb** | nullable | E12 — aliments détectés : `[{label, portionGrams, confidence}]` |
| `estimatedKcal` | decimal(8,2) | nullable | E12 — calories estimées totales |
| `estimatedNutrients` | **jsonb** | nullable | E12 — protéines/glucides/lipides/fibres estimés |
| `confidence` | decimal(4,3) | nullable | E13 — niveau de confiance (0 à 1) |
| `isEstimation` | bool | défaut `false` | **E13 — imposé par la règle de transparence §7.2 : une estimation est toujours signalée** |
| `scannedAt` | `timestamptz` | not null, indexé | E14 — historique chronologique |

**Index** : `(user_id, scannedAt DESC)` pour l'historique ; `(anonymousToken, scannedAt)` pour le quota anonyme.

**Règle de visibilité du fichier** : la photo d'assiette est une **donnée privée** (§7.5 « les scans d'un utilisateur lui appartiennent »). Le `Files` associé est donc stocké en **`privateFile`** (`privateAccess = true`), servi par une route protégée et non depuis `/uploads/public`. Voir le tableau de la §4 D12.

---

### 3.9 `Favorite` 🟡

| Champ | Type | Contraintes | Justification |
| --- | --- | --- | --- |
| `id` | int | PK | |
| `user` | `User` | not null, `ON DELETE CASCADE` | E16 |
| `product` | `Product` | not null, `ON DELETE CASCADE` | E16 |
| `createdAt` | `timestamptz` | lifecycle callback | |

**Contrainte** : `UNIQUE(user_id, product_id)` — pas de doublon.

> **Note** : entité dédiée plutôt que relation `ManyToMany` simple, car des champs métier viendront s'y greffer (note personnelle, date de dernier scan) et le module Communauté l'utilisera.

---

### 3.10 `ProductContribution` 🟡

E18 — « Les utilisateurs peuvent proposer l'ajout de nouveaux produits ou la correction d'informations. Ces contributions sont modérées avant publication. »

| Champ | Type | Contraintes | Justification |
| --- | --- | --- | --- |
| `id` | int | PK | |
| `type` | `ContributionTypeEnum` | not null | `NEW_PRODUCT` ou `CORRECTION` |
| `product` | `Product` | nullable | `null` si `NEW_PRODUCT`, renseigné si `CORRECTION` |
| `barcode` | string(14) | nullable | Code-barres proposé |
| `payload` | **jsonb** | not null | Données proposées (structurellement proche de `Product`) |
| `documentFiles` | `Habeuk\FileUploaderBundle\Entity\Files` | `OneToMany`, vide par défaut | E18 — photos du produit proposé (emballage, tableau nutritionnel), conservées le temps de la modération puis rattachées au `Product` créé. **Toujours privées** (D12) |
| `status` | `ContributionStatusEnum` | not null, défaut `PENDING` | `PENDING`, `APPROVED`, `REJECTED` |
| `submittedBy` | `User` | not null | Proposant |
| `submittedAt` | `timestamptz` | not null | |
| `moderatedBy` | `User` | nullable | Modérateur |
| `moderatedAt` | `timestamptz` | nullable | |
| `moderationComment` | text | nullable | Motif du refus, message au contributeur |

---

## 4. Décisions à trancher

| Réf. | Question | Ma recommandation |
| :---: | --- | --- |
| **D1** | ~~`brand` en chaîne sur `Product` ou entité `Brand` à part ?~~ | ✅ **Tranché : entité `Brand`** (voir §3.3). Permet la recherche par marque, la comparaison et le logo. |
| **D2** | Macro-nutriments en colonnes typées + micros en `jsonb` ? | Oui. Les modules Santé/Budget interrogeront les macros (indexables), les micros sont une liste ouverte. |
| **D3** | Liste des ingrédients normalisée (entité) ou `jsonb` ? | `jsonb` pour le module 01 (`ingredientsText` pour l'affichage brut). Normaliser seulement si le module Santé doit filtrer par ingrédient. |
| **D4** | Un seul `Scan` polymorphe (code-barres + photo) ou entités séparées ? | Un seul `Scan` avec `type` : l'historique (§4.2) et la confidentialité (§7.5) s'appliquent à l'identique aux deux. |
| **D5** | `Scan.user` nullable + `anonymousToken` ? | Oui. Indispensable pour concilier E15 (scan sans compte) et le quota de scans/jour (§4.1). |
| **D6** | Clé primaire `int` auto-incrémentée ou `uuid` ? | `int` pour rester aligné sur `User`. À basculer en `uuid` plus tard si l'export RGPD (§8.4) l'exige. |
| **D7** | `isVerified` sur `Product` : modération obligatoire pour tout produit, ou seulement pour les contributions ? | Seulement les contributions. Un import Open Food Facts/Ciqual arrive vérifié. |
| **D8** | Recherche produit (§3.3, < 1 s) : `ILIKE` + index trigram, ou extension `pg_trgm`/plein-texte `tsvector` ? | Index GIN `pg_trgm` au départ ; `tsvector` si le volume l'exige. |
| **D9** | Séparation `NutritionFact` / `Product` validée ? | Oui (table 1-1). |
| **D10** | ~~Stockage des images et fichiers ?~~ | ✅ **Tranché : `Habeuk\FileUploaderBundle\Entity\Files`** (voir §3.1 et §3.8), en relations `ManyToOne`. Aucun champ `imageUrl` ni `photoPath` en chaîne dans le modèle. |

> **Décision D11 — pas de `slug`** : l'application n'est pas publique à ce stade, donc aucune URL lisible n'est nécessaire. Les champs `slug` sont retirés du modèle (`Product`, `FoodFamily`). Le sujet sera rouvert si l'application devient publique : voir le bundle `habeuk/url-alias-bundle`, qui gère déjà les alias d'URL par entité.

> **Décision D12 — visibilité des fichiers** : `Files` porte deux mappings Vich (`publicFile` / `privateFile`) et un indicateur `privateAccess`. Le choix est fixé par nature de donnée :

| Usage | Champ porteur | Mapping Vich | `privateAccess` | Motif |
| --- | --- | --- | --- | --- |
| Visuel produit | `Product.documentFile`, `Product.documentFileSecond` | `publicFile` | `false` | Information publique, destinée à la fiche produit |
| Logo de marque | `Brand.logo` | `publicFile` | `false` | Information publique |
| Photo d'assiette | `Scan.photoFile` | `privateFile` | `true` | Donnée personnelle (§7.5) : le scan appartient à son auteur |
| Fichiers des contributions | `ProductContribution` | `privateFile` | `true` | Modéré avant publication (§7.4) |

**Règle** : **toute donnée rattachée à un utilisateur est privée par défaut.** Elle ne devient publique que par une décision explicite du propriétaire (§7.5 : « jamais partagés sans son accord explicite ») — et cette transition devra être tracée.

---

## 5. Ce qui n'est pas dans le module 01

Volontairement hors périmètre, pour éviter d'anticiper sur les modules suivants :

- **Entités des autres modules** : `Recipe`, `FridgeItem`, `Price`, `HealthProfile`, `FamilyMember`, `CommunityPost`… (modules 02 à 07).
- **`Nutrient`** (référentiel de nutriments) : non requis par le module 01.
- **`Media`** générique : remplacé par `Habeuk\FileUploaderBundle\Entity\Files`.
- **Suivi nutritionnel quotidien** (journal alimentaire, objectifs) : non demandé par le module 01.

---

## 6. Historique du document

| Version | Date | Changements |
| :---: | --- | --- |
| 0.3 | 13/10/2026 | Règle de visibilité des fichiers (D12) : visuels produit et logos publics, photo d'assiette et fichiers de contribution privés |
| 0.2 | 13/10/2026 | `brand` devient l'entité `Brand` (D1) · suppression des champs `slug` de `Product` et `FoodFamily` (D11) · images et photo d'assiette portées par `Habeuk\FileUploaderBundle\Entity\Files` (D10) · renumérotation des sections |
| 0.1 | 13/10/2026 | Création — proposition initiale des 9 entités du module 01 |

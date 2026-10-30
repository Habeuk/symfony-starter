# Conventions de code

> Règles à respecter pour toute modification ou ajout.
> L'agent IA (ou développeur) DOIT lire ce fichier avant de coder.
>
> Ce document est **générique** : les chemins et les exemples s'appliquent à tout
> projet démarré depuis `habeuk/symfony-starter`.

---

## 🎯 Règles générales

1. **Ne jamais inventer** de classes, méthodes, services, routes ou composants.
   Avant de créer quoi que ce soit, vérifier dans cet ordre :
   1. le code applicatif (`src/`) ;
   2. le socle maison (`vendor/habeuk/`, namespace `Habeuk\HbkSymfony`) ;
   3. les composants Symfony installés (`vendor/symfony/`).

2. **Modifier uniquement ce qui est nécessaire** : pas de refactor massif sans
   validation préalable. Un correctif ciblé vaut mieux qu'une réécriture.

3. **Typage strict** :
   - `declare(strict_types=1);` **en tête de tout fichier PHP** de `src/` et de `tests/` ;
   - signatures **entièrement typées** (paramètres, retours, propriétés) et **PHPDoc sur
     toutes les méthodes publiques** ;

4. **Langue du code** : identifiants techniques en **anglais**, commentaires et
   libellés métier en **français**.

5. **Style de code** : respecter le standard Symfony (règles `@Symfony` de
   php-cs-fixer). Si `friendsofphp/php-cs-fixer` est installé :
   `vendor/bin/php-cs-fixer fix`.

---

## 🏛️ Socle de code

Tout projet démarré depuis le starter s'appuie sur un **socle maison** :
`Habeuk\HbkSymfony` (`vendor/habeuk/habeuk_base_symfony/`).
Le starter installe également `habeuk/file-uploader-bundle`, `habeuk/url-alias-bundle`
et `habeuk/ai-agent-bundle`.

| Besoin | À utiliser (socle) | À ne pas faire |
| --- | --- | --- |
| Classe mère d'entité | `Shared\Doctrine\AbstractBaseEntity` | `#[ORM\Entity]` sur une classe nue |
| Classe mère utilisateur | `Shared\Doctrine\BaseUser` | redéfinir `email`, `password`, `roles` |
| Enum métier | `Enum\BaseEnumInterface` + `Enum\BaseEnumTrait` | enum PHP sans contrat |
| Repository | `Repository\BaseRepository` | `ServiceEntityRepository` en direct |
| DTO | `DTO\BaseDto` | DTO qui redéfinit `id` / `title` / `createdAt` |
| Déclaration d'entité | `Attribute\MenuFrontendConfig` | table de permissions maison |
| Affichage d'une colonne | `Attribute\ColumnLabel` + `Enum\ColumnType` | libellé en dur dans le front |
| Filtrage par droits en requête | `Security\QueryFilter\*` | `if ($user->hasRole(...))` en SQL |
| Mapping entité ↔ DTO | `Service\DtoMapper` | `json_decode()` / conversions manuelles |

**Règle** : si le socle fournit une brique, on l'utilise telle quelle. Si elle est
inadaptée, on la fait évoluer **dans le socle**, on ne la duplique pas dans `src/`.

---

## 📚 Documentation vivante

Chaque fonctionnalité livrée = **3 mises à jour obligatoires** :

1. **Fiche technique** : créer ou mettre à jour `docs/features/<Domaine>/<sous-fonctionnalité>.md`
   (modèle : `docs/features/_TEMPLATE.md`).
2. **Indexation** : mettre à jour l'index du domaine (`docs/features/<Domaine>/index.md`).
3. **Historique** : ajouter une entrée dans `docs/features/<Domaine>/CHANGELOG.md`
   spécifique au domaine (section `[Ajout]`, `[Modification]`, `[Correction]` ou `[Suppression]`).

> La structure documentaire complète (définition d'un « Domaine », arborescence exacte)
> est décrite dans `docs/index.md`. **Ne pas la dupliquer ici** : ce document décrit
> l'obligation, `index.md` en décrit la forme.
>
> Les modèles sont fournis : `docs/features/_TEMPLATE.md`,
> `docs/tests/_TEMPLATE.md` et `docs/entites/_TEMPLATE.md`.

### 🧪 Documentation des tests

Chaque fichier de test fait l'objet d'une fiche Markdown, rangée **par domaine, puis par type de test** :

    docs/tests/<Domaine>/<Type>/<NomFichierTest>.md

*Exemple* :

    docs/tests/Invoice/Functional/CreateInvoiceTest.md
    ↔ tests/Functional/Invoice/CreateInvoiceTest.php

> Le nom de la fiche reprend celui du fichier de test, **sans** l'extension `.php`.

---

## 🗄️ Entités et base de données

### Squelette obligatoire

Toute entité applicative :

1. `extends Habeuk\HbkSymfony\Shared\Doctrine\AbstractBaseEntity`
   — ou `BaseUser` pour l'entité utilisateur, qui en hérite ;
2. porte `#[ORM\Entity(repositoryClass: XRepository::class)]` ;
3. porte `#[ORM\Table(name: 'x')]` — **snake_case, singulier, sans préfixe** ;
4. porte `#[ORM\HasLifecycleCallbacks]` — **obligatoire** dès que l'entité utilise un
   trait avec callbacks (`PrePersist`, `PreUpdate`), sinon ces callbacks sont ignorés ;
5. porte `#[MenuFrontendConfig(...)]` (voir § Contrôle d'accès) ;
6. dispose de son **DTO** (`#[Map]` des deux côtés) et de son **Repository**
   (`extends BaseRepository`) ;
7. utilise les **traits du socle** au lieu de redéclarer `id`, `createdAt`, `owner`, `status`.

> `TimestampableTrait` déclare `createdAt` / `updatedAt` typées et non initialisées :
> elles sont renseignées par les callbacks `PrePersist` / `PreUpdate`, d'où l'obligation
> de `#[ORM\HasLifecycleCallbacks]` sur la classe finale.

### Traits du socle

| Trait | Quand l'utiliser | Contrainte |
| --- | --- | --- |
| `TimestampableTrait` | dès que la donnée est datée | dates renseignées par les callbacks ; `#[ORM\HasLifecycleCallbacks]` obligatoire |
| `UuidTrait` | donnée exposée en URL ou exportée (RGPD, API publique) | ajoute `uuid` unique, généré en `PrePersist` |
| `OwnerTrait` | donnée rattachée à un utilisateur | implique `OwnerInterface` + filtre de visibilité |
| `StatusTrait` | activation / désactivation | ⚠ `status` vaut **`false`** par défaut → forcer la valeur voulue dans le constructeur |

### Clé primaire

- `int` auto-incrémenté, hérité de `AbstractBaseEntity` — **convention par défaut**.
- Ajouter `UuidTrait` si l'entité est exposée publiquement (URL, API) ou exportée.

### Nommage

| Objet | Convention | Exemple |
| --- | --- | --- |
| Table | snake_case, singulier, quotée si mot réservé SQL | `` `user` ``, `article`, `order_line` |
| Colonne | snake_case | `created_at`, `unit_price` |
| Clé étrangère (colonne) | `<relation>_id` | `owner_id`, `category_id` |
| Table pivot | `<table_a>_<table_b>`, ordre alphabétique | `article_tag` |
| Index | `IDX_<TABLE>_<COLONNE>` | `IDX_ARTICLE_REFERENCE` |
| Contrainte unique | `UNIQ_<TABLE>_<COLONNE>` | `UNIQ_USER_EMAIL` |
| Clé étrangère (nom) | `FK_<TABLE>_<COLONNE>` | `FK_ARTICLE_CATEGORY_ID` |
| Entité / DTO / Repository | PascalCase, singulier, suffixe de rôle | `Article`, `ArticleDto`, `ArticleRepository` |

### Migrations

- **Toujours** `php bin/console make:migration` puis `php bin/console doctrine:migrations:migrate`.
- **Jamais** `doctrine:schema:update`, jamais de SQL écrit à la main.
- Une entité et sa migration vivent dans le **même commit**.

### Types et choix de stockage

- **Dates** : `Types::DATETIME_IMMUTABLE`.
- **Montants, mesures, pourcentages** : `DECIMAL(p, s)`, **jamais `FLOAT`**
  (erreurs d'arrondi inacceptables sur des montants ou des mesures).
- **Listes ouvertes** : `JSON` / `JSONB` lorsque le SGBD le permet, plutôt qu'une
  table dédiée dont le schéma serait figé trop tôt.
- Un **enum PHP** doit implémenter `BaseEnumInterface`.

### Suppression des données

- **Par défaut : on ne supprime pas**, on désactive (`StatusTrait`).
- `ON DELETE CASCADE` **uniquement** pour les données filles d'un utilisateur
  (favoris, historiques, préférences) — leur disparition va de soi.
- `ON DELETE SET NULL` pour les références de traçabilité (`createdBy`, `updatedBy`).
- `ON DELETE RESTRICT` (comportement par défaut) pour tout référentiel partagé.

### Relations

- Toujours mapper **les deux côtés** d'une relation `OneToMany` / `ManyToOne`
  (`inversedBy` / `mappedBy`) et déclarer explicitement le côté propriétaire
  (`ManyToOne`).
- Les collections sont initialisées dans le constructeur
  (`new ArrayCollection()`), jamais laissées nulles.
- Fournir les méthodes `add…` / `remove…` symétriques, jamais un setter de collection
  qui écrase la collection existante.
- Les associations exposées au front sont annotées
  `#[EntityReference(Target::class, 'name')]` ou
  `#[EntityCollectionReference(Target::class, 'name')]`.

---

## 🔐 Contrôle d'accès aux entités

### Déclaration obligatoire

Toute entité exposée à l'interface porte `#[MenuFrontendConfig]` :

```php
#[MenuFrontendConfig(
  enabled: true,
  label: "Articles",            // libellé français affiché dans le menu
  entity: "Article",
  icon: "pi pi-file",
  order: 10,
  scope: ScopeEnum::GLOBAL,     // QUI VOIT
  requireOwnership: false,      // QUI MODIFIE
  permissions: PermissionEnum::basic(),
  cardinality: MenuFrontendConfig::UNLIMITED,
)]
```

> Autres paramètres disponibles : `roles`, `display`, `parentEntity`, `auditable`,
> `revisionable` — voir la docblock de `Habeuk\HbkSymfony\Attribute\MenuFrontendConfig`.

### `scope` — « qui voit »

| Scope | Sens |
| --- | --- |
| `PERSONAL` | l'utilisateur ne voit que ses propres données |
| `TEAM` | les données sont partagées au sein de l'équipe |
| `GLOBAL` | tout le monde voit tout |
| `RESTRICTED` | accès par règles métier spécifiques (**non implémenté dans le socle**) |

### `requireOwnership` — « qui modifie »

`scope` et `requireOwnership` sont **complémentaires, pas redondants** :
`scope` gouverne la **lecture**, `requireOwnership` gouverne l'**écriture**.
`requireOwnership: true` → seul le propriétaire peut modifier ou supprimer
(la consultation reste régie par `scope`).

**Cas particuliers :**

- `scope: PERSONAL` → `requireOwnership` est **ignoré** (on est déjà propriétaire).
- `scope: GLOBAL` + `permissions: [PermissionEnum::VIEW]` → référentiel public en lecture seule.

### Filtrage des requêtes (obligatoire)

Le socle applique un **filtre de visibilité** à chaque requête issue d'un
`BaseRepository` (`loadAll`, `findWithSecurity`). Si aucun filtre ne prend en charge
l'entité, une **exception est levée** et toutes les listes de cette entité cassent.

Conséquences pratiques :

- entité avec `OwnerTrait` → implémenter `OwnerInterface`, le socle fournit le filtre ;
- entité **sans propriétaire** (référentiel, contenu public) → **créer un
  `AbstractQueryVisibilityFilter` dédié** dans `src/Security/QueryFilter/Entity/`.
  Il est pris en compte automatiquement (tag `app.query_visibility_filter`) ;
- les tests d'accès **ne s'écrivent pas à la main** dans les contrôleurs : on s'appuie
  sur `EntityAccessChecker`.

### DTO et champs exposés

- Un DTO par entité : `extends BaseDto`, annoté `#[Map(target: Entité::class)]`
  (et l'entité annotée `#[Map(target: Dto::class)]`).
- **Les contraintes de validation (`#[Assert\…]`) et `#[ColumnLabel]` vont sur le DTO**,
  pas sur l'entité.
- Les **groupes de sérialisation** (`VIEW`, `LIST`, `EDIT`, `CREATE`, `ADMIN`,
  `REFERENCE`, `EXPORT`…) filtrent les champs des formulaires et des réponses.
  **Ne jamais exposer un champ sans groupe explicite.**

---

## 🔢 Enums

Tout enum métier implémente `Habeuk\HbkSymfony\Enum\BaseEnumInterface` et utilise
`BaseEnumTrait`. Il fournit alors `getValue()`, `getLabel()`, `values()`, `labels()`,
`choices()`, `createFromValue()`, `createFromLabel()`, `tryFrom…()`, `isValid()`.

- `value` : technique, en anglais (`ROLE_USER`, `pending`).
- `getLabel()` : **en français**, destiné à l'affichage.
- Emplacement : `src/Enum/`.
- **Jamais** de constantes de classe à la place d'un enum
  (`const STATUS_PENDING = 'pending'` est proscrit).

---

## 🛡️ Sécurité et données personnelles

Dès qu'une donnée personnelle ou sensible est manipulée :

- **Mots de passe** : uniquement via `UserPasswordHasherInterface` — **jamais** en clair,
  jamais en MD5/SHA1, jamais de comparaison `===` sur un mot de passe.
- **Toute entrée utilisateur** passe par le Validator (`#[Assert\…]` ou contraintes DTO).
- **Aucune donnée personnelle** n'est exposée dans un groupe de sérialisation public.
- **Aucune donnée n'est partagée** sans consentement explicite de l'utilisateur.
- Le contrôle d'accès passe par `EntityAccessChecker` / `EntityVisibilityFilter` —
  **pas de test de rôle écrit à la main** dans un contrôleur.
- Les secrets vivent dans `.env.local` (non versionné) ou dans le coffre
  (`php bin/console secrets:set`), **jamais** dans `.env` versionné.
- Toute donnée sensible doit être **exportable et supprimable** sur demande
  de l'utilisateur (RGPD).

---

## 🧪 Tests

- Une fonctionnalité **n'est pas terminée** sans un test qui l'exerce comme un appelant :
  une **requête HTTP** pour un contrôleur, un **appel de service** pour un service —
  pas seulement « ça ne lève pas d'exception ».
- `WebTestCase` pour le fonctionnel (HTTP), `KernelTestCase` pour un service.
- Emplacement : `tests/<Type>/<Domaine>/`.
- Avant de livrer : `php bin/phpunit` **et** `vendor/bin/phpstan analyse`.
- Un correctif de bug commence par un test qui reproduit le bug.

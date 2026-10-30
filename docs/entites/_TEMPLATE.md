# Proposition de modèle de données — Module `<NN>` « `<Nom>` »

> **Statut : proposition à valider.** Aucune entité ne sera codée avant validation de ce document.
> Version : 0.1 — Date : JJ/MM/AAAA
> Référentiel fonctionnel : `docs/fonctionnel/module-<NN>-<nom>.md`
> Conventions applicables : [CONVENTIONS.md](../CONVENTIONS.md)

## 1. Origine de chaque besoin

Chaque entité décrite ci-dessous découle d'une exigence explicite du module.

| Réf. | Exigence fonctionnelle | § du module |
| :---: | --- | :---: |
| E1 | … | §… |

## 2. Vue d'ensemble des entités proposées

| # | Entité | Priorité | Réf. | Rôle en une phrase |
| :---: | --- | :---: | :--- | --- |
| 1 | `…` | 🟢 Noyau / 🟡 Secondaire | E… | … |

**Relations entre entités**

```text
EntitéA
    ├─1..n─ EntitéB
    └─n..n─ EntitéC   (via table_pivot)
```

## 3. Détail des entités

### 3.1 `<Entité>` 🟢

Description en une ou deux phrases.

| Champ | Type | Contraintes | Justification |
| --- | --- | --- | --- |
| `id` | int | PK, auto | Convention projet |
| `…` | … | … | … |
| `createdAt` / `updatedAt` | datetime | lifecycle callbacks | Convention projet |

**Index** : …

**Note** : …

## 4. Décisions à trancher

| Réf. | Question | Recommandation |
| :---: | --- | --- |
| **D1** | … | … |

## 5. Ce qui n'est pas dans ce module

Volontairement hors périmètre, pour éviter d'anticiper sur les modules suivants :

- …

## 6. Historique du document

| Version | Date | Changements |
| :---: | --- | --- |
| 0.1 | JJ/MM/AAAA | Création — proposition initiale |

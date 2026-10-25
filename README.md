Voici un `README.md` complet et professionnel pour votre projet `habeuk/symfony-starter`, prêt à être publié sur Packagist/GitHub. Il couvre l'installation, la configuration, l'utilisation, et les informations de licence.

```markdown
# Habeuk Symfony Starter

[![Latest Version on Packagist](https://img.shields.io/packagist/v/habeuk/symfony-starter.svg)](https://packagist.org/packages/habeuk/symfony-starter)
[![Total Downloads](https://img.shields.io/packagist/dt/habeuk/symfony-starter.svg)](https://packagist.org/packages/habeuk/symfony-starter)
[![License](https://img.shields.io/packagist/l/habeuk/symfony-starter.svg)](https://packagist.org/packages/habeuk/symfony-starter)

Un squelette Symfony prêt à l'emploi, maintenu par [Habeuk](https://habeuk.com). Ce starter inclut une configuration de base, des dépendances essentielles et une structure de projet prête pour le développement d'applications web modernes.

## Sommaire

- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [Structure du projet](#structure-du-projet)
- [Tests](#tests)
- [Contribuer](#contribuer)
- [Sécurité](#sécurité)
- [Licence](#licence)

## Prérequis

Avant de commencer, assurez-vous que votre environnement dispose de :

- **PHP 8.2 ou supérieur** (avec les extensions `ctype`, `iconv`, `intl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `mbstring`)
- **Composer 2.x**
- **Symfony CLI** (recommandé, mais optionnel)
- **MySQL 8.0+** ou **PostgreSQL 14+** (selon votre configuration)
- **Node.js 18+** et **npm** (si vous utilisez les assets front-end)

## Installation

Créez un nouveau projet à partir de ce starter :

```bash
composer create-project habeuk/symfony-starter mon-projet
```

Puis placez-vous dans le dossier du projet :

```bash
cd mon-projet
```

Si vous utilisez Symfony CLI, vous pouvez lancer le serveur de développement :

```bash
symfony serve
```

Sinon, avec le serveur PHP intégré :

```bash
php -S localhost:8000 -t public
```

L'application est accessible à l'adresse [http://localhost:8000](http://localhost:8000).

## Configuration

### Variables d'environnement

Copiez le fichier d'exemple et adaptez les valeurs à votre environnement :

```bash
cp .env .env.local
```

Éditez `.env.local` pour configurer :

- `APP_ENV` : environnement (`dev`, `prod`, `test`)
- `APP_SECRET` : clé secrète de l'application
- `DATABASE_URL` : chaîne de connexion à la base de données
- `MAILER_DSN` : configuration du transport de mail

Exemple de `DATABASE_URL` pour MySQL :

```dotenv
DATABASE_URL="mysql://user:password@127.0.0.1:3306/mon_projet?serverVersion=8.0&charset=utf8mb4"
```

### Base de données

Créez la base de données et exécutez les migrations :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Assets front-end

Si le projet utilise Webpack Encore ou Vite, installez les dépendances et compilez les assets :

```bash
npm install
npm run build
```

## Utilisation

### Commandes utiles

```bash
# Vider le cache
php bin/console cache:clear

# Créer une entité Doctrine
php bin/console make:entity

# Créer un contrôleur
php bin/console make:controller

# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Lancer les tests
php bin/phpunit
```

### Développement

Le starter est fourni avec le pack de développement Symfony (`symfony/debug-pack`, `symfony/profiler-pack`). Le profiler est accessible en environnement `dev` à l'adresse `/ _profiler` (sans l'espace).

## Structure du projet

```
mon-projet/
├── bin/                  # Exécutables (console, phpunit)
├── config/               # Configuration Symfony (packages, routes, services)
├── migrations/           # Migrations Doctrine
├── public/               # Racine web (index.php, assets)
├── src/                  # Code source PHP (Controller, Entity, Repository, ...)
├── templates/            # Templates Twig
├── tests/                # Tests PHPUnit
├── translations/         # Fichiers de traduction
├── var/                  # Cache et logs
├── vendor/               # Dépendances Composer
├── .env                  # Variables d'environnement par défaut
├── .env.local            # Variables d'environnement locales (non versionné)
├── composer.json
└── README.md
```

## Tests

Le starter inclut PHPUnit. Pour lancer la suite de tests :

```bash
php bin/phpunit
```

Pour un rapport de couverture (si Xdebug est installé) :

```bash
php bin/phpunit --coverage-html var/coverage
```

## Contribuer

Les contributions sont les bienvenues. Pour contribuer :

1. Forkez le dépôt
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/ma-fonctionnalite`)
3. Commitez vos changements (`git commit -m 'Ajout de ma fonctionnalité'`)
4. Poussez la branche (`git push origin feature/ma-fonctionnalite`)
5. Ouvrez une Pull Request

Merci de respecter les standards de code Symfony (PSR-12) et de lancer les tests avant de soumettre une PR.

## Sécurité

Si vous découvrez une vulnérabilité de sécurité, merci de **ne pas** ouvrir une issue publique. Contactez directement l'équipe à l'adresse `security@habeuk.com` pour permettre une correction avant divulgation.

## Licence

Ce projet est distribué sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

Maintenu par [Habeuk](https://habeuk.com) — pour toute question, ouvrez une issue ou contactez-nous à `contact@habeuk.com`.
```

### Points à adapter avant publication

1. **Le nom du package** : vérifiez que `habeuk/symfony-starter` correspond bien au nom déclaré dans votre `composer.json`.
2. **Les prérequis** : ajustez la version de PHP (8.2, 8.3, 8.4 ?) et les extensions selon ce que votre starter utilise réellement.
3. **La licence** : le README mentionne MIT, mais vérifiez que c'est bien la licence du projet (le fichier `LICENSE` doit exister à la racine).
4. **Les liens** : remplacez `https://habeuk.com` et `contact@habeuk.com` par les URLs et adresses réelles.
5. **Les commandes** : si votre starter utilise Vite au lieu de Webpack Encore, ou si la base est PostgreSQL au lieu de MySQL, adaptez les exemples en conséquence.
6. **Le badge Packagist** : les badges ne fonctionneront qu'une fois le package publié sur Packagist. Tant que ce n'est pas le cas, vous pouvez les retirer ou les laisser (ils afficheront « not found »).

Une fois ce README en place, pensez à ajouter un fichier `LICENSE` (MIT, par exemple) et, si ce n'est pas déjà fait, à déclarer le package sur [Packagist](https://packagist.org/packages/submit) pour que `composer create-project habeuk/symfony-starter` fonctionne pour tout le monde.
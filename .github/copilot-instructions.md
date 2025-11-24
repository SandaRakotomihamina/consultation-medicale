## Contexte rapide

Ce dépôt est une application Symfony (requiert PHP >= 8.2, Symfony 7.3.*). Structure clé :
- Code PHP : `src/` (contrôleurs dans `src/Controller`, sécurité dans `src/Security`).
- Configuration : `config/` (packages, routes, services). Voir `config/services.yaml` pour les defaults (autowire/autoconfigure).
- Front web : `public/index.php` est l'entrée HTTP (utilise `vendor/autoload_runtime.php`).
- Templates Twig : `templates/` (ex. `templates/base.html.twig` utilise `importmap('app')`).
- Assets JS/CSS : `assets/` (Stimulus controllers sous `assets/controllers`, importmap configuré via `assets/importmap`).
- Base de données et migrations : `migrations/` (migrations Doctrine present).

## Objectifs pour un agent IA (priorités)
- Comprendre la hiérarchie Symfony : routes → contrôleurs (`src/Controller`) → services (`src/`) → templates (`templates/`).
- Respecter l'autowiring/autoconfigure : évitez d'ajouter des constructions manuelles sans raison; préférez déclarer une classe sous `App\` et laisser Symfony l'injecter.
- Pour tout changement côté JS Stimulus, mettez à jour `assets/controllers/` et l'importmap (voir `composer.json` scripts `importmap:install`).

## Commandes fréquentes (exemples réels)
- Installer dépendances : `composer install` (exécute aussi les auto-scripts définis dans `composer.json`).
- Console Symfony : `php bin/console <commande>` (ex. `php bin/console cache:clear`, `php bin/console doctrine:migrations:migrate`).
- Importmap / assets :
  - `php bin/console importmap:install`
  - `php bin/console assets:install public` (les scripts sont aussi dans `composer.json` auto-scripts).
- Lancer l'app localement :
  - Préféré : utiliser Symfony CLI `symfony server:start` (si installé).
  - Alternative : `php -S localhost:8000 -t public`.
- Tests : exécuter `./bin/phpunit` (fichier `bin/phpunit` fourni) ou `vendor/bin/phpunit`. Le bootstrap est `tests/bootstrap.php`.

## Conventions/projets spécifiques
- Namespace racine : `App\` mappé à `src/` (PSR-4). Nommez les classes conformément aux dossiers.
- Services : `config/services.yaml` définit `_defaults: autowire: true, autoconfigure: true`. Préférez déclarer explicitement uniquement si vous avez besoin de tags ou d'arguments custom.
- Kernel : `src/Kernel.php` utilise `MicroKernelTrait` — le projet suit la structure standard Symfony, donc cherchez la configuration dans `config/packages/*` et `config/routes/*`.
- Templates : blocs Twig standard ; `base.html.twig` contient l'importmap et les blocs `stylesheets`, `javascripts`, `body` — respecter ces blocs lors de l'ajout de markup ou de scripts.
- JS Stimulus : les contrôleurs frontend sont en `assets/controllers/` et référencés via importmap; préférez la logique Stimulus pour interactions UI.

## Intégrations & points d'attention
- Doctrine ORM + Migrations : utilisez `php bin/console doctrine:schema:update --dump-sql` pour vérifier, et `php bin/console doctrine:migrations:migrate` pour exécuter.
- Sécurité : contrôlez `config/packages/security.yaml` et les classes dans `src/Security/` (ex. `UserAuthenticator.php`, `Entity/User.php`).
- Runtime/Symfony : l'application utilise `vendor/autoload_runtime.php` (voir `public/index.php` et `bin/console`) — ne supprimez pas ce bootstrap.

## Exemples courts à utiliser lors des modifications
- Ajouter un service : créer `src/Service/MyService.php` puis l'utiliser dans un contrôleur — Symfony l'enregistrera automatiquement grâce à `services.yaml`.
- Ajout d'une route simple : créer `src/Controller/ThingController.php` et utiliser l'attribut de route PHP 8 (ex. Route('/thing', name: 'thing')) ; vérifier `config/routes/` si des routes y sont définies par défaut.

## Recommandations pour l'agent
- Ne modifiez pas les fichiers de bootstrap (`public/index.php`, `bin/console`, `vendor/autoload_runtime.php`) sauf pour corrections évidentes; expliquez toute modification proposée.
- Quand vous proposez des commandes, fournissez le contexte (ex. pourquoi exécuter `cache:clear` ou `importmap:install`).
- Incluez un petit test/unittest quand vous changez une logique métier : le projet a PHPUnit configuré (`phpunit.dist.xml`).

## Où lire pour plus de contexte
- `composer.json` (requirements, scripts, PHP/Symfony version)
- `config/services.yaml` (autowire/autoconfigure defaults)
- `templates/base.html.twig` (structure HTML et importmap usage)
- `assets/controllers/` (Stimulus controllers pattern)
- `migrations/` (histoire du schéma DB)

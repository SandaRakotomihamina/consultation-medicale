# 📋 Application de Consultation Médicale - Gendarmerie Nationale

## Vue d'ensemble

Cette application Symfony 7.3 est un système de gestion de consultations médicales pour la Gendarmerie Nationale. Elle permet aux utilisateurs (médecins et unités) de gérer les demandes et consultations médicales du personnel, avec un système d'authentification et de rôles.

**Stack technique:**
- PHP >= 8.2
- Symfony 7.3
- MySQL/PostgreSQL (Doctrine ORM)
- Twig (templates)
- Stimulus (JavaScript côté client)
- Bootstrap & CSS personnalisé

---

## 📁 Structure du projet

```
App/
├── src/                      # Code source principal
│   ├── Controller/          # Contrôleurs Symfony (routes, logique métier)
│   ├── Entity/              # Entités Doctrine (modèles de données)
│   ├── Form/                # Formulaires Symfony
│   ├── Repository/          # Classes d'accès aux données
│   ├── Security/            # Authentification et sécurité
│   ├── Service/             # Services métier
│   ├── DataFixtures/        # Données de test
│   └── Kernel.php           # Noyau Symfony
├── config/                  # Configuration globale
│   ├── packages/            # Configuration des bundles
│   ├── routes/              # Configuration des routes
│   ├── services.yaml        # Configuration des services (autowiring)
│   └── routes.yaml          # Routes principales
├── templates/               # Templates Twig (vues HTML)
├── assets/                  # Assets front-end
│   ├── controllers/         # Contrôleurs Stimulus (JS interactif)
│   ├── styles/             # CSS
│   └── images/             # Images
├── migrations/              # Migrations de schéma Doctrine
├── public/                  # Dossier racine web
│   └── index.php           # Point d'entrée HTTP
├── tests/                   # Tests unitaires
├── var/                    # Fichiers générés (cache, logs)
└── vendor/                 # Dépendances Composer

```

---

## 🗄️ Architecture données

### Entités principales

#### 1. **User** (`src/Entity/User.php`)
Utilisateurs de l'application avec authentification et rôles.

```
Champs:
- id (int) : Identifiant unique
- username (string, 180) : Nom d'utilisateur unique
- password (string) : Hash du mot de passe
- roles (array JSON) : Tableau des rôles (ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_USER)
- Matricule (string, 16, nullable) : Utilisé pour les rôles ROLE_ADMIN/ROLE_SUPER_ADMIN
- title (string, 128, nullable) : Titre (Dr., etc.)
- name (string, 255, nullable) : Nom complet
- CODUTE (string, 6, nullable) : Code unité (pour ROLE_USER)
- LIBUTE (string, 35, nullable) : Libellé unité (pour ROLE_USER)
- LOCAL (string, 35, nullable) : Localisation (pour ROLE_USER)
```

#### 2. **DemandeDeConsultation** (`src/Entity/DemandeDeConsultation.php`)
Demandes de consultation en attente.

```
Champs:
- id (int) : Identifiant unique
- Matricule (string, 16) : Matricule du personnel
- Nom (string, 255) : Nom du personnel
- Grade (string, 255) : Grade militaire
- LIBUTE (string, 35, nullable) : Unité
- Motif (text) : Motif de la consultation
- DelivreurDeMotif (string, 255) : Qui a délivré le motif
- Date (datetime) : Date de la demande
```

#### 3. **ConsultationList** (`src/Entity/ConsultationList.php`)
Consultations finalisées avec observations et options.

```
Champs:
- id (int) : Identifiant unique
- Matricule (string, 16) : Matricule du personnel
- Nom (string, 255) : Nom du personnel
- Grade (string, 255) : Grade militaire
- LIBUTE (string, 35, nullable) : Unité
- Motif (text) : Motif initial
- DelivreurDeMotif (string, 255) : Délivreur du motif
- Observation (text) : Observations médicales
- DelivreurDObservation (string, 255) : Médecin qui a observé
- Date (datetime) : Date de consultation
- Repos (string, 16, nullable) : Jours de repos recommandés
- Exemption (JSON, nullable) : Options d'exemption
- debutExemption (date, nullable) : Début exemption
- finExemption (date, nullable) : Fin exemption
- Adrresse (JSON, nullable) : Adresse (mal orthographié, garder comme est)
- PATC (int, nullable) : Jours supplémentaires
```

#### 4. **Personnel** (`src/Entity/Personnel.php`)
Base de données de personnel pour les tests (mode DEV).

```
Champs:
- Matricule (string, 255) : Clé primaire
- Nom (string, 255) : Nom
- Grade (string, 255) : Grade
- LIBUTE (string, 255, nullable) : Unité
```

#### 5. **Unite** (`src/Entity/Unite.php`)
Unités militaires pour la recherche et validation.

```
Champs:
- CODUTE (string, 6) : Code unité (clé primaire)
- UNITY (string, 255) : Nom/libellé de l'unité
```

#### 6. **ExemptionOption & AdresseOption**
Tables de référence pour les options de consultation.

---

## 🔐 Système de rôles et authentification

### Rôles utilisateurs

| Rôle | Description | Accès |
|------|-------------|-------|
| **ROLE_SUPER_ADMIN** | Administrateur du site | Tous les patients, gestion des utilisateurs, statistiques |
| **ROLE_ADMIN** | Médecin | Voir les consultations de son unité/personnel, créer consultations |
| **ROLE_USER** | Compte d'unité | Voir les consultations de son unité uniquement |

### Authentification

- **Fichier:** `src/Security/UserAuthenticator.php`
- **Stockage:** Base de données (Doctrine)
- **Hashage:** Symfony Password Hasher (bcrypt par défaut)
- **Login:** Via username avec CSRF token
- **Logout:** Route `/logout`

**Fixtures par défaut:**
- Username: `superadmin` / Password: `superadmin` (défini dans `src/DataFixtures/UserFixtures.php`)

---

## 🎮 Contrôleurs et Routes

### MainController (`src/Controller/MainController.php`)
Gestion principale des consultations et utilisateurs.

| Route | Méthode | Rôles | Description |
|-------|---------|-------|-------------|
| `/` | GET | PUBLIC | Page d'accueil (listage consultations) |
| `/api/consultations/load-more` | GET | PUBLIC | Charger plus de consultations (infinite scroll) |
| `/api/statistique` | GET | SUPER_ADMIN | Statistiques |
| `/demande/new` | GET/POST | ADMIN | Créer demande consultation |
| `/consultation/{id}` | GET | ADMIN/SUPER_ADMIN | Voir consultation |
| `/consultation/{id}/edit` | POST | ADMIN/SUPER_ADMIN | Modifier consultation |
| `/user/new` | GET/POST | SUPER_ADMIN | Créer utilisateur |
| `/user/{id}/edit` | GET/POST | SUPER_ADMIN | Modifier utilisateur |
| `/user/list` | GET | SUPER_ADMIN | Lister utilisateurs |

### ApiController (`src/Controller/ApiController.php`)
APIs pour recherche de personnel et unités.

| Route | Méthode | Description |
|-------|---------|-------------|
| `/api/personnel/{matricule}` | GET | Rechercher personnel (API PROD GRH) |
| `/api/personnel-local/{matricule}` | GET | Rechercher personnel (mode DEV) |
| `/api/check-user-exists` | POST | Vérifier existence utilisateur |
| `/api/unite-search` | GET | Chercher unités (suggestions) |
| `/api/check-unite-exists` | POST | Vérifier existence unité |

### SecurityController (`src/Controller/SecurityController.php`)
Gestion authentification.

| Route | Méthode | Description |
|-------|---------|-------------|
| `/login` | GET/POST | Page de connexion |
| `/logout` | GET | Déconnexion |

### StatistiqueController
Génération de statistiques.

### OptionsApiController
Gestion des options (exemptions, adresses).

---

## 🎯 Flux utilisateur principal

### 1. Créer une demande de consultation
**Accès:** ROLE_ADMIN (médecin)

```
Formulaire DemandeType:
  └─ Chercher personnel par matricule (via Stimulus personnel_lookup_controller)
     ├─ Mode PERSONNEL: Récupère nom, grade, unité
     └─ Mode UNITÉ: Recherche d'unités avec suggestions
  └─ Saisir motif de consultation
  └─ Enregistrer demande
```

### 2. Traiter une demande → Consultation
**Accès:** ROLE_ADMIN

```
Page consultation:
  └─ Voir demande
  └─ Ajouter observations
  └─ Définir jours de repos
  └─ Ajouter exemptions (dates, types)
  └─ Enregistrer → ConsultationList
```

### 3. Gérer utilisateurs
**Accès:** ROLE_SUPER_ADMIN

```
Créer utilisateur:
  ├─ ROLE_ADMIN/SUPER_ADMIN:
  │  └─ Chercher personnel par matricule
  │     └─ Récupère automatiquement nom, grade, unité
  └─ ROLE_USER (unité):
     └─ Chercher unité par code
        └─ Récupère libellé et localisation
```

---

## 🔌 Intégrations externes

### API GRH (Production)
**URL:** `http://10.254.52.116:7000/apigrh/client`

Récupère les données RH du personnel:
- Matricule (MLE)
- Nom (NOMPERS + PRENOM)
- Grade (ABREVGRADE)
- Unité (UNITE)

**Credentials (hardcodés, voir `src/Controller/ApiController.php`):**
```
Authorization: clé API
x-api-key: mail utilisateur
```

### Mode Développement
Utilise la table `Personnel` locale au lieu de l'API GRH.

**Remplissage:** `php bin/console doctrine:fixtures:load` (voir `src/DataFixtures/PersonnelFixtures.php`)

---

## 🎨 Front-end et Stimulus

### Stimulus Controllers
Contrôleurs JavaScript pour interactions interactives dans `assets/controllers/`:

#### 1. **personnel_lookup_controller.js**
Recherche dynamique de personnel/unités.

**Fonctionnalités:**
- Recherche matricule en temps réel (débounce 500ms)
- Mode PERSONNEL: Recherche personnel (matricule, nom, grade)
- Mode UNITÉ: Recherche unités (suggestions autocomplete)
- Validation double: Personnel doit être de la même unité
- Validation unicité: Utilisateur ne peut pas exister deux fois

**Usage Twig:**
```twig
<div data-controller="personnel-lookup"
     data-personnel-lookup-check-user-exists-value="true"
     data-personnel-lookup-user-libute-value="{{ user.libute }}"
     data-personnel-lookup-user-local-value="{{ user.local }}">
  <input data-personnel-lookup-target="matricule" ...>
  <input data-personnel-lookup-target="nom" readonly ...>
  <select data-personnel-lookup-target="roles" 
          data-action="change->personnel-lookup#onRoleChange">
  </select>
</div>
```

#### 2. **Other Controllers**
- `animation_slideshow_controller.js` : Carrousel
- `consultation_options_controller.js` : Options de consultation
- `csrf_protection_controller.js` : Protection CSRF
- `infinite_scroll_controller.js` : Chargement infini
- `loading_controller.js` : Écran de chargement
- `scroll_to_top_controller.js` : Bouton haut de page
- `theme_controller.js` : Thème clair/sombre

### Assets
- **CSS:** `assets/styles/app.css` (variables CSS pour thème)
- **JS:** `assets/app.js` (point d'entrée)
- **Importmap:** `assets/importmap` (gestion dépendances JS)

---

## 🗄️ Base de données

### Configuration
**Fichier:** `config/packages/doctrine.yaml`

```yaml
DATABASE_URL="mysql://root:@127.0.0.1:3306/consultation"
```

Base MySQL nommée `consultation` (voir `.env`).

### Migrations
**Dossier:** `migrations/`

Exécuter migrations:
```bash
php bin/console doctrine:migrations:migrate
```

Créer nouvelle migration:
```bash
php bin/console make:migration
```

### Schéma
```
user
├── id (PK)
├── username (UNIQUE)
├── roles (JSON)
├── password
├── matricule (NULLABLE, pour ROLE_ADMIN/SUPER_ADMIN)
├── title, name (nullable)
├── CODUTE, LIBUTE, LOCAL (nullable, pour ROLE_USER)
└── (Timestamps)

demande_de_consultation
├── id (PK)
├── matricule
├── nom, grade
├── LIBUTE (unité)
├── motif (text)
├── delivreur_de_motif
└── date

consultation_list (résultat de consultation)
├── id (PK)
├── (mêmes champs que demande)
├── observation (observations médicales)
├── delivreur_d_observation (médecin)
├── repos (jours de repos)
├── exemption (JSON options)
├── debut_exemption, fin_exemption
└── patc (jours supplémentaires)

personnel (MODE DEV)
├── matricule (PK)
├── nom, grade
└── libute

unite
├── codute (PK)
└── unity (libellé)
```

---

## 🚀 Installation et démarrage

### Prérequis
- PHP >= 8.2
- Composer
- MySQL/PostgreSQL
- Node.js (optionnel, pour assets)

### Installation

1. **Cloner et installer dépendances:**
```bash
cd App
composer install
```

2. **Configurer base de données (.env.local):**
```bash
# .env (par défaut MySQL)
DATABASE_URL="mysql://root:password@127.0.0.1:3306/consultation"
```

3. **Créer base et appliquer migrations:**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

4. **Charger fixtures (données de test):**
```bash
php bin/console doctrine:fixtures:load
```

5. **Générer assets:**
```bash
php bin/console importmap:install
php bin/console assets:install public
```

6. **Démarrer serveur:**
```bash
# Méthode 1: Symfony CLI (préféré)
symfony server:start

# Méthode 2: PHP built-in
php -S localhost:8000 -t public
```

Accéder à `http://localhost:8000`

---

## 💾 Commandes utiles

```bash
# Cache et assets
php bin/console cache:clear
php bin/console assets:install public
php bin/console importmap:install

# Base de données
php bin/console doctrine:database:create
php bin/console doctrine:database:drop
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

# Développement
php bin/console make:entity
php bin/console make:migration
php bin/console make:controller
php bin/console make:form

# Tests
./bin/phpunit
```

---

## 🔧 Configuration services

### Services (autowiring)

**Fichier:** `config/services.yaml`

```yaml
services:
  _defaults:
    autowire: true      # Injection automatique via type hints
    autoconfigure: true # Enregistrement automatique
```

Toute classe sous `App\` est automatiquement enregistrée comme service.

### Exemple service custom:

```php
// src/Service/MyService.php
namespace App\Service;

class MyService {
    public function doSomething() { }
}

// Utilisation dans contrôleur
class MyController extends AbstractController {
    public function action(MyService $service) {
        $service->doSomething();
    }
}
```

---

## 🔒 Sécurité

### Firewall (`config/packages/security.yaml`)
```yaml
firewalls:
  main:
    lazy: true
    provider: app_user_provider
    custom_authenticator: App\Security\UserAuthenticator
    logout:
      path: app_logout
      target: app_login
```

### Access Control
- Actuellement aucune restriction globale
- Vérifications `$this->isGranted('ROLE_X')` dans contrôleurs

### CSRF
Protection automatique via Symfony (jeton caché dans formulaires).

---

## 📝 Formulaires

**Dossier:** `src/Form/`

### DemandeType
Création demande consultation.

### ConsultationType
Finalization consultation (observations, exemptions, repos).

### UserType
Création/modification utilisateurs (différents champs selon rôle).

---

## 🧪 Tests

**Configuration:** `phpunit.dist.xml`

```bash
./bin/phpunit
./bin/phpunit tests/Controller/
./bin/phpunit tests/Entity/
```

---

## 📊 Points clés pour la modification du code

### 1. Ajouter une nouvelle route
```php
// src/Controller/MyController.php
#[Route('/my-route', name: 'app_my_route')]
public function myAction(): Response {
    return $this->render('my_template.html.twig');
}
```

### 2. Créer nouvelle entité
```bash
php bin/console make:entity EntityName
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### 3. Ajouter validation formulaire
```php
// src/Form/MyType.php
->add('field', TextType::class, [
    'constraints' => [
        new NotBlank(['message' => 'Message erreur']),
        new Length(['min' => 3])
    ]
])
```

### 4. Utiliser service custom
```php
// Créer src/Service/MyService.php
// Utiliser dans contrôleur via injection
public function action(MyService $service) { }
```

### 5. Ajouter contrôleur Stimulus
```javascript
// assets/controllers/my_controller.js
import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
    static targets = ['input'];
    connect() { }
}
```

Puis utiliser dans Twig:
```twig
<div data-controller="my" data-my-target="input"></div>
```

### 6. Modifier template
```twig
{# templates/my_template.html.twig #}
{% extends 'base.html.twig' %}

{% block title %}Mon Titre{% endblock %}

{% block body %}
  <div class="container">
    Contenu ici
  </div>
{% endblock %}
```

---

## 🎯 Workflow des consultations

```
1. DEMANDE (DemandeDeConsultation)
   └─ Créée par: Médecin (ROLE_ADMIN)
   └─ Saisie: Matricule, Motif, Délivreur
   └─ État: En attente

2. CONSULTATION (ConsultationList)
   └─ Créée à partir de: Demande
   └─ Ajout par: Médecin
   └─ Saisie: Observations, Repos, Exemptions
   └─ État: Finalisée

3. AFFICHAGE
   └─ Page d'accueil: Dernières consultations
   └─ Filtrage par LIBUTE: Selon rôle (ROLE_USER)
   └─ Statistiques: Pour ROLE_SUPER_ADMIN
```

---

## 🌐 Gestion du thème

### Thème clair/sombre
**Contrôleur:** `assets/controllers/theme_controller.js`

- Stockage localStorage
- Applique classe CSS sur `<html data-theme="light|dark">`
- Variables CSS pour couleurs (`--accent-color`, `--bg-secondary`, etc.)

**Fichier CSS:** `assets/styles/app.css`

---

## 🔍 Débogage

### Logs
```
var/log/dev.log
var/log/prod.log
```

### Profiler Symfony (dev)
Accessible dans barre noire en bas des pages

### Dump variables (dev)
```twig
{# Dans Twig #}
{{ dump(variable) }}
```

```php
// Dans PHP
dump($variable);
dd($variable); // dump + die
```

---

## 📞 Support et documentation

- **Symfony:** https://symfony.com/doc/current/
- **Doctrine:** https://www.doctrine-project.org/
- **Stimulus:** https://stimulus.hotwired.dev/
- **Twig:** https://twig.symfony.com/

---

**Dernière mise à jour:** 22 janvier 2026  
**Version Symfony:** 7.3  
**Environnement:** Development

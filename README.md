# Système de Gestion des Stocks et des Approvisionnements

## Description

Ce projet est un Système complet de Gestion des Stocks et des Approvisionnements développé avec Laravel, ReactJS et MySQL, destiné à gérer complètement un commerce avec des fonctionnalités avancées de gestion des stocks, ventes, achats, retours, dépenses, fournisseurs, clients, et la génération de rapports détaillés.

## Technologies Utilisées

### Backend

-   **Laravel** - Framework PHP
-   **PHP** - Langage de programmation
-   **MySQL** - Base de données

### Frontend

-   **ReactJS** - Framework JavaScript
-   **JavaScript** - Langage de programmation
-   **HTML5** - Langage de balisage
-   **CSS3** - Feuilles de style

## Prérequis

### Système

-   Un navigateur web
-   Un environnement d'exécution PHP (**XAMPP** ou **WAMP**)
-   **PHP** >= 7.4
-   **MySQL** >= 5.6
-   **Composer** >= 2.0

### Extensions PHP Requises

```bash
php-bcmath
php-ctype
php-fileinfo
php-json
php-mbstring
php-openssl
php-pdo
php-tokenizer
php-xml
```

## Structure du Projet

Arborescence typique des dossiers :

```
TEAM 462/
├── app/                          # Logique métier Laravel
│   ├── Console/                  # Commandes Artisan
│   ├── DTOs/                     # Data Transfer Objects
│   ├── Exceptions/               # Gestion des exceptions
│   ├── Exports/                  # Export de données
│   ├── Filters/                  # Filtres de données
│   ├── Http/                     # Contrôleurs et Middleware
│   │   ├── Controllers/          # Contrôleurs web
│   │   ├── API/                  # Contrôleurs API
│   │   └── Middleware/           # Middleware personnalisés
│   ├── Imports/                  # Import de données
│   ├── Mail/                     # Templates d'emails
│   ├── MediaLibrary/             # Gestion des médias
│   ├── Models/                   # Modèles Eloquent
│   │   ├── Contracts/            # Interfaces des modèles
│   └── Notifications/            # Notifications système
├── bootstrap/                    # Configuration de démarrage
├── config/                       # Fichiers de configuration
├── database/                     # Base de données
│   ├── factories/                # Factories pour les tests
│   ├── migrations/               # Migrations de base de données
│   └── seeders/                  # Seeders de données
├── lang/                         # Fichiers de traduction
├── public/                       # Fichiers publics
├── resources/                    # Ressources frontend
│   ├── css/                      # Styles CSS/SCSS
│   ├── js/                       # JavaScript/React
│   ├── pos/                      # Interface POS
│   └── views/                    # Vues Blade
├── routes/                       # Définition des routes
│   ├── api.php                   # Routes API
│   └── web.php                   # Routes web
├── storage/                      # Stockage des fichiers
└── tests/                        # Tests automatisés
```

## Base de Données

### Description

Le système utilise une base de données relationnelle avec les tables principales suivantes :

### Structure Principale

#### Gestion des Produits

-   `products` - Produits principaux
-   `variation_products` - Variations de produits
-   `product_categories` - Catégories de produits
-   `brands` - Marques
-   `units` - Unités de mesure
-   `base_units` - Unités de base

#### Gestion des Stocks

-   `warehouses` - Entrepôts
-   `adjustments` - Ajustements de stock

#### Gestion Commerciale

-   `sales` - Ventes
-   `purchases` - Achats
-   `quotations` - Devis

#### Gestion des Entités

-   `customers` - Clients
-   `suppliers` - Fournisseurs
-   `users` - Utilisateurs système
-   `roles` - Rôles utilisateurs
-   `permissions` - Permissions

#### Configuration

-   `settings` - Paramètres système
-   `currencies` - Devises

## Installation

**1. Téléchargez ou clonez le répertoire de ce projet sur votre ordinateur**

```bash
git clone https://github.com/AMANI-Jonam/Systeme-de-gestion-des-stocks-et-des-approvisionnements---TEAM-462.git
```

**2. Placez le répertoire du projet dans le répertoire `htdocs` (XAMP) ou `www` (WAMP)**

**3. Importez le fichier `pos.sql` à partir du dossier `TEAM 462/database/` via phpMyAdmin dans votre base de données MySQL**

**4. Configurez l'environnement en éditant le fichier `.env` avec vos identifiants :**

```env
APP_URL=https://votre-domaine

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=le_nom_de_votre_base_de_données
DB_USERNAME=votre_nom_d'utilisateur
DB_PASSWORD=votre_mot_depasse
```

**5. Configurez l'hôte virtuel**

Pour XAMPP (Windows)

1. Ouvrez le fichier `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
2. Ajoutez la configuration suivante :

```apache
<VirtualHost *:80>
	ServerName votre-domaine
	DocumentRoot "c:/xampp/htdocs/team 462"
	<Directory  "c:/xampp/htdocs/team 462/">
		AllowOverride All
		Require all granted
	</Directory>
</VirtualHost>
```

Pour WAMP (Windows)

1. Ouvrez le fichier `C:\wamp64\bin\apache\apache2.4.x\conf\extra\httpd-vhosts.conf`
2. Ajoutez la configuration suivante :

```apache
<VirtualHost *:80>
	ServerName votre-domaine
	DocumentRoot "c:/wamp64/www/team 462"
	<Directory  "c:/wamp64/www/team 462/">
		Options +Indexes +Includes +FollowSymLinks +MultiViews
		AllowOverride All
		Require all granted
	</Directory>
</VirtualHost>
```

En suite, redémarrez les services dans XAMPP/WAMP

**6. Exécutez l'application**

Option 1 : Via l'hôte virtuel

-   Accédez à `http://votre-domaine` dans votre navigateur
-   L'application sera accessible via votre domaine configuré

Option 2 : Via le serveur Laravel

```bash
# Dans le répertoire du projet
php artisan serve
# Puis accédez à http://localhost:8000
```

**7. Accès à l'Application**

Utilisez les informations d'administrateur par défaut

-   **Email** : `admin@standard.com`
-   **Mot de passe** : `012345`

## Fonctionnalités Principales

-   **Gestion des Produits** : Catalogue complet avec variations, catégories, marques
-   **Point de Vente (POS)** : Interface moderne pour les ventes en magasin
-   **Gestion des Stocks** : Suivi en temps réel, alertes de stock bas
-   **Achats & Fournisseurs** : Gestion complète des achats et retours
-   **Ventes & Clients** : Historique des ventes, gestion des clients
-   **Rapports Avancés** : Tableaux de bord, analyses de ventes
-   **Gestion Multi-entrepôts** : Support de plusieurs entrepôts
-   **Système de Permissions** : Contrôle d'accès granulaire

## Architecture Générale

Cette application utilise une architecture MVC/API (Laravel + React) basée sur une architecture MVC/API moderne :

### Backend (Laravel)

-   **Modèles Laravel (Eloquent)** : Gestion des entités de base de données
-   **Contrôleurs** : Logique CRUD et traitement des requêtes
-   **Routes API** : Endpoints RESTful utilisés par le Frontend React
-   **Middleware** : Authentification, permissions, validation

### Frontend (React)

-   **Vues/JS ReactJS** : Interface utilisateur dynamique et responsive
-   **Components** : Architecture modulaire et réutilisable
-   **State Management** : Redux pour la gestion d'état globale
-   **API Integration** : Communication avec le backend Laravel

## Contribution

### Comment Contribuer

1. Forkez le projet depuis GitHub

    ```bash
    git clone https://github.com/AMANI-Jonam/Systeme-de-gestion-des-stocks-et-des-approvisionnements---TEAM-462.git
    cd Systeme-de-gestion-des-stocks-et-des-approvisionnements---TEAM-462
    ```

2. Créez une nouvelle branche pour votre fonctionnalité

    ```bash
    git checkout -b feature/nom-de-la-fonctionnalite
    ```

3. Ajoutez, committez et poussez vos modifications

    ```bash
    git add .
    git commit -m "Ajout : nouvelle fonctionnalité"
    git push origin feature/nouvelle-fonctionnalite
    ```

4. Créez une Pull Request depuis GitHub pour proposer vos modifications

## Équipe de Développement

Ce système de gestion des stocks et des approvisionnements a été conçu et développé dans le cadre d’un projet de mémoire de fin d’études par la **TEAM 462**, composée d'étudiants en dernière année de Licence en Informatique appliquée à la gestion des entreprises.

### Membres du Projet

-   **AMANI BITHA Jonathan** - Analyste fonctionnel
-   **BAHADOOR MULOHWE** - Développeur Frontend
-   **KASONGO DJAMBA Raphaël** - Développeur Backend
-   **KIBANJU KABANGWA Robert** - Analyste fonctionnel
-   **SHATI SHAMAVU Herman** - Responsable des tests, de la qualité et de la documentation

---

Ce projet représente l’aboutissement de notre parcours académique, et nous sommes heureux de le partager avec vous.

_Merci de votre intérêt pour ce projet !_

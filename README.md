# symfoLivre

Plateforme de bibliothèque numérique développée avec **Symfony 7**. Permet à des auteurs de publier des livres (texte ou audio), aux lecteurs de les consulter, les lire en ligne, les télécharger et constituer un panier de lecture, et aux administrateurs de gérer les catégories et les comptes utilisateurs.

## Stack technique

- PHP 8.2+
- Symfony 7
- Doctrine ORM / MariaDB (ou MySQL)
- Twig + Bootstrap 5 + Font Awesome
- Symfony CLI (recommandé pour le serveur de dev)

## Prérequis

- PHP >= 8.2 avec les extensions `pdo_mysql`, `intl`, `mbstring`
- Composer 2
- Un serveur MariaDB ou MySQL (ex. via XAMPP en local)
- [Symfony CLI](https://symfony.com/download) (optionnel mais conseillé)

## Installation

```bash
git clone <url-du-depot> symfoLivre
cd symfoLivre
composer install
```

### Configuration de la base de données

Copier `.env` en `.env.local` et adapter l'URL de connexion :

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/symfolivre?serverVersion=8.0&charset=utf8mb4"
```

Créer la base et exécuter les migrations :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Chargement des données de démonstration (fixtures)

```bash
php bin/console doctrine:fixtures:load
```

> ⚠️ Cette commande **vide et recharge** la base. Les comptes de démonstration (admin, auteur, lecteur) et leur mot de passe sont définis dans `src/DataFixtures/AppFixtures.php` — consulter ce fichier pour les identifiants exacts avant la première connexion.

### Lancement du serveur

```bash
symfony server:start
```
ou, sans Symfony CLI :
```bash
php -S 127.0.0.1:8000 -t public
```

L'application est ensuite accessible sur **http://127.0.0.1:8000**.

## Rôles et permissions

| Rôle | Description |
|---|---|
| `ROLE_USER` (Lecteur) | Consulter, lire en ligne et télécharger les livres, gérer son panier de lecture, modifier son profil |
| `ROLE_AUTEUR` | Hérite de `ROLE_USER` + publier/modifier/supprimer ses propres livres |
| `ROLE_ADMIN` | Hérite de tout + gérer les catégories et les comptes utilisateurs (`/admin/users`) |

La hiérarchie des rôles est définie dans `config/packages/security.yaml`.

## API

Une API en lecture seule permet d'interroger le catalogue :

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/api/books?title=...` | Recherche par titre (partielle) |
| `GET` | `/api/books?isbn=...` | Recherche par ISBN (exacte) |
| `GET` | `/api/books/{id}` | Détail d'un livre |

Réponse au format JSON, publique (pas d'authentification requise).

## Structure du projet

src/
├── Controller/
│ ├── Admin/UserController.php # gestion des utilisateurs (admin)
│ ├── Api/BookApiController.php # API de recherche
│ ├── BookController.php
│ ├── CategoryController.php
│ ├── ProfileController.php # "Mon profil"
│ ├── ReadingBasketController.php
│ ├── RegisterController.php
│ └── SecurityController.php
├── Entity/ # User, Book, Category, ReadingBasket
├── Form/
├── Repository/
templates/
├── admin/user/
├── book/
├── category/
├── profile/
├── reading_basket/
└── base.html.twig

## Tests manuels rapides

```bash
php bin/console debug:router          # vérifier que toutes les routes sont bien enregistrées
php bin/console doctrine:schema:validate
```

## Licence

Apache 2.0
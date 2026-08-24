# Dictionnaire de Données - symfoLivre

Ce document recense l'ensemble des données manipulées par l'application `symfoLivre`.

## 1. Entité : Utilisateur (`User`)
L'utilisateur peut être un Lecteur, un Auteur ou un Administrateur.

| Champ | Description | Type | Contraintes | Note |
|---|---|---|---|---|
| `id` | Identifiant unique de l'utilisateur | Integer | PK, Auto-increment | |
| `email` | Adresse email de connexion | String | Unique, Not Null | Utilisé comme login |
| `password` | Mot de passe haché | String | Not Null | Format BCrypt/Argon2 |
| `firstname` | Prénom de l'utilisateur | String | Not Null | |
| `lastname` | Nom de l'utilisateur | String | Not Null | |
| `roles` | Rôles attribués à l'utilisateur | JSON / Array | Not Null | ex: `ROLE_USER`, `ROLE_AUTEUR`, `ROLE_ADMIN` |
| `created_at` | Date de création du compte | DateTime | Not Null | |
| `updated_at` | Date de dernière modification | DateTime | - | |

## 2. Entité : Livre (`Book`)
Représente un ouvrage numérique (texte ou audio).

| Champ | Description | Type | Contraintes | Note |
|---|---|---|---|---|
| `id` | Identifiant unique du livre | Integer | PK, Auto-increment | |
| `title` | Titre de l'ouvrage | String | Not Null | |
| `isbn` | Numéro ISBN international | String | Unique, Not Null | Format 13 chiffres |
| `summary` | Résumé/Description du livre | Text | - | |
| `publication_date` | Date de publication originale | Date | - | |
| `type` | Nature du contenu | Enum / String | Not Null | Valeurs : `TEXTE`, `AUDIO` |
| `file_path` | Chemin vers le fichier sur le serveur | String | Not Null | Ex: `/uploads/books/livre1.md` |
| `created_at` | Date d'ajout au site | DateTime | Not Null | |
| `updated_at` | Date de mise à jour | DateTime | - | |

## 3. Entité : Catégorie (`Category`)
Permet le classement thématique des livres.

| Champ | Description | Type | Contraintes | Note |
|---|---|---|---|---|
| `id` | Identifiant unique de la catégorie | Integer | PK, Auto-increment | |
| `label` | Nom de la catégorie | String | Unique, Not Null | Ex: "Science-Fiction", "Histoire" |
| `description` | Description courte de la thématique | Text | - | |

## 4. Entité : Panier de lecture (`ReadingBasket`)
Table d'association entre l'utilisateur et les livres qu'il a sauvegardés.

| Champ | Description | Type | Contraintes | Note |
|---|---|---|---|---|
| `id` | Identifiant unique de l'entrée | Integer | PK, Auto-increment | |
| `user_id` | Référence à l'utilisateur | Integer | FK, Not Null | Lien vers `User.id` |
| `book_id` | Référence au livre | Integer | FK, Not Null | Lien vers `Book.id` |
| `added_at` | Date d'ajout au panier | DateTime | Not Null | |
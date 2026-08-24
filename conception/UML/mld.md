# Modèle Logique de Données (MLD) - symfoLivre

## 1. Schéma Relationnel (Notation Textuelle)

**users** (<u>id</u>, email, password, firstname, lastname, roles, created_at, updated_at)
- `id` : PK

**categories** (<u>id</u>, label, description)
- `id` : PK

**books** (<u>id</u>, title, isbn, summary, publication_date, type, file_path, created_at, updated_at, #author_id, #category_id)
- `id` : PK
- `author_id` : FK $\rightarrow$ **users(id)**
- `category_id` : FK $\rightarrow$ **categories(id)**

**reading_basket** (<u>#user_id, #book_id</u>, added_at)
- `user_id` : PK, FK $\rightarrow$ **users(id)**
- `book_id` : PK, FK $\rightarrow$ **books(id)**

---

## 2. Détails Techniques des Tables

### Table : `users`
| Colonne | Type | Contraintes | Note |
|---|---|---|---|
| `id` | INT | PK, AI | Identifiant unique |
| `email` | VARCHAR(180) | Unique, Not Null | Utilisé pour l'authentification |
| `password` | VARCHAR(255) | Not Null | Mot de passe haché |
| `firstname` | VARCHAR(50) | Not Null | |
| `lastname` | VARCHAR(50) | Not Null | |
| `roles` | JSON | Not Null | Stockage des rôles (ex: ["ROLE_USER"]) |
| `created_at` | DATETIME | Not Null | |
| `updated_at` | DATETIME | - | |

### Table : `categories`
| Colonne | Type | Contraintes | Note |
|---|---|---|---|
| `id` | INT | PK, AI | Identifiant unique |
| `label` | VARCHAR(100) | Unique, Not Null | Nom de la catégorie |
| `description` | TEXT | - | Description de la thématique |

### Table : `books`
| Colonne | Type | Contraintes | Note |
|---|---|---|---|
| `id` | INT | PK, AI | Identifiant unique |
| `title` | VARCHAR(255) | Not Null | |
| `isbn` | VARCHAR(20) | Unique, Not Null | |
| `summary` | TEXT | - | |
| `publication_date` | DATE | - | |
| `type` | VARCHAR(10) | Not Null | 'TEXTE' ou 'AUDIO' |
| `file_path` | VARCHAR(255) | Not Null | Chemin vers le fichier serveur |
| `created_at` | DATETIME | Not Null | |
| `updated_at` | DATETIME | - | |
| `author_id` | INT | FK, Not Null | Référence à **users.id** |
| `category_id` | INT | FK, Not Null | Référence à **categories.id** |

### Table : `reading_basket` (Table Pivot)
| Colonne | Type | Contraintes | Note |
|---|---|---|---|
| `user_id` | INT | PK, FK, Not Null | Référence à **users.id** |
| `book_id` | INT | PK, FK, Not Null | Référence à **books.id** |
| `added_at` | DATETIME | Not Null | Date d'ajout au panier |
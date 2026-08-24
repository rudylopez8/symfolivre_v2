# Compilation des Diagrammes PlantUML

Ce fichier contient 12 diagrammes.

---

## Source : `Connexion.puml`

```puml
@startuml --- Module Auth: Connexion ---
title Processus d'Authentification

|Utilisateur|
start
:Saisit Email et Mot de passe;
:Clique sur "Se connecter";

|Serveur|
:Reçoit les identifiants;
|Base de données|
:Recherche l'utilisateur par email;
if (Utilisateur trouvé ?) then (Oui)
    |Serveur|
    :Vérifie la correspondance du mot de passe haché;
    if (Mot de passe correct ?) then (Oui)
        :Génère la session utilisateur;
        :Récupère les rôles associés;
        :Redirige vers la page d'accueil / dashboard;
        |Utilisateur|
        :Accède à son espace personnel;
        stop
    else (Non)
        :Retourne erreur "Identifiants incorrects";
        |Utilisateur|
        :Voit le message d'erreur;
        stop
    endif
else (Non)
    |Serveur|
    :Retourne erreur "Identifiants incorrects";
    |Utilisateur|
    :Voit le message d'erreur;
    stop
endif
@enduml
```

---

## Source : `Gestion_Categories.puml`

```puml
@startuml --- Module Admin: Gestion Catégories ---
title Gestion des catégories de livres

|Administrateur|
start
:Accède à "Gestion des Catégories";
if (Action == "Créer" ?) then (Oui)
    :Saisit le nom de la catégorie;
    :Valide;
    |Serveur|
    :Vérifie session ROLE_ADMIN;
    |Base de données|
    :Insère la nouvelle catégorie;
else (Action == "Supprimer" ?)
    :Sélectionne une catégorie et clique sur "Supprimer";
    |Serveur|
    :Vérifie session ROLE_ADMIN;
    |Base de données|
    :Supprime la catégorie / Déplace les livres vers "Sans Catégorie";
endif
|Serveur|
:Confirme l'opération;
|Administrateur|
:Voit la liste des catégories à jour;
stop
@enduml
```

---

## Source : `gestion_panier.puml`

```puml
@startuml --- Module Consultation: Panier de lecture ---
title --- Module Consultation: Gestion du panier de lecture (Ajout et Suppression) ---

|Lecteur/Auteur|
start
:Accède à la fiche d'un livre;
if (Action == "Ajouter au panier" ?) then (Oui)
    |Serveur|
    :Vérifie la session;
    if (Session valide ?) then (Oui)
        |Base de données|
        :Vérifie existence du lien [Utilisateur <-> Livre];
        if (Lien existant ?) then (Non)
            :Crée l'association dans la table Panier;
            |Serveur|
            :Retourne "Ajouté avec succès";
        else (Oui)
            |Serveur|
            :Retourne "Déjà dans le panier";
        endif
    else (Non)
        :Redirige vers connexion;
    endif
else (Action == "Supprimer du panier" ?)
    |Serveur|
    :Vérifie la session;
    if (Session valide ?) then (Oui)
        |Base de données|
        :Supprime l'association [Utilisateur <-> Livre];
        |Serveur|
        :Retourne "Livre retiré du panier";
    else (Non)
        :Redirige vers connexion;
    endif
endif
|Lecteur/Auteur|
:Voit la mise à jour de son panier;
stop
@enduml
```

---

## Source : `Gestion_Utilisateur_et_Rôle.puml`

```puml
@startuml --- Module Admin: Gestion Utilisateurs & Rôles ---
title Administration des comptes et rôles

|Administrateur|
start
:Accède au panel "Gestion Utilisateurs";
:Sélectionne un utilisateur;
if (Action == "Modifier Rôle" ?) then (Oui)
    :Choisit le nouveau rôle (Lecteur, Auteur ou Admin);
    :Valide le changement;
    
    |Serveur|
    :Vérifie session ROLE_ADMIN;
    if (Admin ?) then (Oui)
        |Base de données|
        :Met à jour le champ 'roles' de l'utilisateur;
        |Serveur|
        :Confirme le changement de rôle;
    else (Non)
        :Erreur 403;
    endif
else (Action == "Supprimer Utilisateur" ?)
    :Clique sur "Supprimer";
    
    |Serveur|
    :Vérifie session ROLE_ADMIN;
    if (Admin ?) then (Oui)
        |Base de données|
        :Supprime l'utilisateur et ses liens panier;
        |Serveur|
        :Confirme la suppression;
    else (Non)
        :Erreur 403;
    endif
endif
|Administrateur|
:Voit la liste des utilisateurs mise à jour;
stop
@enduml
```

---

## Source : `Inscription.puml`

```puml
@startuml --- Module Auth: Inscription ---
title Inscription d'un nouvel utilisateur

|Visiteur|
start
:Clique sur "S'inscrire";
:Choisit le type de compte (Lecteur ou Auteur);
:Remplit le formulaire (Email, Mot de passe, Nom...);
:Valide l'inscription;

|Serveur|
:Reçoit les données d'inscription;
if (L'email est-il déjà utilisé ?) then (Oui)
    :Retourne erreur "Email déjà existant";
    |Visiteur|
    :Voit le message d'erreur;
    stop
else (Non)
    :Hache le mot de passe (BCrypt/Argon2);
    :Attribue le rôle correspondant (ROLE_USER / ROLE_AUTEUR);
    |Base de données|
    :Enregistre l'utilisateur;
    |Serveur|
    :Confirme la création du compte;
    |Visiteur|
    :Reçoit la confirmation et est redirigé vers Login;
    stop
endif
@enduml
```

---

## Source : `Lectur_en_ligne.puml`

```puml
@startuml --- Module Consultation: Lecture en ligne ---
title Lecture d'un livre texte (.txt / .md)

|Lecteur/Auteur|
start
:Consulte la fiche d'un livre;
if (Type du livre == "Texte" ?) then (Oui)
    :Clique sur "Lire en ligne";
    
    |Serveur|
    :Vérifie la session;
    if (Session valide ?) then (Oui)
        :Récupère le chemin du fichier .txt ou .md;
        |Système de Fichiers|
        :Lit le contenu du fichier;
        |Serveur|
        :Injecte le texte dans un template de lecture;
        :Envoie la page au navigateur;
        |Lecteur/Auteur|
        :Lit l'ouvrage sur le site;
        stop
    else (Non)
        :Redirige vers connexion;
        stop
    endif
else (Non)
    |Lecteur/Auteur|
    :Le bouton "Lire en ligne" est désactivé;
    :L'utilisateur doit télécharger le .zip audio;
    stop
endif
@enduml
```

---

## Source : `Modification.puml`

```puml
@startuml --- Module Profil: Modification ---
title Mise à jour du profil utilisateur

|Lecteur/Auteur|
start
:Accède à "Mon Profil";
:Modifie ses informations personnelles;
:Clique sur "Enregistrer";

|Serveur|
:Vérifie la session;
if (Session valide ?) then (Oui)
    :Valide les données saisies;
    if (Données valides ?) then (Oui)
        |Base de données|
        :Met à jour les informations de l'utilisateur;
        |Serveur|
        :Confirme la modification;
        |Lecteur/Auteur|
        :Voit le message "Profil mis à jour";
        stop
    else (Non)
        |Serveur|
        :Retourne erreur de validation;
        |Lecteur/Auteur|
        :Corrige les erreurs;
        stop
    endif
else (Non)
    :Redirige vers connexion;
    stop
endif
@enduml
```

---

## Source : `Modification_livre.puml`

```puml
@startuml --- Module Auteur: Modification Livre ---
title Modification d'un livre par l'auteur

|Auteur|
start
:Accède à "Mes Livres";
:Sélectionne un livre et clique sur "Modifier";

|Serveur|
:Vérifie la session;
if (Rôle == ROLE_AUTEUR ?) then (Oui)
    |Base de données|
    :Vérifie si l'auteur est le propriétaire du livre;
    if (Propriétaire ?) then (Oui)
        |Serveur|
        :Affiche le formulaire avec les données actuelles;
        |Auteur|
        :Modifie les métadonnées ou remplace le fichier;
        :Valide les modifications;
        
        |Serveur|
        :Valide les nouvelles données/extension fichier;
        if (Données valides ?) then (Oui)
            if (Fichier remplacé ?) then (Oui)
                |Système de Fichiers|
                :Supprime l'ancien fichier;
                :Enregistre le nouveau fichier;
            endif
            |Base de données|
            :Met à jour les métadonnées en DB;
            |Serveur|
            :Confirme la modification;
            |Auteur|
            :Voit le livre mis à jour;
            stop
        else (Non)
            |Serveur|
            :Retourne erreur de validation;
            |Auteur|
            :Corrige les erreurs;
            stop
        endif
    else (Non)
        |Serveur|
        :Retourne erreur 403 "Action interdite";
        stop
    endif
else (Non)
    :Redirige vers connexion;
    stop
endif
@enduml
```

---

## Source : `Modération_livres.puml`

```puml
@startuml  --- Module Admin: Modération Livres ---
title Modération et Suppression d'un livre par l'Admin

|Administrateur|
start
:Accède à l'interface de gestion des livres;

|Serveur|
:Vérifie la session;
if (Rôle == ROLE_ADMIN ?) then (Oui)
    :Charge la liste complète des livres;
    :Envoie la page;
    |Administrateur|
    :Sélectionne un livre à supprimer;
    :Clique sur "Supprimer définitivement";

    |Serveur|
    :Reçoit la demande de suppression;
    :Récupère le chemin du fichier associé;

    |Système de Fichiers|
    :Supprime physiquement le fichier (.txt, .md ou .zip);

    |Base de données|
    :Supprime l'entrée correspondante dans la table Livres;
    :Supprime les liens dans le panier de lecture;

    |Serveur|
    :Confirme la suppression;
    |Administrateur|
    :Voit le livre disparaître de la liste;
    stop
else (Non)
    :Retourne erreur 403;
    |Administrateur|
    :Voit message "Accès réservé à l'administrateur";
    stop
endif
@enduml
```

---

## Source : `Recherche_et_téléchargement.puml`

```puml
@startuml  --- Module Consultation: Recherche et Téléchargement --- 
title  --- Module Consultation: Recherche et Téléchargement d'un livre ---

|Utilisateur|
start
:Saisit un mot-clé dans la barre de recherche;
:Clique sur "Rechercher";

|Serveur|
:Reçoit la requête de recherche;
:Construit la requête SQL (Titre/Auteur/Catégorie);

|Base de données|
:Exécute la recherche;
:Retourne la liste des livres correspondants;

|Serveur|
:Génère la page de résultats;
:Envoie la page;

|Utilisateur|
:Consulte la liste;
:Sélectionne un livre et clique sur "Détails";

|Serveur|
:Récupère les infos complètes du livre (ISBN, résumé...);
:Envoie la page de détails;

|Utilisateur|
:Clique sur "Télécharger le livre";

|Serveur|
:Vérifie la session utilisateur;
if (Utilisateur authentifié ?) then (Oui)
    :Vérifie le chemin du fichier sur le serveur;
    if (Fichier existant ?) then (Oui)
        :Prépare le flux de téléchargement (.txt, .md ou .zip);
        |Utilisateur|
        :Reçoit et enregistre le fichier;
        stop
    else (Non)
        |Serveur|
        :Retourne erreur "Fichier introuvable";
        |Utilisateur|
        :Voit message d'erreur;
        stop
    endif
else (Non)
    :Redirige vers la page de connexion;
    |Utilisateur|
    :Voit l'invitation à s'inscrire/se connecter;
    stop
endif
@enduml
```

---

## Source : `Recherche_exhaustive.puml`

```puml
@startuml --- Module API: Recherche exhaustive ---
title Flux détaillé de l'API de recherche

|Client API|
start
:Envoie requête GET /api/books (paramètre titre ou isbn);

|Serveur|
:Intercepte la demande via le routeur Symfony;
:Vérifie si le paramètre est un ISBN (format 13 chiffres) ou un Titre;

if (Paramètre valide ?) then (Oui)
    |Base de données|
    :Exécute requête SQL :
    SELECT * FROM livre WHERE isbn = ? OR titre LIKE ?;
    :Retourne le résultat;
    
    |Serveur|
    if (Résultat trouvé ?) then (Oui)
        :Transforme l'entité Livre en format JSON via Serializer;
        :Envoie réponse HTTP 200 OK + Body JSON;
    else (Non)
        :Prépare message "Livre non trouvé";
        :Envoie réponse HTTP 404 Not Found;
    endif
else (Non)
    |Serveur|
    :Prépare erreur "Paramètre invalide";
    :Envoie réponse HTTP 400 Bad Request;
endif

|Client API|
:Traite la réponse JSON ou l'erreur;
stop
@enduml
```

---

## Source : `Téléversement_livre.puml`

```puml
@startuml --- Téléversement Auteur: Ajouter un livre ---
title Téléversement d'un livre par un Auteur

|Auteur|
start
:Accède à la page "Ajouter un livre";

|Serveur|
:Vérifie la session;
if (Rôle == ROLE_AUTEUR ou ROLE_ADMIN ?) then (Oui)
    :Affiche le formulaire de dépôt;
    |Auteur|
    :Saisit les métadonnées (Titre, ISBN...);
    :Sélectionne le fichier (.txt, .md ou .zip);
    :Valide le formulaire;

    |Serveur|
    :Reçoit les données et le fichier;
    :Vérifie l'extension du fichier;
    if (Extension valide ?) then (Oui)
        :Génère un nom de fichier unique;
        :Déplace le fichier vers le stockage serveur;
        
        |Base de données|
        :Enregistre les métadonnées et le chemin du fichier;
        :Liaise le livre à l'auteur actuel;
        
        |Serveur|
        :Confirme le succès de l'opération;
        |Auteur|
        :Voit le message de confirmation;
        stop
    else (Non)
        |Serveur|
        :Retourne erreur "Format de fichier non supporté";
        |Auteur|
        :Voit le message d'erreur;
        stop
    endif
else (Non)
    :Retourne erreur 403 (Accès refusé);
    |Auteur|
    :Voit message "Action interdite";
    stop
endif
@enduml
```

---


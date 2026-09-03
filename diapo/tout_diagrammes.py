import os
import sys

# Nom du fichier de sortie (passé en argument ou valeur par défaut)
nom_sortie = sys.argv[1] if len(sys.argv) > 1 else "liste_fichiers.txt"

if not nom_sortie.endswith(".txt"):
    nom_sortie += ".txt"

# Liste tous les fichiers du dossier avec leur extension
fichiers = [f for f in os.listdir(".") if os.path.isfile(f)]

# Écrit la liste dans le fichier texte, séparée par des espaces
with open(nom_sortie, "w", encoding="utf-8") as f:
    f.write(" ".join(fichiers))
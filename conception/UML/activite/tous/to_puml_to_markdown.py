from pathlib import Path

def fusionner_puml_en_md(nom_sortie="global_diagrams.md"):
    # On cible le répertoire actuel
    chemin_actuel = Path('.')
    # On récupère tous les fichiers .puml
    fichiers_puml = list(chemin_actuel.glob('*.puml'))

    if not fichiers_puml:
        print("Aucun fichier .puml n'a été trouvé dans ce dossier.")
        return

    with open(nom_sortie, 'w', encoding='utf-8') as f_out:
        f_out.write(f"# Compilation des Diagrammes PlantUML\n\n")
        f_out.write(f"Ce fichier contient {len(fichiers_puml)} diagrammes.\n\n---\n\n")

        for fichier in fichiers_puml:
            print(f"Lecture de : {fichier.name}...")
            
            # Lecture du contenu du fichier .puml
            contenu = fichier.read_text(encoding='utf-8')

            # Écriture dans le fichier Markdown avec formatage
            f_out.write(f"## Source : `{fichier.name}`\n\n")
            f_out.write("```puml\n")
            f_out.write(contenu.strip()) # strip() pour éviter les sauts de ligne inutiles
            f_out.write("\n```\n\n")
            f_out.write("---\n\n")

    print(f"\n Succès ! Tout a été copié dans : {nom_sortie}")

if __name__ == "__main__":
    fusionner_puml_en_md()
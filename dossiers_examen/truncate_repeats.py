#!/usr/bin/env python3
"""
truncate_repeats.py — Réduit les longues suites de caractères répétés dans un fichier Markdown.
"""

import argparse
import re
import sys
from pathlib import Path


def truncate_repeated_chars(text: str, max_repeat: int = 4) -> str:
    """
    Réduit les répétitions contiguës de même caractère à `max_repeat` occurrences.
    Presserve les sauts de ligne, les espaces et les chiffres.
    """
    pattern = re.compile(r'([^\s\d])\1{' + str(max_repeat) + r',}')
    replacement = r'\1' * max_repeat
    return pattern.sub(replacement, text)


def main():
    parser = argparse.ArgumentParser(
        description="Réduit les suites de caractères répétés sans toucher aux chiffres ni aux sauts de ligne."
    )
    parser.add_argument("input", help="Fichier .md d'entrée")
    parser.add_argument("-o", "--output", default=None, help="Fichier de sortie (défaut: écrase le fichier d'entrée)")
    parser.add_argument("-m", "--max", type=int, default=4, help="Limite de répétition (défaut: 4)")

    args = parser.parse_args()

    in_path = Path(args.input)
    if not in_path.exists():
        sys.exit(f"Erreur : fichier introuvable → {in_path}")

    content = in_path.read_text(encoding="utf-8")
    cleaned_content = truncate_repeated_chars(content, max_repeat=args.max)

    out_path = Path(args.output) if args.output else in_path
    out_path.write_text(cleaned_content, encoding="utf-8")

    print(f"✅ Traitement terminé : {out_path} (limite : {args.max})")


if __name__ == "__main__":
    main()
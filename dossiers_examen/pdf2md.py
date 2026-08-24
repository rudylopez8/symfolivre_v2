#!/usr/bin/env python3
"""
pdf2md.py — Convertit un fichier PDF en Markdown (.md) ou en texte brut (.txt).

Usage:
    python pdf2md.py document.pdf
    python pdf2md.py document.pdf -o sortie.md
    python pdf2md.py document.pdf -f txt
    python pdf2md.py document.pdf --extract-images --img-dir ./images

Dépendances recommandées :
    pip install PyMuPDF pdfplumber pypdf
"""

import argparse
import re
import sys
from pathlib import Path


# ──────────────────────────────────────────────
#  Moteurs d'extraction
# ──────────────────────────────────────────────

def extract_with_pymupdf(pdf_path: Path, extract_images: bool = False, image_dir: Path = None) -> str:
    """
    Extrait le contenu et reconstitue la structure Markdown via PyMuPDF (fitz).
    Gère la détection des titres, du gras/italique et l'extraction des images.
    """
    import fitz  # PyMuPDF

    doc = fitz.open(pdf_path)
    md_pages = []

    if extract_images and image_dir:
        image_dir.mkdir(parents=True, exist_ok=True)

    for page_num, page in enumerate(doc, start=1):
        page_md = []

        # 1. Extraction des images si demandée
        if extract_images and image_dir:
            image_list = page.get_images(full=True)
            for img_index, img in enumerate(image_list, start=1):
                xref = img[0]
                base_image = doc.extract_image(xref)
                image_bytes = base_image["image"]
                image_ext = base_image["ext"]
                img_filename = f"page_{page_num}_img_{img_index}.{image_ext}"
                img_path = image_dir / img_filename
                img_path.write_bytes(image_bytes)
                page_md.append(f"![Image {page_num}-{img_index}]({image_dir.name}/{img_filename})\n")

        # 2. Extraction du texte structuré sous forme de blocs
        blocks = page.get_text("dict")["blocks"]

        for b in blocks:
            if b.get("type") == 0:  # Bloc de texte
                block_lines = []
                for line in b["lines"]:
                    line_text = ""
                    for span in line["spans"]:
                        text = span["text"]
                        flags = span["flags"]
                        size = span["size"]

                        # Style : gras / italique via drapeaux typographiques
                        is_bold = bool(flags & 2 ** 4) or "bold" in span["font"].lower()
                        is_italic = bool(flags & 2 ** 1) or "italic" in span["font"].lower()

                        if is_bold and is_italic:
                            text = f"***{text}***"
                        elif is_bold:
                            text = f"**{text}**"
                        elif is_italic:
                            text = f"*{text}*"

                        # Détection indicative des titres selon la taille de police
                        if size > 18:
                            text = f"# {text}"
                        elif size > 14:
                            text = f"## {text}"
                        elif size > 12:
                            text = f"### {text}"

                        line_text += text

                    block_lines.append(line_text)

                page_md.append("\n".join(block_lines))

        md_pages.append("\n\n".join(page_md))

    doc.close()

    # Insertion du séparateur de page compatible avec md2pdf.py
    return "\n\n---PAGE---\n\n".join(md_pages)


def extract_with_pdfplumber(pdf_path: Path) -> str:
    """
    Extrait le texte et reconstitue les tableaux Markdown (GFM) via pdfplumber.
    """
    import pdfplumber

    md_pages = []
    with pdfplumber.open(pdf_path) as pdf:
        for page in pdf.pages:
            page_text = []

            # Extraction des tableaux et conversion en syntaxe GFM Markdown
            tables = page.extract_tables()
            if tables:
                for table in tables:
                    table_md = []
                    clean_table = [[(cell or "").replace("\n", " ").strip() for cell in row] for row in table]
                    if clean_table:
                        # En-tête
                        header = clean_table[0]
                        table_md.append("| " + " | ".join(header) + " |")
                        table_md.append("| " + " | ".join(["---"] * len(header)) + " |")
                        # Lignes de données
                        for row in clean_table[1:]:
                            table_md.append("| " + " | ".join(row) + " |")
                        page_text.append("\n".join(table_md))

            # Extraction du texte courant
            text = page.extract_text()
            if text:
                page_text.append(text)

            md_pages.append("\n\n".join(page_text))

    return "\n\n---PAGE---\n\n".join(md_pages)


def extract_with_pypdf(pdf_path: Path) -> str:
    """
    Extraction basique / secours sans dépendances C secondaires.
    """
    import pypdf

    reader = pypdf.PdfReader(pdf_path)
    pages_text = []
    for page in reader.pages:
        text = page.extract_text() or ""
        pages_text.append(text)

    return "\n\n---PAGE---\n\n".join(pages_text)


# ──────────────────────────────────────────────
#  Post-traitement et nettoyage
# ──────────────────────────────────────────────

def post_process_markdown(text: str) -> str:
    """Ajuste la syntaxe Markdown extraite pour la rendre plus propre."""
    # Nettoyage des espaces superflus autour des balises
    text = re.sub(r'\*\*\s+', ' **', text)
    text = re.sub(r'\s+\*\*', '** ', text)

    # Reconstitution des encarts d'origine si détectés
    text = re.sub(r'(?:ℹ\s*Note\s*:\s*)(.*)', r'---NOTE: \1 ---', text)
    text = re.sub(r'(?:⚠\s*Attention\s*:\s*)(.*)', r'---WARNING: \1 ---', text)

    # Réduction des retours à la ligne multiples
    text = re.sub(r'\n{3,}', '\n\n', text)
    return text.strip()


# ──────────────────────────────────────────────
#  Fonction Principale
# ──────────────────────────────────────────────

def convert_pdf_to_md(
    pdf_path: Path,
    output_format: str = "md",
    engine: str = "auto",
    extract_images: bool = False,
    image_dir: Path = None
) -> str:
    """Sélectionne le meilleur moteur disponible et extrait le texte."""
    
    # Sélection automatique du moteur en fonction des paquets installés
    if engine == "auto":
        try:
            import fitz
            engine = "pymupdf"
        except ImportError:
            try:
                import pdfplumber
                engine = "pdfplumber"
            except ImportError:
                engine = "pypdf"

    if engine in ("pymupdf", "fitz"):
        content = extract_with_pymupdf(pdf_path, extract_images, image_dir)
    elif engine == "pdfplumber":
        content = extract_with_pdfplumber(pdf_path)
    elif engine == "pypdf":
        content = extract_with_pypdf(pdf_path)
    else:
        raise ValueError(f"Moteur inconnu : {engine}")

    if output_format == "md":
        content = post_process_markdown(content)
    else:
        # En mode TXT brut : suppression des balises Markdown basiques
        content = re.sub(r'[#*`_~]', '', content)

    return content


def main():
    parser = argparse.ArgumentParser(
        description="Convertit un fichier PDF en Markdown (.md) ou en texte brut (.txt).",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""Exemples :
  python pdf2md.py document.pdf
  python pdf2md.py document.pdf -o sortie.md
  python pdf2md.py document.pdf -f txt -o texte_brut.txt
  python pdf2md.py document.pdf --extract-images --img-dir ./images_doc
"""
    )
    parser.add_argument("input", help="Fichier .pdf d'entrée")
    parser.add_argument("-o", "--output", default=None, help="Fichier de sortie (défaut: <input>.md)")
    parser.add_argument("-f", "--format", choices=["md", "txt"], default="md", help="Format de sortie (défaut: md)")
    parser.add_argument(
        "--engine",
        choices=["auto", "pymupdf", "pdfplumber", "pypdf"],
        default="auto",
        help="Moteur d'extraction à forcer (défaut: auto)"
    )
    parser.add_argument("--extract-images", action="store_true", help="Extrait et insère les images dans le Markdown")
    parser.add_argument("--img-dir", default="images", help="Dossier de destination des images (défaut: images)")

    args = parser.parse_args()

    in_path = Path(args.input)
    if not in_path.exists():
        sys.exit(f"Erreur : fichier introuvable → {in_path}")

    ext = ".txt" if args.format == "txt" else ".md"
    out_path = Path(args.output) if args.output else in_path.with_suffix(ext)
    img_dir_path = out_path.parent / args.img_dir if args.extract_images else None

    print(f"⚙  Extraction du PDF : {in_path.name} → {out_path.name} [Moteur: {args.engine}]")

    try:
        converted_text = convert_pdf_to_md(
            pdf_path=in_path,
            output_format=args.format,
            engine=args.engine,
            extract_images=args.extract_images,
            image_dir=img_dir_path
        )

        out_path.write_text(converted_text, encoding="utf-8")
        size_kb = out_path.stat().st_size / 1024
        print(f"✅ Fichier généré avec succès : {out_path} ({size_kb:.1f} Ko)")

    except Exception as e:
        sys.exit(f"❌ Erreur lors de la conversion : {e}")


if __name__ == "__main__":
    main()
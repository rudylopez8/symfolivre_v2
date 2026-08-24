#!/usr/bin/env python3
"""
md2pdf.py — Convertit un fichier Markdown (.md) en PDF mis en forme.

Usage:
    python md2pdf.py document.md
    python md2pdf.py document.md -o sortie.pdf --title "Mon Doc"
    python md2pdf.py document.md --theme dark

Fonctionnalités Markdown supportées :
    - Titres (h1 à h6)
    - Gras / Italique / Barré / Code inline
    - Listes (numérotées et à puces, imbriquées)
    - Tableaux (syntaxe GFM)
    - Blocs de code (avec langue)
    - Blocs de citation (>)
    - Liens, images, séparateurs horizontaux
    - Séparateurs de page  :  ---PAGE---  (chaîne prédéfinie)
    - Encart / Note        :  ---NOTE: texte---
    - Encart / Avertissement:  ---WARNING: texte---
    - Encart / Code        :  ---CODE:langue\\n...\\n---END---
"""

import argparse
import re
import sys
from pathlib import Path

import markdown
from weasyprint import HTML, CSS

# ──────────────────────────────────────────────
#  Thèmes CSS
# ──────────────────────────────────────────────

THEMES = {
    "light": """
        @page {
            size: A4;
            margin: 2.2cm 2.0cm 2.5cm 2.0cm;
            @bottom-center {
                content: "Page " counter(page) " / " counter(pages);
                font-size: 0.75em;
                color: #888;
            }
        }
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 10.5pt;
            line-height: 1.65;
            color: #1a1a1a;
        }
        h1 { font-size: 1.9em; color: #1a1a2e; border-bottom: 2px solid #1a1a2e; padding-bottom: 0.2em; }
        h2 { font-size: 1.5em; color: #16213e; margin-top: 1.4em; }
        h3 { font-size: 1.25em; color: #0f3460; }
        h4, h5, h6 { font-size: 1.1em; color: #333; }
        code {
            font-family: 'Consolas', 'Courier New', monospace;
            background: #f4f4f4;
            padding: 0.15em 0.35em;
            border-radius: 3px;
            font-size: 0.9em;
        }
        pre {
            background: #f8f8f8;
            border: 1px solid #e0e0e0;
            border-left: 4px solid #4a90d9;
            padding: 0.8em 1em;
            border-radius: 4px;
            overflow-x: auto;
        }
        pre code { background: none; padding: 0; }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 1em 0;
            font-size: 0.95em;
        }
        th { background: #1a1a2e; color: #fff; font-weight: 600; }
        th, td { border: 1px solid #ccc; padding: 0.45em 0.7em; text-align: left; }
        tr:nth-child(even) { background: #f7f7f9; }
        blockquote {
            border-left: 4px solid #4a90d9;
            margin: 1em 0;
            padding: 0.5em 1em;
            background: #f0f6ff;
            color: #333;
        }
        hr { border: none; border-top: 2px solid #ddd; margin: 1.5em 0; }
        img { max-width: 100%; }
        a { color: #2563eb; text-decoration: none; }
        ul, ol { padding-left: 1.5em; }
        li { margin-bottom: 0.25em; }

        /* Encarts personnalisés (chaînes prédéfinies) */
        .callout-note {
            background: #eff6ff; border-left: 5px solid #3b82f6;
            padding: 0.8em 1em; margin: 1em 0; border-radius: 4px;
        }
        .callout-warning {
            background: #fffbeb; border-left: 5px solid #f59e0b;
            padding: 0.8em 1em; margin: 1em 0; border-radius: 4px;
        }
        .callout-code {
            background: #1e1e2e; color: #cdd6f4;
            padding: 1em; border-radius: 6px; margin: 1em 0;
            font-family: 'Consolas', monospace; font-size: 0.9em;
            white-space: pre-wrap;
        }
        .page-break { page-break-before: always; }
    """,

    "dark": """
        @page {
            size: A4;
            margin: 2.2cm 2.0cm 2.5cm 2.0cm;
            @bottom-center {
                content: "Page " counter(page) " / " counter(pages);
                font-size: 0.75em;
                color: #aaa;
            }
        }
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 10.5pt;
            line-height: 1.65;
            color: #e0e0e0;
            background: #1a1a2e;
        }
        h1 { font-size: 1.9em; color: #f0f0f0; border-bottom: 2px solid #4a90d9; padding-bottom: 0.2em; }
        h2 { font-size: 1.5em; color: #93c5fd; }
        h3 { font-size: 1.25em; color: #a5b4fc; }
        code {
            font-family: 'Consolas', monospace;
            background: #2a2a3e;
            padding: 0.15em 0.35em;
            border-radius: 3px;
            font-size: 0.9em;
            color: #f8f8f2;
        }
        pre {
            background: #16162a;
            border: 1px solid #333;
            border-left: 4px solid #4a90d9;
            padding: 0.8em 1em;
            border-radius: 4px;
        }
        pre code { background: none; padding: 0; }
        table { border-collapse: collapse; width: 100%; margin: 1em 0; }
        th { background: #2a2a4e; color: #fff; }
        th, td { border: 1px solid #444; padding: 0.45em 0.7em; }
        tr:nth-child(even) { background: #22223a; }
        blockquote {
            border-left: 4px solid #4a90d9;
            padding: 0.5em 1em; background: #1e1e35;
        }
        hr { border: none; border-top: 2px solid #444; }
        a { color: #60a5fa; }

        .callout-note {
            background: #1e2a40; border-left: 5px solid #3b82f6;
            padding: 0.8em 1em; margin: 1em 0; border-radius: 4px;
        }
        .callout-warning {
            background: #3a2e10; border-left: 5px solid #f59e0b;
            padding: 0.8em 1em; margin: 1em 0; border-radius: 4px;
        }
        .callout-code {
            background: #111122; color: #cdd6f4;
            padding: 1em; border-radius: 6px; margin: 1em 0;
            font-family: 'Consolas', monospace; font-size: 0.9em;
            white-space: pre-wrap;
        }
        .page-break { page-break-before: always; }
    """,
}


# ──────────────────────────────────────────────
#  Pré-traitements Markdown (chaînes prédéfinies)
# ──────────────────────────────────────────────

def preprocess_markdown(md_text: str) -> str:
    """
    Détecte et remplace les chaînes prédéfinies par du HTML/CSS.

    Chaînes reconnues (sur ligne entière) :
        ---PAGE---           → saut de page
        ---NOTE: texte---    → encart bleu
        ---WARNING: texte--- → encart orange
        ---CODE:langue       → bloc code jusqu'à ---END---
    """
    # Saut de page
    md_text = re.sub(
        r"^---PAGE---\s*$",
        '<div class="page-break"></div>',
        md_text,
        flags=re.MULTILINE,
    )

    # Encart NOTE
    md_text = re.sub(
        r"^---NOTE:\s*(.+?)\s*---\s*$",
        r'<div class="callout-note"><strong>ℹ Note :</strong> \1</div>',
        md_text,
        flags=re.MULTILINE,
    )

    # Encart WARNING
    md_text = re.sub(
        r"^---WARNING:\s*(.+?)\s*---\s*$",
        r'<div class="callout-warning"><strong>⚠ Attention :</strong> \1</div>',
        md_text,
        flags=re.MULTILINE,
    )

    # Bloc CODE (multi-lignes)  ---CODE:python ... ---END---
    md_text = re.sub(
        r"^---CODE:\s*(\S+)?\s*$(.*?)^---END---\s*$",
        lambda m: f'<div class="callout-code"><strong>{m.group(1) or "code"} :</strong>\n{m.group(2).strip()}</div>',
        md_text,
        flags=re.MULTILINE | re.DOTALL,
    )

    return md_text


# ──────────────────────────────────────────────
#  Conversion Markdown → HTML
# ──────────────────────────────────────────────

def markdown_to_html(md_text: str, title: str = "Document") -> str:
    """Convertit le texte Markdown en une page HTML complète avec CSS."""
    md_text = preprocess_markdown(md_text)

    # Extensions : tables (GFM), codehilite, toc, footnotes, abbr
    extensions = [
        "tables",
        "fenced_code",
        "codehilite",
        "toc",
        "footnotes",
        "abbr",
        "attr_list",
        "md_in_html",
    ]

    body_html = markdown.markdown(
        md_text,
        extensions=extensions,
        extension_configs={
            "codehilite": {
                "guess_lang": True,
                "css_class": "codehilite",
            },
        },
    )

    # Si md_in_html a préservé nos divs, on garde ; sinon on ré-injecte.
    # (La lib `markdown` neutralise le HTML par défaut → on le réactive.)
    body_html = body_html  # déjà correct grâce à "md_in_html"

    return f"""<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{title}</title>
    <style>{THEMES['light']}</style>
</head>
<body>
{body_html}
</body>
</html>"""


def markdown_to_html_themed(md_text: str, theme: str, title: str = "Document") -> str:
    """Version avec choix de thème."""
    css = THEMES.get(theme, THEMES["light"])

    md_text = preprocess_markdown(md_text)
    extensions = ["tables", "fenced_code", "codehilite", "toc", "footnotes", "abbr", "attr_list", "md_in_html"]

    body_html = markdown.markdown(md_text, extensions=extensions)

    bg = "background: #1a1a2e;" if theme == "dark" else ""
    return f"""<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{title}</title>
    <style>
        body {{ {bg} }}
        {css}
    </style>
</head>
<body>
{body_html}
</body>
</html>"""


# ──────────────────────────────────────────────
#  Génération PDF
# ──────────────────────────────────────────────

def generate_pdf(html_string: str, output_path: str) -> Path:
    """Rend le HTML en PDF via WeasyPrint."""
    out = Path(output_path)
    out.parent.mkdir(parents=True, exist_ok=True)

    HTML(string=html_string, base_url=str(out.parent)).write_pdf(str(out))
    return out


# ──────────────────────────────────────────────
#  CLI
# ──────────────────────────────────────────────

def main():
    parser = argparse.ArgumentParser(
        description="Convertit un fichier Markdown en PDF mis en forme.",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""\
Exemples :
  python md2pdf.py readme.md
  python md2pdf.py rapport.md -o rapport.pdf --title "Rapport T2"
  python md2pdf.py notes.md --theme dark
  echo "# Bonjour" | python md2pdf.py - -o stdout.pdf
""",
    )
    parser.add_argument("input", help="Fichier .md d'entrée (ou '-' pour stdin)")
    parser.add_argument("-o", "--output", default=None, help="Fichier .md de sortie (défaut: <input>.pdf)")
    parser.add_argument("--title", default=None, help="Titre du document (défaut: nom du fichier)")
    parser.add_argument("--theme", choices=THEMES.keys(), default="light", help="Thème CSS (défaut: light)")
    args = parser.parse_args()

    # Lecture
    if args.input == "-":
        md_text = sys.stdin.read()
        default_out = "output.pdf"
    else:
        in_path = Path(args.input)
        if not in_path.exists():
            sys.exit(f"Erreur : fichier introuvable → {in_path}")
        md_text = in_path.read_text(encoding="utf-8")
        default_out = str(in_path.with_suffix(".pdf"))

    output = args.output or default_out
    title = args.title or Path(output).stem

    # Conversion
    print(f"⚙  Conversion : {args.input}  →  {output}  [thème: {args.theme}]")
    html = markdown_to_html_themed(md_text, args.theme, title=title)
    out_path = generate_pdf(html, output)
    size_kb = out_path.stat().st_size / 1024
    print(f"✅ PDF généré : {out_path}  ({size_kb:.1f} Ko)")


if __name__ == "__main__":
    main()
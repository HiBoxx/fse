#!/usr/bin/env python3
"""
Convertisseur XML WordPress vers JSON pour les archives
Plus robuste que le parser PHP
"""

import json
import xml.etree.ElementTree as ET
from datetime import datetime
import re
import sys

def clean_text(text):
    """Nettoie le texte des caractères invalides"""
    if not text:
        return ""
    # Retirer les balises HTML
    text = re.sub(r'<[^>]+>', '', text)
    # Normaliser les espaces
    text = re.sub(r'\s+', ' ', text)
    return text.strip()

def parse_wordpress_xml(xml_file):
    """Parse le fichier XML WordPress"""
    print(f"Lecture du fichier {xml_file}...")

    try:
        tree = ET.parse(xml_file)
        root = tree.getroot()
    except ET.ParseError as e:
        print(f"⚠️  Erreur de parsing, tentative de récupération...")
        # Lire le contenu et essayer de nettoyer
        with open(xml_file, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        # Retirer les caractères de contrôle invalides
        content = re.sub(r'[\x00-\x08\x0B\x0C\x0E-\x1F]', '', content)
        try:
            root = ET.fromstring(content)
        except:
            print("❌ Impossible de parser le XML")
            return []

    # Namespaces WordPress
    namespaces = {
        'content': 'http://purl.org/rss/1.0/modules/content/',
        'wp': 'http://wordpress.org/export/1.2/',
        'dc': 'http://purl.org/dc/elements/1.1/'
    }

    articles = []
    count = 0
    errors = 0

    print("Extraction des articles...")

    # Trouver tous les items
    for item in root.findall('.//item'):
        try:
            # Type de post
            post_type = item.find('wp:post_type', namespaces)
            if post_type is None or post_type.text != 'post':
                continue

            # Statut
            status = item.find('wp:status', namespaces)
            if status is None or status.text != 'publish':
                continue

            count += 1

            # ID
            post_id_elem = item.find('wp:post_id', namespaces)
            post_id = int(post_id_elem.text) if post_id_elem is not None and post_id_elem.text else count

            # Titre
            title_elem = item.find('title')
            title = title_elem.text if title_elem is not None and title_elem.text else f"Article sans titre (ID: {post_id})"

            # Date
            date_elem = item.find('wp:post_date', namespaces)
            if date_elem is not None and date_elem.text:
                try:
                    date_obj = datetime.strptime(date_elem.text, '%Y-%m-%d %H:%M:%S')
                    date = date_obj.strftime('%Y-%m-%d')
                    year = date_obj.strftime('%Y')
                except:
                    date = datetime.now().strftime('%Y-%m-%d')
                    year = datetime.now().strftime('%Y')
            else:
                date = datetime.now().strftime('%Y-%m-%d')
                year = datetime.now().strftime('%Y')

            # Contenu
            content_elem = item.find('content:encoded', namespaces)
            content = clean_text(content_elem.text) if content_elem is not None else ""

            # Extrait
            excerpt_elem = item.find('wp:post_excerpt', namespaces)
            excerpt = clean_text(excerpt_elem.text) if excerpt_elem is not None and excerpt_elem.text else ""
            if not excerpt and content:
                excerpt = content[:300] + "..."

            # Catégories, tags, branches
            categories = []
            tags = []
            branches = []

            for category in item.findall('category'):
                domain = category.get('domain', '')
                nicename = category.get('nicename', '')
                name = category.text if category.text else ''

                if domain == 'category':
                    categories.append({'slug': nicename, 'name': name})
                elif domain == 'post_tag':
                    tags.append({'slug': nicename, 'name': name})
                elif domain == 'branche':
                    branches.append({'slug': nicename, 'name': name})

            # Lien PDF (chercher dans les postmeta)
            pdf_url = ""
            for meta in item.findall('wp:postmeta', namespaces):
                meta_key_elem = meta.find('wp:meta_key', namespaces)
                meta_value_elem = meta.find('wp:meta_value', namespaces)

                if meta_key_elem is not None and meta_value_elem is not None:
                    meta_key = meta_key_elem.text or ""
                    meta_value = meta_value_elem.text or ""

                    if ('pdf' in meta_key.lower() or 'file' in meta_key.lower()) and '.pdf' in meta_value:
                        pdf_url = meta_value
                        break

            # Construire l'article
            article = {
                'id': post_id,
                'title': title,
                'date': date,
                'year': year,
                'excerpt': excerpt,
                'content': content,
                'categories': categories,
                'tags': tags,
                'branches': branches,
                'pdf_url': pdf_url
            }

            articles.append(article)

            if count % 100 == 0:
                print(f"  {count} articles traités...")

        except Exception as e:
            errors += 1
            continue

    print(f"Total: {count} articles extraits")
    if errors > 0:
        print(f"⚠️  {errors} articles ignorés (erreurs)")

    return articles

def main():
    if len(sys.argv) < 2:
        print("Usage: python3 convert-xml-to-json.py fichier.xml")
        sys.exit(1)

    xml_file = sys.argv[1]

    # Parser le XML
    articles = parse_wordpress_xml(xml_file)

    if not articles:
        print("❌ Aucun article extrait")
        sys.exit(1)

    # Trier par date décroissante
    articles.sort(key=lambda x: x['date'], reverse=True)

    # Générer le JSON
    print("Génération du fichier JSON...")

    data = {
        'generated_at': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
        'total': len(articles),
        'articles': articles
    }

    output_file = 'data.json'
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)

    import os
    size_mb = os.path.getsize(output_file) / 1024 / 1024

    print(f"✅ Fichier généré: {output_file}")
    print(f"   Taille: {size_mb:.2f} MB")
    print()

if __name__ == '__main__':
    main()

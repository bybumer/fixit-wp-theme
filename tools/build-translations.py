# -*- coding: utf-8 -*-
"""
Tərcümə fayllarını yığır: fixit-theme/languages/{en_US,ru_RU}.po və .mo

Mənbə: tools/translations/*.json  —  {"azərbaycanca mətn": {"en": "...", "ru": "..."}}

İstifadə:
    python tools/build-translations.py

Skript həmçinin temadakı ön üz mətnlərindən hansının tərcüməsiz qaldığını
göstərir — yeni mətn əlavə edəndə buradan bilinir.

Qeyd: tema üçün WordPress faylları `{locale}.mo` adı ilə axtarır
(plaginlərdəki kimi `{domain}-{locale}.mo` yox).
"""

import glob
import io
import json
import os
import re
import struct
import sys

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')

BASE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
THEME = os.path.join(BASE, 'fixit-theme')
SRC = os.path.join(BASE, 'tools', 'translations')
OUT = os.path.join(THEME, 'languages')

LOCALES = {
    'en': ('en_US', 'nplurals=2; plural=(n != 1);'),
    'ru': ('ru_RU', 'nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);'),
}

# Ziyarətçinin gördüyü fayllar (admin bölmələri tərcümə olunmur).
FRONT = [
    '404.php', 'footer.php', 'front-page.php', 'header.php', 'index.php',
    'page.php', 'searchform.php', 'single.php', 'single-fixit_product.php',
    'template-about.php', 'template-contact.php', 'template-products.php',
    'template-remote.php', 'template-remote-privacy.php', 'template-services.php',
    'inc/faq.php', 'inc/product-data.php', 'inc/seo.php', 'inc/contact-form.php',
    'inc/i18n.php',
]

CALL = r"(?:esc_html__|esc_attr__|esc_html_e|esc_attr_e|__|_e)"
PAT = re.compile(CALL + r"\(\s*(?:'((?:[^'\\]|\\.)*)'|\"((?:[^\"\\]|\\.)*)\")\s*,\s*'fixit'", re.S)


def theme_strings():
    """Ön üz fayllarındakı bütün mətnlər (sıra ilə, təkrarsız)."""
    seen, order = set(), []
    for rel in FRONT:
        path = os.path.join(THEME, rel)
        if not os.path.exists(path):
            continue
        s = io.open(path, encoding='utf-8').read()
        for m in PAT.finditer(s):
            raw = m.group(1) if m.group(1) is not None else m.group(2)
            if m.group(1) is not None:
                t = raw.replace("\\'", "'").replace('\\\\', '\\')
            else:
                t = raw.replace('\\"', '"').replace('\\\\', '\\')
            if t not in seen:
                seen.add(t)
                order.append((t, rel))
    return order


def load_translations():
    merged = {}
    for path in sorted(glob.glob(os.path.join(SRC, '*.json'))):
        data = json.load(io.open(path, encoding='utf-8'))
        for az, tr in data.items():
            merged.setdefault(az, {}).update(tr)
    return merged


def po_escape(s):
    return s.replace('\\', '\\\\').replace('"', '\\"').replace('\n', '\\n').replace('\t', '\\t')


def write_po(path, locale, plural, pairs):
    lines = [
        'msgid ""',
        'msgstr ""',
        '"Project-Id-Version: FIXIT Pro\\n"',
        '"Language: {}\\n"'.format(locale),
        '"MIME-Version: 1.0\\n"',
        '"Content-Type: text/plain; charset=UTF-8\\n"',
        '"Content-Transfer-Encoding: 8bit\\n"',
        '"Plural-Forms: {}\\n"'.format(plural),
        '"X-Domain: fixit\\n"',
        '',
    ]
    for src, dst, ref in pairs:
        lines.append('#: ' + ref)
        lines.append('msgid "{}"'.format(po_escape(src)))
        lines.append('msgstr "{}"'.format(po_escape(dst)))
        lines.append('')
    io.open(path, 'w', encoding='utf-8', newline='\n').write('\n'.join(lines))


def write_mo(path, locale, plural, pairs):
    header = (
        'Project-Id-Version: FIXIT Pro\n'
        'Language: {}\n'
        'MIME-Version: 1.0\n'
        'Content-Type: text/plain; charset=UTF-8\n'
        'Content-Transfer-Encoding: 8bit\n'
        'Plural-Forms: {}\n'
        'X-Domain: fixit\n'
    ).format(locale, plural)

    entries = [(b'', header.encode('utf-8'))]
    entries += [(s.encode('utf-8'), d.encode('utf-8')) for s, d, _ in pairs]
    entries.sort(key=lambda e: e[0])

    n = len(entries)
    o_table = 7 * 4
    t_table = o_table + n * 8
    data_off = t_table + n * 8

    ids = b''
    strs = b''
    o_entries, t_entries = [], []

    for k, _ in entries:
        o_entries.append((len(k), data_off + len(ids)))
        ids += k + b'\0'
    str_off = data_off + len(ids)
    for _, v in entries:
        t_entries.append((len(v), str_off + len(strs)))
        strs += v + b'\0'

    out = struct.pack('<Iiiiiii', 0x950412de, 0, n, o_table, t_table, 0, 0)
    for l, o in o_entries:
        out += struct.pack('<ii', l, o)
    for l, o in t_entries:
        out += struct.pack('<ii', l, o)
    out += ids + strs

    io.open(path, 'wb').write(out)


def main():
    strings = theme_strings()
    tr = load_translations()

    if not os.path.isdir(OUT):
        os.makedirs(OUT)

    missing = {code: [] for code in LOCALES}

    for code, (locale, plural) in LOCALES.items():
        pairs = []
        for az, ref in strings:
            # Kənar boşluqlar qəsdən ola bilər ("Bir tərəfdaş — " kimi) — silmirik.
            dst = tr.get(az, {}).get(code, '')
            if dst.strip():
                pairs.append((az, dst, ref))
            else:
                missing[code].append((az, ref))

        # Bazadan gələn, şablonda olmayan amma tərcüməsi olan mətnlər də daxil olsun.
        known = set(a for a, _ in strings)
        for az, t in tr.items():
            if az not in known and t.get(code, '').strip():
                pairs.append((az, t[code], 'tools/translations'))

        write_po(os.path.join(OUT, locale + '.po'), locale, plural, pairs)
        write_mo(os.path.join(OUT, locale + '.mo'), locale, plural, pairs)
        print('{}: {} tərcümə → languages/{}.mo'.format(locale, len(pairs), locale))

    total_missing = 0
    for code, items in missing.items():
        if items:
            print('\n{} — tərcüməsiz {} mətn:'.format(code.upper(), len(items)))
            for az, ref in items:
                print('   [{}] {}'.format(ref, az[:90]))
            total_missing += len(items)

    if not total_missing:
        print('\nBütün ön üz mətnləri tərcümə olunub.')
    return 0


if __name__ == '__main__':
    sys.exit(main())

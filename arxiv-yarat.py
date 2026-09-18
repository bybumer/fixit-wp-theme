"""
fixit-theme.zip arxivini yaradır.

DİQQƏT: PowerShell-in `Compress-Archive` əmri ilə arxiv YARATMAYIN.
O, fayl yollarını tərs kəsik xətlə (fixit-theme\\style.css) yazır və
WordPress "Temada style.css stil dosyası eksik" xətası verir.

İstifadə:
    python arxiv-yarat.py

Python yoxdursa: fixit-theme qovluğuna sağ klik →
"Send to → Compressed (zipped) folder" da düzgün nəticə verir.
"""

import os
import zipfile

BASE = os.path.dirname(os.path.abspath(__file__))
SRC = os.path.join(BASE, 'fixit-theme')
DEST = os.path.join(BASE, 'fixit-theme.zip')

SKIP_DIRS = {'.git', '__pycache__', 'node_modules'}
SKIP_FILES = {'.DS_Store', 'Thumbs.db'}


def main():
    if not os.path.isdir(SRC):
        raise SystemExit('fixit-theme qovlugu tapilmadi: ' + SRC)

    if os.path.exists(DEST):
        os.remove(DEST)

    count = 0
    with zipfile.ZipFile(DEST, 'w', zipfile.ZIP_DEFLATED) as z:
        for root, dirs, files in os.walk(SRC):
            dirs[:] = [d for d in dirs if d not in SKIP_DIRS]
            for name in files:
                if name in SKIP_FILES:
                    continue
                full = os.path.join(root, name)
                rel = os.path.relpath(full, BASE)
                # ZIP standartı: yalnız duz kesik xett (/)
                z.write(full, rel.replace(os.sep, '/'))
                count += 1

    size_kb = round(os.path.getsize(DEST) / 1024, 1)
    print('Hazirdir: {}  ({} fayl, {} KB)'.format(DEST, count, size_kb))


if __name__ == '__main__':
    main()

"""
Temanın yeni versiyasını GitHub-a reliz kimi dərc edir.

Sayt bu relizi özü görür və yenilənir (fixit-theme/inc/updater.php).

Addımlar:
  1) style.css və functions.php-də versiyanı artırın (ikisi EYNİ olmalıdır)
  2) Dəyişiklikləri commit edin
  3) python reliz-yarat.py "Qısa qeyd: nə dəyişdi"

Skript yoxlayır:
  - hər iki fayldakı versiya eynidir
  - commit edilməmiş dəyişiklik yoxdur (reliz kodla tam uyğun olsun)
  - bu versiya üçün reliz hələ yoxdur
  - arxivin quruluşu WordPress-in gözlədiyi kimidir
"""

import io
import os
import re
import subprocess
import sys
import zipfile

# Windows konsolu (cp1252) Azərbaycan hərflərini çap edə bilmir — UTF-8-ə keçirik.
if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')
    sys.stderr.reconfigure(encoding='utf-8', errors='replace')

BASE = os.path.dirname(os.path.abspath(__file__))
THEME = os.path.join(BASE, 'fixit-theme')
ZIP = os.path.join(BASE, 'fixit-theme.zip')


def run(cmd, check=True):
    """Əmri işlədir, nəticəni qaytarır."""
    res = subprocess.run(cmd, cwd=BASE, capture_output=True, text=True, encoding='utf-8')
    if check and res.returncode != 0:
        raise SystemExit('XETA: {}\n{}'.format(' '.join(cmd), (res.stderr or res.stdout).strip()))
    return res


def read_version():
    css = io.open(os.path.join(THEME, 'style.css'), encoding='utf-8').read()
    php = io.open(os.path.join(THEME, 'functions.php'), encoding='utf-8').read()

    m1 = re.search(r'^Version:\s*([0-9.]+)', css, re.M)
    m2 = re.search(r"define\(\s*'FIXIT_VERSION',\s*'([0-9.]+)'\s*\)", php)

    if not m1 or not m2:
        raise SystemExit('XETA: versiya tapilmadi (style.css / functions.php).')
    if m1.group(1) != m2.group(1):
        raise SystemExit('XETA: versiyalar ferqlidir — style.css: {}, functions.php: {}'.format(m1.group(1), m2.group(1)))

    return m1.group(1)


def build_zip():
    """Arxivi yığır — yollar yalnız düz kəsik xəttlə (ZIP standartı)."""
    if os.path.exists(ZIP):
        os.remove(ZIP)

    with zipfile.ZipFile(ZIP, 'w', zipfile.ZIP_DEFLATED) as z:
        for root, dirs, files in os.walk(THEME):
            dirs[:] = [d for d in dirs if d not in ('.git', '__pycache__', 'node_modules')]
            for name in files:
                if name in ('.DS_Store', 'Thumbs.db'):
                    continue
                full = os.path.join(root, name)
                arc = os.path.relpath(full, BASE).replace(os.sep, '/')
                z.write(full, arc)


def verify_zip(version):
    with zipfile.ZipFile(ZIP) as z:
        names = z.namelist()
        if 'fixit-theme/style.css' not in names:
            raise SystemExit('XETA: arxivde fixit-theme/style.css yoxdur.')
        if any('\\' in n for n in names):
            raise SystemExit('XETA: arxivde ters kesik xett var — WordPress acmayacaq.')
        css = z.read('fixit-theme/style.css').decode('utf-8')
        if 'Version: ' + version not in css:
            raise SystemExit('XETA: arxivdeki versiya {} deyil.'.format(version))
    return len(names)


def main():
    notes = sys.argv[1] if len(sys.argv) > 1 else ''
    version = read_version()
    tag = 'v' + version

    # Commit edilməmiş dəyişiklik varsa dayan — reliz kodla eyni olmalıdır.
    status = run(['git', 'status', '--porcelain']).stdout.strip()
    if status:
        raise SystemExit('XETA: commit edilmemis deyisiklikler var:\n' + status)

    # Bu versiya artıq buraxılıbsa dayan.
    if run(['gh', 'release', 'view', tag], check=False).returncode == 0:
        raise SystemExit('XETA: {} artiq buraxilib. Versiyani artirin.'.format(tag))

    build_zip()
    count = verify_zip(version)
    print('Arxiv hazir: {} fayl, {:.1f} KB'.format(count, os.path.getsize(ZIP) / 1024))

    run(['git', 'push', 'origin', 'HEAD'])

    body = notes or 'FIXIT Pro {}'.format(version)
    run(['gh', 'release', 'create', tag, ZIP, '--title', 'FIXIT Pro ' + version, '--notes', body])

    print('Reliz derc olundu: ' + tag)
    print('Sayt 12 saat erzinde ozu gorecek. Derhal: Gorunus -> FIXIT quraşdırma -> "Yenilemeni indi yoxla".')


if __name__ == '__main__':
    main()

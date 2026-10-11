"""
Sorğu Sistemi səhifəsinin ekran görüntülərini demo.fixit.az-dan yenidən çəkir.

Şəkillər fixit-theme/assets/img/products/sorgu-sistemi/ qovluğuna yazılır:
hər ekran üçün 1600 px və 800 px (mobil) WebP. Demo dəyişəndə — yeni ekran,
təzə nümunə məlumat — bir əmr bəsdir:

    python tools/demo-ekranlar.py

Sonra versiyanı artırıb reliz edin (README → Yeniləmələr).

Tələblər: Google Chrome, Node.js 22+ (daxili WebSocket), Pillow.
Real şirkət adları şəkildə uydurma adlarla əvəz olunur (demo-ekranlar.mjs).
Başlıqlar inc/product-data.php → 'screens' bölməsindədir.
"""

import os
import shutil
import subprocess
import sys
import tempfile
import time
import urllib.request

from PIL import Image

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')

BASE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(BASE, 'fixit-theme', 'assets', 'img', 'products', 'sorgu-sistemi')
SCRIPT = os.path.join(BASE, 'tools', 'demo-ekranlar.mjs')
PORT = '9339'

# Sıra = qalereyadakı sıra (product-data.php → 'screens' ilə eyni adlar).
ORDER = ['sorgular', 'sorgu', 'lovhe', 'tapsiriqlar', 'vaxt-hesabati', 'hesabatlar']

CHROME_PATHS = [
    r'C:\Program Files\Google\Chrome\Application\chrome.exe',
    r'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
    '/usr/bin/google-chrome',
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
]


def find_chrome():
    for path in CHROME_PATHS:
        if os.path.exists(path):
            return path
    found = shutil.which('chrome') or shutil.which('google-chrome')
    if not found:
        raise SystemExit('XETA: Google Chrome tapilmadi.')
    return found


def wait_for_chrome():
    for _ in range(40):
        try:
            urllib.request.urlopen('http://127.0.0.1:{}/json/version'.format(PORT), timeout=1)
            return
        except OSError:
            time.sleep(0.25)
    raise SystemExit('XETA: Chrome debug portu acilmadi.')


def main():
    raw = tempfile.mkdtemp(prefix='fixit-shots-')
    profile = tempfile.mkdtemp(prefix='fixit-chrome-')

    # Ayrıca, müvəqqəti profil - istifadəçinin öz Chrome-una toxunmuruq.
    chrome = subprocess.Popen([
        find_chrome(), '--headless=new', '--remote-debugging-port=' + PORT,
        '--user-data-dir=' + profile, '--hide-scrollbars', '--no-first-run',
        '--no-default-browser-check', 'about:blank',
    ], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)

    try:
        wait_for_chrome()
        subprocess.run(['node', SCRIPT, PORT, raw], check=True)
    finally:
        chrome.terminate()
        chrome.wait(timeout=10)

    os.makedirs(OUT, exist_ok=True)
    for i, name in enumerate(ORDER, 1):
        im = Image.open(os.path.join(raw, name + '.png')).convert('RGB')
        for width, suffix in ((1600, ''), (800, '-800')):
            height = round(im.height * width / im.width)
            path = os.path.join(OUT, '{:02d}-{}{}.webp'.format(i, name, suffix))
            im.resize((width, height), Image.LANCZOS).save(path, 'WEBP', quality=80, method=6)
            print('{}  {} KB'.format(os.path.relpath(path, BASE), os.path.getsize(path) // 1024))

    shutil.rmtree(raw, ignore_errors=True)
    shutil.rmtree(profile, ignore_errors=True)
    print('Hazirdir. Sekillere baxin, sonra versiyani artirib reliz edin.')


if __name__ == '__main__':
    main()

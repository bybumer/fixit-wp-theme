"""
FIXIT loqo dəstini hazırlayır.

Mənbə: OneDrive_1_24.08.2026/cek logo.png  (2000x1000, şəffaf fon)
Nəticə: logo-hazir/ qovluğunda WordPress-ə yüklənməyə hazır fayllar.

İstifadə:
    python tools/make-logos.py
"""

import os
import sys

from PIL import Image

SRC = os.path.join(
    os.path.dirname(os.path.abspath(__file__)), '..',
    'OneDrive_1_24.08.2026', 'cek logo.png'
)
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'logo-hazir')

# Brend rəngi — saytın əsas mavisi (#0b63e5, style.css → --brand-600).
# Əvvəl mənbə faylın öz rəngi idi (#2c08c0, bənövşəyi) və saytla uyuşmurdu.
BRAND = (11, 99, 229)

# "X" nişanının başladığı sütun (kəsilmiş loqoda, "IX" seqmentinin içində).
# Sütun profilində 106-cı pikseldə "I" zolağı bitir.
X_MARK_START = 534 + 112


def load_logo():
    """Mənbəni açır, şəffaf kənarları kəsir və rəngi təmizləyir."""
    im = Image.open(SRC).convert('RGBA')
    im = im.crop(im.getchannel('A').getbbox())
    return flatten_colour(im, BRAND)


def flatten_colour(im, rgb):
    """
    Bütün piksellərə eyni RGB verir, yalnız alfa kanalını saxlayır.

    Mənbə şəkil generativ alətdən gəldiyi üçün rəngdə səs-küy var
    (#2c08c0, #2c08c2, #2c08bf...). Bu həm loqonu çirkləndirir, həm də
    PNG-ni şişirdir. Forma tamamilə alfa kanalında olduğu üçün RGB-ni
    bir dəyərə yığmaq həm təmizləyir, həm faylı kiçildir.
    """
    solid = Image.new('RGBA', im.size, rgb + (255,))
    solid.putalpha(im.getchannel('A'))
    return solid


def fit_height(im, height):
    """Nisbəti saxlayaraq verilmiş hündürlüyə gətirir."""
    width = round(im.width * height / im.height)
    return im.resize((width, height), Image.LANCZOS)


def pad_square(im, size, bg, margin=0.18):
    """Şəkli kvadrat kətanın ortasına, kənarlarda boşluq buraxaraq yerləşdirir."""
    canvas = Image.new('RGBA', (size, size), bg)
    inner = round(size * (1 - margin * 2))
    scale = min(inner / im.width, inner / im.height)
    fitted = im.resize((max(1, round(im.width * scale)), max(1, round(im.height * scale))), Image.LANCZOS)
    canvas.paste(fitted, ((size - fitted.width) // 2, (size - fitted.height) // 2), fitted)
    return canvas


def banner(im, width, height, bg, margin=0.14):
    """Sosial şəbəkə üçün üfüqi kətan."""
    canvas = Image.new('RGBA', (width, height), bg)
    inner_w = round(width * (1 - margin * 2))
    inner_h = round(height * (1 - margin * 2))
    scale = min(inner_w / im.width, inner_h / im.height)
    fitted = im.resize((round(im.width * scale), round(im.height * scale)), Image.LANCZOS)
    canvas.paste(fitted, ((width - fitted.width) // 2, (height - fitted.height) // 2), fitted)
    return canvas


def main():
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')

    if not os.path.isdir(OUT):
        os.makedirs(OUT)

    logo = load_logo()
    white = flatten_colour(logo, (255, 255, 255))

    # Yalnız "X" nişanı — favicon üçün. Söz kimi loqo 16 pikseldə oxunmur.
    full = Image.open(SRC).convert('RGBA')
    full = full.crop(full.getchannel('A').getbbox())
    mark = full.crop((X_MARK_START, 0, 1296, full.height))
    mark = flatten_colour(mark.crop(mark.getchannel('A').getbbox()), BRAND)
    mark_white = flatten_colour(mark, (255, 255, 255))

    files = []

    def save(im, name, note):
        path = os.path.join(OUT, name)
        im.save(path, optimize=True)
        files.append((name, im.width, im.height, os.path.getsize(path), note))

    # --- Başlıq loqosu (açıq fon) ---
    save(fit_height(logo, 120), 'fixit-logo.png', 'Başlıq — açıq fon')

    # --- Ağ variant (qaranlıq fon: altlıq) ---
    save(fit_height(white, 120), 'fixit-logo-white.png', 'Altlıq — qaranlıq fon')

    # --- Sayt ikonu (favicon) ---
    save(pad_square(mark_white, 512, BRAND + (255,)), 'fixit-icon-512.png', 'Sayt ikonu (WordPress)')

    # --- Sosial paylaşım şəkli ---
    save(banner(white, 1200, 630, BRAND + (255,)), 'fixit-og-1200x630.png', 'Sosial paylaşım (OG)')

    # --- Yalnız nişan, şəffaf (ehtiyat) ---
    save(fit_height(mark, 512), 'fixit-mark.png', 'Yalnız X nişanı, şəffaf')

    print('Brend rəngi: #%02X%02X%02X\n' % BRAND)
    print('%-26s %-11s %-9s %s' % ('FAYL', 'ÖLÇÜ', 'HƏCM', 'HARA'))
    for name, w, h, size, note in files:
        print('%-26s %-11s %-9s %s' % (name, '%dx%d' % (w, h), '%.0f KB' % (size / 1024), note))
    print('\nQovluq: %s' % os.path.abspath(OUT))


if __name__ == '__main__':
    main()

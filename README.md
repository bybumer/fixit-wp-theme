# FIXIT Pro — WordPress teması

fixit.az saytı üçün hazırlanmış peşəkar WordPress teması.
Mövcud saytdakı bütün mətnlər saxlanılıb, dizayn və struktur yenidən qurulub.

**Texnologiya:** sadə PHP + CSS + vanilla JavaScript.
Build sistemi, npm, SASS və ya hər hansı əlavə plagin **tələb olunmur** —
faylı açıb redaktə etmək və yükləmək kifayətdir.

---

## 1. Qovluq quruluşu

```
fixit-web/
├── docker-compose.yml        ← lokal test mühiti (ticket sistemindən tam ayrı)
├── .env.example              ← lokal parolların şablonu (.env git-ə düşmür)
├── reliz-yarat.py            ← yeni versiyanı GitHub-a dərc edir (sayt özü yenilənir)
├── arxiv-yarat.py            ← yalnız ZIP yığır (əl ilə yükləmək lazım olsa)
├── README.md                 ← bu fayl
└── fixit-theme/              ← WordPress teması (Hostinger-ə bu qovluq yüklənir)
    ├── style.css             ← BÜTÜN dizayn burada (rənglər, ölçülər, adaptivlik)
    ├── functions.php         ← temanın nüvəsi, stil/skript qoşulması
    ├── header.php            ← üst zolaq, loqo, menyu, mobil menyu
    ├── footer.php            ← alt hissə, üzən düymələr (WhatsApp / zəng)
    ├── front-page.php        ← ANA SƏHİFƏ (hero, xidmətlər, proses, rəylər, FAQ)
    ├── template-services.php ← Xidmətlər səhifəsi
    ├── template-products.php ← Məhsullar siyahısı + satış şərtləri
    ├── single-fixit_product.php ← HƏR MƏHSULUN detallı səhifəsi (/mehsul/<ad>/)
    ├── template-about.php    ← Haqqımızda səhifəsi
    ├── template-contact.php  ← Əlaqə səhifəsi + form
    ├── page.php              ← adi səhifələr
    ├── index.php             ← bloq / axtarış nəticələri
    ├── single.php            ← tək məqalə
    ├── 404.php               ← səhifə tapılmadı
    ├── searchform.php        ← axtarış formu
    ├── assets/js/main.js     ← tema dəyişdirici, mobil menyu, animasiyalar, şəkil böyüdücü
    ├── assets/js/admin-gallery.js ← admin: məhsul ekran görüntülərinin seçilməsi
    └── inc/
        ├── icons.php         ← SVG ikon kitabxanası
        ├── customizer.php    ← admin paneldəki ayarlar (telefon, ünvan, sosial)
        ├── cpt.php           ← "Xidmətlər", "Məhsullar" və "Rəylər" bölmələri
        ├── product-data.php  ← məhsul səhifələrinin detallı məzmunu (imkanlar, iş prinsipi, suallar)
        ├── product-demo.php  ← məhsulun demo ayarları və ekran görüntüləri qalereyası
        ├── slugs.php         ← ünvanlarda ə → e (təmiz URL)
        ├── faq.php           ← sual-cavab məzmunu (səhifə + Google schema)
        ├── contact-form.php  ← əlaqə formu (plagin yoxdur)
        ├── demo-content.php  ← ilk aktivləşdirmədə səhifə/menyu yaradır
        ├── seo.php           ← meta teqlər + Google struktur məlumatları
        ├── seo-admin.php     ← admin paneldəki SEO izləmə lövhəsi
        └── updater.php       ← GitHub relizlərindən avtomatik yeniləmə
```

---

## 2. Lokal test (Docker)

> Bu stek `fixitweb` adlı **ayrı** layihədir: öz şəbəkəsi, öz volume-ları,
> 8090 portu. Ticket sisteminin konteynerlərinə heç bir təsiri yoxdur.

```bash
cd C:\fixit-web && docker compose -p fixitweb up -d
```

| | |
|---|---|
| Sayt | http://localhost:8090 |
| Admin | http://localhost:8090/wp-admin |
| Giriş məlumatları | `.env` faylında (git-ə düşmür) |

Dayandırmaq:

```bash
docker compose -p fixitweb down
```

Hər şeyi (baza daxil) silmək:

```bash
docker compose -p fixitweb down -v
```

`fixit-theme/` qovluğu konteynerə birbaşa bağlanıb — faylı dəyişib
brauzeri yeniləmək kifayətdir, heç nə köçürmək lazım deyil.

---

## 3. Nəyi harada dəyişirsiniz?

| İstəyiniz | Yer |
|---|---|
| Telefon, e-poçt, ünvan, iş saatları | Admin → **Görünüş → Fərdiləşdir → FIXIT tema ayarları → Əlaqə** |
| Facebook / Instagram / TikTok linkləri | Fərdiləşdir → **Sosial şəbəkələr** |
| Ana səhifədəki böyük başlıq və statistika | Fərdiləşdir → **Ana səhifə (Hero)** |
| Loqo | Fərdiləşdir → **Sayt kimliyi → Loqo** |
| Xidmət mətnləri, ikonlar, üstünlüklər | Admin → **Xidmətlər** |
| Məhsul adı, qısa təsvir, əsas xüsusiyyətlər | Admin → **Məhsullar** |
| Məhsulun demo ünvanı, demo rejimi, **ekran görüntüləri** | Admin → Məhsullar → məhsulu açın → **Demo və ekran görüntüləri** |
| Məhsul səhifəsinin detallı bölmələri (imkan qrupları, iş prinsipi, təhlükəsizlik, suallar) | `inc/product-data.php` |
| Demo sorğuları | Admin → **Form mesajları** ("Növ: Demo") |
| Məhsulların satış şərtləri, qiymət mətni, FAQ | `template-products.php` |
| Müştəri rəyləri | Admin → **Müştəri rəyləri** |
| Formdan gələn müraciətlər | Admin → **Form mesajları** |
| Menyu | Görünüş → **Menyular** |
| Rəng palitrası | `style.css` faylının başındakı `:root` bloku |
| Ana səhifədəki bölmələrin sırası | `front-page.php` (hər bölmə şərhlə ayrılıb) |
| FAQ sualları | `front-page.php` → `$fixit_faqs` massivi |
| Tərəfdaş/texnologiya adları | `front-page.php` → `$fixit_brands` massivi |
| Yeni ikon | `inc/icons.php` → massivə bir sətir |
| Səhifənin SEO başlığı və təsviri | Yazını açın → aşağıdakı **SEO** qutusu |
| SEO vəziyyətinə baxmaq | Admin → **FIXIT SEO** |
| Sual-cavab mətnləri | `inc/faq.php` |

---

## 4. Hostinger-ə yükləmək

> ⚠️ **Vacib:** hazırkı fixit.az Hostinger-in **AI Website Builder**-i ilə
> qurulub — bu, WordPress deyil. WordPress-ə keçmək üçün hPanel-dən
> həmin domen üçün WordPress quraşdırmalısınız; bu, builder saytını əvəz edəcək.
> Köhnə saytın ehtiyat nüsxəsini əvvəlcədən götürün.

**Addımlar:**

1. hPanel → **Websites → fixit.az → WordPress quraşdır** (əgər hələ yoxdursa).
2. Hazır arxivi götürün: **`C:\fixit-web\fixit-theme.zip`**

   > ⚠️ Arxivi yenidən yaratmaq lazım gələrsə, PowerShell-in `Compress-Archive`
   > əmrindən **istifadə etməyin** — o, yolları tərs kəsik xətlə yazır və
   > WordPress *"Temada style.css stil dosyası eksik"* xətası verir.
   > Bunun əvəzinə:
   > ```bash
   > python C:\fixit-web\arxiv-yarat.py
   > ```
   > və ya `fixit-theme` qovluğuna sağ klik → *Send to → Compressed (zipped) folder*.
3. WordPress admin → **Görünüş → Temalar → Yeni əlavə et → Tema yüklə** →
   ZIP-i seçin → **Quraşdır** → **Aktivləşdir**.
4. Aktivləşdirən kimi tema özü yaradır:
   - Ana səhifə, Xidmətlər, Haqqımızda, Əlaqə səhifələri
   - 6 xidmət və 2 müştəri rəyi
   - əsas menyu
5. **Ayarlar → Daimi linklər → "Yazı adı"** seçib yadda saxlayın.
6. Fərdiləşdir bölməsindən telefon/ünvan məlumatlarını yoxlayın.

**Temanı yeniləmək:** 1.4.0 versiyasından etibarən ZIP yükləmək lazım deyil —
bax: **5. Yeniləmələr**.

**Məzmun yaradılmayıbsa:** Görünüş → **FIXIT quraşdırma** → *Başlanğıc məzmunu yarat*.
Bu düymə təhlükəsizdir: mövcud olanı silmir, yalnız çatışmayanı yaradır.

**E-poçt haqqında:** əlaqə formu mesajı həm admin panelə yazır,
həm də `wp_mail()` ilə göndərir. Paylaşılan hostinqlərdə məktublar bəzən
spama düşür — etibarlı çatdırılma üçün **WP Mail SMTP** plagini quraşdırıb
şirkət mail hesabınızı (info@fixit.az) SMTP kimi göstərmək tövsiyə olunur.

---

## 5. Yeniləmələr (GitHub relizləri)

Tema yeni versiyaları özü tapır — ZIP yükləmək lazım deyil.

**Repo:** https://github.com/bybumer/fixit-wp-theme

**Necə işləyir:**
1. Dəyişiklik edilir, `style.css` və `functions.php`-də versiya artırılır
2. `python reliz-yarat.py "nə dəyişdi"` — GitHub-a reliz dərc olunur
3. WordPress ~12 saatdan bir yoxlayır və yeniləməni görür
4. Avtomatik yeniləmə açıqdırsa, özü quraşdırır; deyilsə *Başlanğıc → Yeniləmələr*-də görünür

**Dərhal yoxlamaq:** Görünüş → **FIXIT quraşdırma** → *Yeniləməni indi yoxla*.
Orada quraşdırılmış və son versiya, avtomatik yeniləmənin vəziyyəti görünür.

**Avtomatik yeniləməni açmaq (bir dəfəlik):** Görünüş → Temalar → FIXIT Pro →
*Avtomatik yeniləmələri aktivləşdir*.

**Versiya qaydası:** `1.4.0 → 1.4.1 → 1.4.2 …` kiçik düzəlişlər,
yeni bölmə/imkan olanda `1.5.0`. Hər iki faylda versiya eyni olmalıdır —
skript fərqli olsa dərc etmir.

**Təhlükəsizlik:** sayta heç bir parol və ya açar verilmir. Sayt yalnız GitHub-ın
açıq API-sindən son relizi oxuyur və arxivi WordPress-in öz quraşdırıcısı ilə yükləyir.

---

## 6. Nə edilib

- 5 tam səhifə: Ana səhifə, Xidmətlər, **Məhsullar**, Haqqımızda, Əlaqə
- **Hər məhsulun ayrıca səhifəsi** (`/mehsul/sorgu-sistemi/`, `/mehsul/remote/`,
  `/mehsul/sebeke-monitorinqi/`): problem → həll, imkan qrupları, ekran
  görüntüləri qalereyası, iş prinsipi diaqramı, təhlükəsizlik, demo bloku və suallar
- **Demo:** üç rejim — *sorğu ilə* (müştəri formu doldurur, girişi siz göndərirsiniz),
  *açıq* (düymə birbaşa demo ünvanına aparır), *söndürülüb*
- Menyuda *Məhsullar* altında hər məhsul (açılan menyu)
- Təmiz ünvanlar: WordPress "ə" hərfini kodlayırdı (`%c9%99`) — indi `e` olur,
  köhnə ünvanlar yeniyə yönləndirilir
- Məhsullar bölməsi: FIXIT Sorğu Sistemi və FIXIT Şəbəkə Monitorinqi —
  xüsusiyyətlər, on-premise satış şərtləri, demo prosesi və FAQ
  (qiymət yazılmır, "Təklif üçün əlaqə" modeli)
- İşıqlı / qaranlıq rejim (istifadəçinin seçimi yadda qalır)
- Tam adaptiv dizayn (mobil, planşet, masaüstü)
- Daxili əlaqə formu — plagin yoxdur, bot tələsi (honeypot) var,
  mesajlar admin panelə yazılır
- WhatsApp və zəng üçün üzən düymələr
- Xidmətlər, məhsullar və rəylər admin paneldən idarə olunur
- Məhsul səhifəsindəki "Demo təyin et" düyməsi əlaqə formunda mövzunu
  avtomatik seçir — müştəri hansı məhsulla maraqlandığı dərhal bilinir
- **SEO paketi:**
  - Hər səhifə üçün admin paneldən yazılan SEO başlığı və təsviri
    (canlı simvol sayğacı ilə), istənilən səhifəni `noindex` etmək imkanı
  - Meta təsvir, Open Graph, Twitter kartı, robots göstərişləri
  - Struktur məlumatlar: şirkət kartı, sayt kartı, naviqasiya zənciri və
    **sual-cavab (FAQ)** — Google-da zəngin nəticə şansı verir
  - Şablona görə hazır təsvirlər: səhifə boş olsa belə təsvirsiz qalmır
  - Axtarış nəticələri və 404 səhifəsi indeksləşmir
- **Admin paneldə SEO izləmə lövhəsi** (menyu: *FIXIT SEO*):
  sayt ayarlarının yoxlanışı (indeksləşmə, HTTPS, sitemap, loqo, favicon…),
  hər səhifə üzrə bal, başlıq/təsvir uzunluğu və Google önizləməsi
- Sürət: xarici kitabxana yoxdur, yalnız Google Fonts

## 7. Sonrakı addımlar üçün ideyalar

- Bloq bölməsi (şablonlar hazırdır — sadəcə yazı əlavə etmək kifayətdir)
- Real müştəri loqoları ilə "Tərəfdaşlar" bölməsi
- Çoxdilli dəstək (Polylang və ya WPML)
- Xidmət səhifələrinin hər biri üçün ayrıca ünvan (SEO üçün faydalı)

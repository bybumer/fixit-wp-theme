// demo.fixit.az ekran görüntüləri - başsız Chrome-u DevTools protokolu ilə idarə edir.
// Birbaşa işlədilmir: tools/demo-ekranlar.py Chrome-u açır və bu faylı çağırır.
//
// node demo-ekranlar.mjs <debug-port> <çıxış-qovluğu>
//
// Node 22+ lazımdır (daxili WebSocket) - heç bir npm paketi tələb olunmur.
import { writeFileSync, mkdirSync } from 'node:fs';
import { join } from 'node:path';

const port = process.argv[2] || '9333';
const out = process.argv[3] || '.';
mkdirSync(out, { recursive: true });

const BASE = 'https://demo.fixit.az/portal';

// Demo əvvəl real şirkət adları işlədirdi (tk1.20.5-də uydurma adlarla əvəz
// olundu). Marketinq şəklində real ad «bizim müştərimizdir» kimi oxunar -
// ona görə səhifədə bunlardan biri görünsə şəkil ÇƏKİLMİR, skript dayanır.
const FORBIDDEN = /SOCAR|Pasha Holding|Kapital Bank|Azərenerji|Azerenerji|Bravo Supermarket|Azərsu|Azersu|socar\.az|pasha\.az|kapitalbank\.az/i;

// [fayl adı, ünvan, gözləmə ms, səhifədə edilən hərəkət] - adlar demo-ekranlar.py-dəki
// sıra ilə eynidir. Demo hər gecə yenidən yaranır və ID-lər dəyişir - ona görə tək
// sorğu sabit nömrə ilə yox, siyahıdakı sorğulardan biri kimi açılır (FIRST_TICKET).
const FIRST_TICKET = 'FIRST_TICKET';
const SHOTS = [
  // Siyahı və sorğu yan-yana: sətrə bir klik sorğunu sağ paneldə açır.
  ['sorgular', '/tickets?status=open', 2800, 'open-panel'],
  // Sorğunun tam səhifəsi: SLA nişanı, Müştəri paneli, «Həll olundu».
  ['sorgu', FIRST_TICKET, 2800, ''],
  // Ctrl+K: sorğu, tapşırıq və müştəri bir pəncərədən.
  ['axtaris', '/dashboards', 3000, 'palette'],
  ['dashboard', '/dashboards', 3500, ''],
  ['tapsiriqlar', '/tasks', 2800, ''],
  ['gantt', '/reports/view?type=gantt&kind=task', 3500, ''],
  ['vaxt-hesabati', '/time-report', 2800, ''],
  ['hesabatlar', '/reports/view?type=resolution&kind=ticket&days=90', 3500, ''],
];

// Yalnız bəzilərini çəkmək üçün: node demo-ekranlar.mjs PORT OUT sorgular,gantt
const ONLY = (process.argv[4] || '').split(',').filter(Boolean);

const list = await (await fetch(`http://127.0.0.1:${port}/json/list`)).json();
const ws = new WebSocket(list.find((t) => t.type === 'page').webSocketDebuggerUrl);
await new Promise((r) => ws.addEventListener('open', r, { once: true }));

let seq = 0;
const pending = new Map();
ws.addEventListener('message', (ev) => {
  const msg = JSON.parse(ev.data);
  if (msg.id && pending.has(msg.id)) {
    const { res, rej } = pending.get(msg.id);
    pending.delete(msg.id);
    msg.error ? rej(new Error(JSON.stringify(msg.error))) : res(msg.result);
  }
});
const send = (method, params = {}) =>
  new Promise((res, rej) => {
    const id = ++seq;
    pending.set(id, { res, rej });
    ws.send(JSON.stringify({ id, method, params }));
  });
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const evaluate = async (expression) =>
  (await send('Runtime.evaluate', { expression, awaitPromise: true, returnByValue: true })).result.value;
const go = async (url, wait) => {
  await send('Page.navigate', { url });
  await sleep(wait);
};
// Ctrl+K kimi qısayol (modifiers: 2 = Ctrl).
const chord = async (k, code, mods) => {
  const vk = k.toUpperCase().charCodeAt(0);
  await send('Input.dispatchKeyEvent', { type: 'keyDown', key: k, code, modifiers: mods, windowsVirtualKeyCode: vk });
  await send('Input.dispatchKeyEvent', { type: 'keyUp', key: k, code, modifiers: mods, windowsVirtualKeyCode: vk });
};

await send('Page.enable');
await send('Runtime.enable');
// 1440×900 iş masası, 2x sıxlıq - sonra 1600 px-ə kiçildilir, mətn kəskin qalır.
await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 900, deviceScaleFactor: 2, mobile: false });

// Giriş səhifəsindəki "Admin" demo düyməsi - parol yazılmır.
await go(`${BASE}/login`, 2500);
const clicked = await evaluate(`(() => {
  const b = [...document.querySelectorAll('button')].find(x => x.textContent.trim() === 'Admin');
  if (!b) return false; b.click(); return true; })()`);
if (!clicked) {
  console.error('XETA: "Admin" demo duymesi tapilmadi.');
  process.exit(1);
}
await sleep(3000);

const polish = `(() => {
  // Yuxarıdakı "demo versiyasıdır" zolağı məhsulun hissəsi deyil. Ən dərin
  // uyğun element götürülür - əks halda bütün tətbiqin kökü (#root) tutulur.
  const banner = [...document.querySelectorAll('body *')]
    .filter(el => /demo versiyas/i.test(el.textContent) && el.getBoundingClientRect().top < 40)
    .sort((a, b) => a.textContent.length - b.textContent.length)[0];
  if (banner) {
    let el = banner;
    while (el.parentElement && el.parentElement.getBoundingClientRect().height < 60) el = el.parentElement;
    el.style.display = 'none';
  }
  const s = document.createElement('style');
  s.textContent = '::-webkit-scrollbar{display:none} *{scrollbar-width:none}';
  document.head.appendChild(s);
})()`;

// Siyahının ilk sətirləri çox vaxt təzə, cavabsız sorğudur - üçüncünü seçirik,
// orada adətən yazışma və müştəri məlumatı olur.
const pickTicket = `(() => {
  const links = [...document.querySelectorAll('a[href]')].filter(x => /^\\/portal\\/tickets\\/\\d+$/.test(x.getAttribute('href')));
  const a = links[Math.min(2, links.length - 1)];
  return a ? a.getAttribute('href') : ''; })()`;

for (const [name, path, wait, action] of SHOTS) {
  if (ONLY.length && !ONLY.includes(name)) continue;

  if (path === FIRST_TICKET) {
    await go(`${BASE}/tickets?status=open`, wait);
    const href = await evaluate(pickTicket);
    if (!href) {
      console.error('XETA: siyahida sorgu tapilmadi.');
      process.exit(1);
    }
    await go(new URL(href, BASE).href, wait);
  } else {
    await go(BASE + path, wait);
  }

  if (action === 'open-panel') {
    // Sətrə bir klik: sorğu sağ paneldə açılır, siyahı yerində qalır.
    const ok = await evaluate(`(() => {
      const rows = [...document.querySelectorAll('tbody tr')];
      const target = rows[Math.min(2, rows.length - 1)];
      if (!target) return false;
      (target.querySelector('td:nth-child(3)') || target).click();
      return true; })()`);
    if (!ok) console.error('XEBERDARLIQ: panel acilmadi (' + name + ')');
    await sleep(2000);
  } else if (action === 'palette') {
    await chord('k', 'KeyK', 2);
    await sleep(800);
    await send('Input.insertText', { text: 'print' });
    await sleep(1600);
  }

  // Boş və ya xəta səhifəsi qalereyaya düşməsin.
  const broken = await evaluate(`/tapılmadı|not found/i.test(document.body.innerText) || document.body.innerText.trim().length < 200`);
  if (broken) {
    console.error('XETA: ' + name + ' sehifesi bos ve ya tapilmadi - sekil cekilmedi.');
    process.exit(1);
  }
  const text = await evaluate(`document.body.innerText + ' ' + [...document.querySelectorAll('input,textarea,select')].map(e => e.value).join(' ')`);
  const real = text.match(FORBIDDEN);
  if (real) {
    console.error('XETA: ' + name + ' sehifesinde real sirket adi var (' + real[0] + ') - demo_seed.py-ni yoxlayin.');
    process.exit(1);
  }

  await evaluate(polish);
  await sleep(600);
  const { data } = await send('Page.captureScreenshot', { format: 'png' });
  writeFileSync(join(out, `${name}.png`), Buffer.from(data, 'base64'));
  console.log('cekildi:', name);
}

ws.close();
process.exit(0);

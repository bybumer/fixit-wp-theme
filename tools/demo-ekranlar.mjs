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

// [fayl adı, ünvan, gözləmə ms] - adlar demo-ekranlar.py-dəki sıra ilə eynidir.
// Demo hər gecə yenidən yaranır və ID-lər dəyişir - ona görə tək sorğu
// sabit nömrə ilə yox, siyahıdakı birinci sorğu kimi açılır (FIRST_TICKET).
const FIRST_TICKET = 'FIRST_TICKET';
const SHOTS = [
  ['sorgular', '/tickets?status=open', 2500],
  ['sorgu', FIRST_TICKET, 2500],
  ['lovhe', '/board', 2500],
  ['tapsiriqlar', '/tasks', 2500],
  ['vaxt-hesabati', '/time-report', 2500],
  ['hesabatlar', '/reports', 2500],
];

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

for (const [name, path, wait] of SHOTS) {
  if (path === FIRST_TICKET) {
    await go(`${BASE}/tickets?status=open`, wait);
    const href = await evaluate(`(() => {
      const a = [...document.querySelectorAll('a[href]')].find(x => /^\\/portal\\/tickets\\/\\d+$/.test(x.getAttribute('href')));
      return a ? a.getAttribute('href') : ''; })()`);
    if (!href) {
      console.error('XETA: siyahida sorgu tapilmadi.');
      process.exit(1);
    }
    await go(new URL(href, BASE).href, wait);
  } else {
    await go(BASE + path, wait);
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

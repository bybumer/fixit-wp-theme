// remote-demo.fixit.az ekran görüntüləri - başsız Chrome-u DevTools protokolu ilə idarə edir.
// Birbaşa işlədilmir: tools/remote-ekranlar.py Chrome-u açır və bu faylı çağırır.
//
// node remote-ekranlar.mjs <debug-port> <çıxış-qovluğu> [demo ünvanı]
//
// Node 22+ lazımdır (daxili WebSocket) - heç bir npm paketi tələb olunmur.
import { writeFileSync, mkdirSync } from 'node:fs';
import { join } from 'node:path';

const port = process.argv[2] || '9334';
const out = process.argv[3] || '.';
const BASE = (process.argv[4] || 'https://remote-demo.fixit.az').replace(/\/$/, '');
mkdirSync(out, { recursive: true });

// [fayl adı, ünvan, gözləmə ms] - adlar remote-ekranlar.py-dəki sıra ilə eynidir.
// VIEWER: ekran pəncərəsi - işçinin öz kompüteri (ortaq terminal yox).
// CHAT: cihazlar siyahısı və yazışması olan kompüterin çat paneli.
const VIEWER = 'VIEWER';
const CHAT = 'CHAT';
const SHOTS = [
  ['ekran', VIEWER, 3500],
  ['cihazlar', '/#devices', 2500],
  ['idare-lovhesi', '/#dashboard', 3000],
  ['yazisma', CHAT, 2500],
  ['sessiyalar', '/#sessions', 2500],
  ['komandalar', '/#teams', 2500],
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
// 1920×1200 iş masası: cihazlar cədvəli tam sığır. 2x sıxlıq - sonra 1600 px-ə
// kiçildilir, mətn kəskin qalır. Saytdakı Sorğu Sistemi şəkilləri kimi tünd mövzu.
await send('Emulation.setDeviceMetricsOverride', { width: 1920, height: 1200, deviceScaleFactor: 2, mobile: false });
await send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-color-scheme', value: 'dark' }] });

// Panelin dili azərbaycancadır (başsız Chrome-un öz dili ingiliscədir).
await go(`${BASE}/login.html?lang=az`, 1500);
await evaluate(`localStorage.setItem('fixit.language', 'az'); localStorage.setItem('fixit.theme', 'dark'); location.reload(); true`);
await sleep(2500);

// Giriş səhifəsindəki "Administrator" demo kartı - parol yazılmır.
const clicked = await evaluate(`(() => {
  const b = [...document.querySelectorAll('button')].find(x => x.textContent.trim().startsWith('Administrator'));
  if (!b) return false; b.click(); return true; })()`);
if (!clicked) {
  console.error('XETA: "Administrator" demo karti tapilmadi.');
  process.exit(1);
}
await sleep(3500);

// Ziyarətçilərin həqiqi IP-si marketinq şəklinə düşməsin (nümunə məlumatdakı
// daxili ünvanlar qalır). Panelin "Nümayiş" nişanı məhsulun hissəsi deyil.
const polish = `(() => {
  const isPrivate = (ip) => /^(10|127)\\./.test(ip) || /^192\\.168\\./.test(ip) || /^172\\.(1[6-9]|2\\d|3[01])\\./.test(ip);
  const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
  let n = 0;
  for (let node = walker.nextNode(); node; node = walker.nextNode()) {
    const next = node.nodeValue.replace(/\\b\\d{1,3}(\\.\\d{1,3}){3}\\b/g, (ip) => isPrivate(ip) ? ip : '192.168.10.' + (21 + (n++ % 9)));
    if (next !== node.nodeValue) node.nodeValue = next;
  }
  for (const el of document.querySelectorAll('header span, header a')) {
    if (/^(Nümayiş|Öz serverinizi istəyirsiniz\\?)$/.test(el.textContent.trim())) el.style.display = 'none';
  }
  const note = document.getElementById('note');
  if (note) note.style.display = 'none';
  const s = document.createElement('style');
  s.textContent = '::-webkit-scrollbar{display:none} *{scrollbar-width:none}';
  document.head.appendChild(s);
})()`;

for (const [name, path, wait] of SHOTS) {
  if (path === VIEWER) {
    // Paneldəki "Qoşul" kimi: demo-viewer.html eyni parametrlərlə.
    const url = await evaluate(`(async () => {
      const me = await (await fetch('/api/auth/me')).json().catch(() => ({}));
      const list = await (await fetch('/api/devices')).json();
      const d = list.find(x => x.online && x.approved && x.owner && x.phone && x.canControl);
      if (!d) return '';
      const q = new URLSearchParams({ deviceId: d.deviceId, name: d.hostname, owner: d.owner, code: d.code || '',
        operator: (me && (me.name || me.displayName)) || 'Kamran Əhmədov' });
      return '/demo-viewer.html?' + q; })()`);
    if (!url) {
      console.error('XETA: onlayn kompüter tapilmadi.');
      process.exit(1);
    }
    await go(BASE + url, wait);
  } else if (path === CHAT) {
    await go(BASE + '/#devices', wait);
    // Ən çox mesajı olan kompüterin çatı (məlumat hər gecə yenidən yaranır).
    const opened = await evaluate(`(async () => {
      const list = await (await fetch('/api/devices')).json();
      let best = null, most = 0;
      for (const d of list) {
        const chat = await (await fetch('/api/devices/' + d.deviceId + '/chat?peek=1')).json();
        if (Array.isArray(chat) && chat.length > most) { best = d; most = chat.length; }
      }
      if (!best) return false;
      const row = [...document.querySelectorAll('main tbody tr')].find(r => r.textContent.includes(best.hostname));
      const button = row && row.querySelector('[aria-label="Çat"]');
      if (!button) return false;
      button.click();
      return true; })()`);
    if (!opened) {
      console.error('XETA: yazismasi olan komputer tapilmadi.');
      process.exit(1);
    }
    await sleep(2000);
  } else {
    await go(BASE + path, wait);
  }

  const broken = await evaluate(`document.body.innerText.trim().length < 80`);
  if (broken) {
    console.error('XETA: ' + name + ' sehifesi bosdur - sekil cekilmedi.');
    process.exit(1);
  }

  await evaluate(polish);
  await sleep(600);
  const { data } = await send('Page.captureScreenshot', { format: 'png' });
  writeFileSync(join(out, `${name}.png`), Buffer.from(data, 'base64'));
  console.log('cekildi:', name);

  // Çat paneli açıq qalmasın: növbəti şəkillərə düşər.
  if (path === CHAT) {
    await evaluate(`location.reload(); true`);
    await sleep(2500);
  }
}

ws.close();
process.exit(0);

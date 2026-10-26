import {createServer} from 'node:http';
import {readFile, mkdir} from 'node:fs/promises';
import {resolve, extname} from 'node:path';
import assert from 'node:assert/strict';
import {fileURLToPath, pathToFileURL} from 'node:url';
const standRoot=process.env.FAIR_STAND_ROOT || fileURLToPath(new URL('../../../kyrox-fair-stand',import.meta.url));
const {chromium}=await import(pathToFileURL(resolve(standRoot,'node_modules/playwright/index.mjs')).href);
const root=fileURLToPath(new URL('../../tema/kyrox',import.meta.url)).replace(/[\\\/]$/,'');
const info=await readFile(`${root}/inc/bilgilendirme.php`,'utf8');
const panel=info.slice(info.indexOf('<section class="kyrox-storage-prompt"'),info.indexOf('\n\t<?php',info.indexOf('<section class="kyrox-storage-prompt"'))).replace(/<\?php[\s\S]*?\?>/g,'/cerezler/');
const stand=(await readFile(`${root}/inc/baslangic/fuar-stand.html`,'utf8')).replaceAll('{{STAND_ASSETS}}','/assets/stand-v3');
const html=`<!doctype html><html lang="tr"><head><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/stand-v3/styles-v3.css"><link rel="stylesheet" href="/assets/css/depolama.css"></head><body>${stand}<button data-storage-open>Depolama tercihleri</button>${panel}<script src="/assets/js/depolama.js" defer></script><script src="/assets/stand-v3/interactions-v3.js" defer></script></body></html>`;
const server=createServer(async(req,res)=>{
 const path=new URL(req.url,'http://local').pathname;
 if(path==='/'){res.setHeader('content-type','text/html; charset=utf-8');return res.end(html);}
 const file=resolve(root,`.${decodeURIComponent(path)}`);
 if(!file.startsWith(root+'/')&&!file.startsWith(root+'\\'))return res.writeHead(403).end();
 try{res.setHeader('content-type',({'.js':'text/javascript','.css':'text/css','.woff2':'font/woff2','.svg':'image/svg+xml','.webp':'image/webp','.glb':'model/gltf-binary'})[extname(file)]||'text/plain');res.end(await readFile(file));}catch{res.writeHead(404).end();}
});
await new Promise(resolve=>server.listen(4394,'127.0.0.1',resolve));
const browser=await chromium.launch();
const base='http://127.0.0.1:4394/';
const key='kyrox.storage-preference.v1', cache='kyrox-stand-scenes-v1';
const ready=p=>p.waitForSelector('[data-showcase][data-state="ready"]',{timeout:45000});
const cacheKeys=p=>p.evaluate(()=>caches.keys());
try{
 const context=await browser.newContext();const p=await context.newPage();const errors=[];p.on('pageerror',e=>errors.push(e.message));
 await p.goto(base);await ready(p);assert.equal((await cacheKeys(p)).includes(cache),false);assert(await p.locator('[data-storage-prompt]').isVisible());
 await p.locator('[data-storage-allow]').click();await p.reload();await ready(p);assert((await cacheKeys(p)).includes(cache));
 let requests=0;await context.route('**/elikon.glb*',route=>{requests++;return route.abort();});await p.reload();await ready(p);assert.equal(requests,0);await context.unroute('**/elikon.glb*');
 await p.evaluate(async()=>{const c=await caches.open('unrelated-cache');await c.put('/sentinel',new Response('keep'));});
 await p.locator('button[data-storage-open]').first().click();await p.locator('[data-storage-model]').uncheck();await p.locator('[data-storage-save]').click();
 assert.equal((await cacheKeys(p)).includes(cache),false);assert((await cacheKeys(p)).includes('unrelated-cache'));
 await p.reload();await ready(p);assert.equal((await cacheKeys(p)).includes(cache),false);assert.equal(await p.locator('[data-storage-prompt]').isVisible(),false);
 await p.locator('button[data-storage-open]').first().click();await p.keyboard.press('Escape');assert.equal(await p.locator('dialog').isVisible(),false);
 await p.evaluate(k=>localStorage.setItem(k,JSON.stringify({version:1,modelCache:true,expires:1})),key);await p.reload();await ready(p);assert.equal((await cacheKeys(p)).includes(cache),false);assert(await p.locator('[data-storage-prompt]').isVisible());
 await p.setViewportSize({width:390,height:844});assert(await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));if(process.env.WEB_PREFERENCE_EVIDENCE){await mkdir(process.env.WEB_PREFERENCE_EVIDENCE,{recursive:true});await p.screenshot({path:resolve(process.env.WEB_PREFERENCE_EVIDENCE,'preference-mobile.png'),fullPage:false});}
 await p.locator('[data-storage-deny]').click();const second=await context.newPage();await second.goto(base);await ready(second);
 await second.locator('button[data-storage-open]').first().click();await second.locator('[data-storage-model]').check();await second.locator('[data-storage-save]').click();await p.reload();await ready(p);assert((await cacheKeys(p)).includes(cache));
 await second.locator('button[data-storage-open]').first().click();await second.locator('[data-storage-model]').uncheck();await second.locator('[data-storage-save]').click();await p.waitForFunction(()=>globalThis.kyroxSceneStorage.allowed()===false);assert.equal((await cacheKeys(p)).includes(cache),false);
 const blocked=await browser.newContext();await blocked.addInitScript(()=>{Object.defineProperty(window,'localStorage',{get(){throw Error('blocked');}});Object.defineProperty(window,'caches',{value:undefined});});const b=await blocked.newPage();await b.goto(base);await ready(b);await b.locator('[data-storage-deny]').click();assert.match(await b.locator('[data-storage-status]').textContent(),/yalnız bu sayfa/);await blocked.close();
 const race=await browser.newContext();await race.addInitScript(k=>localStorage.setItem(k,JSON.stringify({version:1,modelCache:true,expires:Date.now()+60000})),key);
 let release,requested;const hold=new Promise(r=>release=r);const requestSeen=new Promise(r=>requested=r);
 await race.route('**/elikon.glb*',async route=>{requested();await hold;await route.continue();});const rp=await race.newPage();await rp.goto(base);await requestSeen;
 await rp.locator('button[data-storage-open]').first().click();await rp.locator('[data-storage-model]').uncheck();await rp.locator('[data-storage-save]').click();release();await ready(rp);assert.equal((await cacheKeys(rp)).includes(cache),false);await race.close();
 assert.deepEqual(errors,[]);console.log('PASS: default deny, auto scene, allow/cache hit, revoke including in-flight load, unrelated cache preservation, expiry, cross-tab, blocked storage, mobile and Escape.');
}finally{await browser.close();await new Promise(resolve=>server.close(resolve));}

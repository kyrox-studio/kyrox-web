// V3 görüntüleyicisini ürünün mevcut Three.js/Vite bağımlılıklarıyla derler.
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { existsSync } from 'node:fs';
const webRoot=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'../..');
const standRoot=process.env.FAIR_STAND_ROOT||path.resolve(webRoot,'../kyrox-fair-stand');
const vite=path.join(standRoot,'node_modules/vite/dist/node/index.js');
if(!existsSync(vite))throw new Error('Fair Stand bağımlılıkları gerekli. FAIR_STAND_ROOT ile kurulu ürün klasörünü belirtin.');
const {build}=await import(pathToFileURL(vite).href);
await build({configFile:false,resolve:{alias:[
  {find:/^three$/,replacement:path.join(standRoot,'node_modules/three/build/three.module.js')},
  {find:'three/addons',replacement:path.join(standRoot,'node_modules/three/examples/jsm')},
]},build:{lib:{entry:path.join(webRoot,'tasarim/stand-v3/viewer.js'),formats:['es'],fileName:()=> 'viewer.js'},outDir:path.join(webRoot,'tema/kyrox/assets/stand-v3'),emptyOutDir:false,minify:true}});

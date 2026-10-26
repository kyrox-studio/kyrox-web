// FAIR_STAND_ROOT=/path/to/kyrox-fair-stand node tasarim/scripts/build-viewer.mjs
// Ürünle aynı Three.js/Vite sürümünden yalnız salt okunur tanıtım görüntüleyicisini paketler.
import {createRequire} from 'node:module';
import {fileURLToPath, pathToFileURL} from 'node:url';
import {resolve} from 'node:path';
const root=fileURLToPath(new URL('../..',import.meta.url));
const stand=process.env.FAIR_STAND_ROOT || resolve(root,'../kyrox-fair-stand');
const require=createRequire(resolve(stand,'package.json'));
const {build}=await import(pathToFileURL(require.resolve('vite')).href);
const three=resolve(stand,'node_modules/three');
await build({root,configFile:false,publicDir:false,
 resolve:{alias:[{find:'three/addons',replacement:resolve(three,'examples/jsm')},{find:'three',replacement:three}]},
 build:{outDir:resolve(root,'tema/kyrox/assets/stand-v3'),emptyOutDir:false,
 lib:{entry:resolve(root,'tasarim/stand-v3/viewer.js'),formats:['es'],fileName:()=> 'viewer.js'},
 rolldownOptions:{output:{codeSplitting:false}}}
});

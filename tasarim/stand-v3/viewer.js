import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { MeshoptDecoder } from 'three/addons/libs/meshopt_decoder.module.js';

/** Yalnız kamera: dışa aktarılmış gerçek sahnede düzenleme veya API bağlantısı yoktur. */
export async function createViewer({ container, url, signal, onProgress = () => {} }) {
  const renderer = new THREE.WebGLRenderer({antialias:true,alpha:false});
  renderer.setPixelRatio(Math.min(devicePixelRatio,1.5));
  renderer.outputColorSpace=THREE.SRGBColorSpace;
  renderer.toneMapping=THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure=1.08;
  const scene=new THREE.Scene();scene.background=new THREE.Color(0x191b1e);
  const camera=new THREE.PerspectiveCamera(38,1,0.1,150);
  const controls=new OrbitControls(camera,renderer.domElement);
  controls.enableDamping=true;controls.dampingFactor=0.09;controls.enablePan=false;
  controls.maxPolarAngle=Math.PI*0.49;controls.minPolarAngle=Math.PI*0.08;
  const hemi=new THREE.HemisphereLight(0xb7bdc5,0xffffff,3.6);scene.add(hemi);
  const key=new THREE.DirectionalLight(0xffffff,2.3);key.position.set(-5,12,12);scene.add(key);
  let root=null,frame=0,disposed=false,inView=true;
  function schedule(){if(disposed||frame||document.hidden||!inView)return;frame=requestAnimationFrame(draw);}
  function draw(){frame=0;if(disposed||document.hidden||!inView)return;const moved=controls.update();renderer.render(scene,camera);if(moved)schedule();}
  const resize=()=>{const w=Math.max(1,container.clientWidth),h=Math.max(1,container.clientHeight);renderer.setSize(w,h,false);camera.aspect=w/h;camera.updateProjectionMatrix();schedule();};
  const observer=new ResizeObserver(resize);
  const intersection=new IntersectionObserver(entries=>{inView=entries[0]?.isIntersecting??true;if(inView)schedule();else if(frame){cancelAnimationFrame(frame);frame=0;}});
  const visibility=()=>{if(document.hidden){cancelAnimationFrame(frame);frame=0;}else schedule();};
  const resources=new Set();
  function dispose(){if(disposed)return;disposed=true;cancelAnimationFrame(frame);observer.disconnect();intersection.disconnect();document.removeEventListener('visibilitychange',visibility);controls.dispose();root?.traverse(node=>{node.geometry?.dispose();for(const material of [node.material].flat().filter(Boolean)){for(const value of Object.values(material))if(value?.isTexture)resources.add(value);material.dispose();}});resources.forEach(t=>t.dispose());renderer.dispose();renderer.domElement.remove();}
  try {
    const response=await fetch(url,{signal,credentials:'omit'});
    if(!response.ok)throw new Error('Sahne dosyası yüklenemedi.');
    const total=Number(response.headers.get('content-length'))||0;
    const reader=response.body?.getReader();let binary;
    if(reader){const chunks=[];let size=0;while(true){const {done,value}=await reader.read();if(done)break;chunks.push(value);size+=value.length;onProgress(total?Math.min(99,Math.round(size/total*100)):null);}const merged=new Uint8Array(size);let offset=0;for(const chunk of chunks){merged.set(chunk,offset);offset+=chunk.length;}binary=merged.buffer;}
    else binary=await response.arrayBuffer();
    signal?.throwIfAborted();
    const loader=new GLTFLoader();loader.setMeshoptDecoder(MeshoptDecoder);
    const gltf=await loader.parseAsync(binary,new URL('.',url).href);
    signal?.throwIfAborted();root=gltf.scene;scene.add(root);
    const box=new THREE.Box3().setFromObject(root),center=box.getCenter(new THREE.Vector3()),size=box.getSize(new THREE.Vector3());
    const radius=Math.max(size.x,size.z,size.y)*0.9;
    const target=new THREE.Vector3(center.x,Math.max(1.35,size.y*0.43),center.z);
    function home(){const halfFov=THREE.MathUtils.degToRad(camera.fov/2);const distance=(Math.max(size.x/(2*Math.tan(halfFov)*camera.aspect),size.y/(2*Math.tan(halfFov)),size.z)+size.z*0.55)*1.25;controls.target.copy(target);camera.position.copy(target).add(new THREE.Vector3(-0.14,0.55,1).normalize().multiplyScalar(distance));camera.lookAt(target);controls.update();schedule();}
    controls.minDistance=Math.max(2,size.y);controls.maxDistance=radius*3.3;
    container.appendChild(renderer.domElement);renderer.domElement.tabIndex=0;
    renderer.domElement.setAttribute('aria-label','Elikon standının gerçek 3D sahnesi. Ok tuşlarıyla döndürün, artı ve eksiyle yakınlaştırın, Home ile başlangıca dönün.');
    const orbit=direction=>{const spherical=new THREE.Spherical().setFromVector3(camera.position.clone().sub(controls.target));spherical.theta+=direction*Math.PI/12;camera.position.copy(controls.target).add(new THREE.Vector3().setFromSpherical(spherical));controls.update();schedule();};
    const zoom=direction=>{const offset=camera.position.clone().sub(controls.target);offset.multiplyScalar(direction>0?0.85:1.18);offset.setLength(THREE.MathUtils.clamp(offset.length(),controls.minDistance,controls.maxDistance));camera.position.copy(controls.target).add(offset);controls.update();schedule();};
    renderer.domElement.addEventListener('keydown',event=>{if(event.key==='ArrowLeft'){orbit(-1);}else if(event.key==='ArrowRight'){orbit(1);}else if(event.key==='+'||event.key==='='){zoom(1);}else if(event.key==='-'){zoom(-1);}else if(event.key==='Home'){home();}else return;event.preventDefault();});
    controls.addEventListener('change',schedule);controls.addEventListener('start',schedule);
    document.addEventListener('visibilitychange',visibility);observer.observe(container);intersection.observe(container);
    resize();home();onProgress(100);
    return {home,orbit,zoom,dispose,position:()=>camera.position.toArray(),capture:()=>{renderer.render(scene,camera);return renderer.domElement.toDataURL('image/png');}};
  } catch(error){dispose();throw error;}
}

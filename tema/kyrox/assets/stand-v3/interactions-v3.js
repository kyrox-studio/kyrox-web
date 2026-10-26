(() => {
  const slides=[...document.querySelectorAll('[data-proof-slide]')],dots=[...document.querySelectorAll('[data-proof-dot]')];
  let index=0;
  function show(next){index=(next+slides.length)%slides.length;slides.forEach((slide,i)=>{slide.classList.toggle('active',i===index);slide.hidden=i!==index;});dots.forEach((dot,i)=>{dot.classList.toggle('active',i===index);dot.setAttribute('aria-pressed',String(i===index));});}
  document.getElementById('proofPrev')?.addEventListener('click',()=>show(index-1));
  document.getElementById('proofNext')?.addEventListener('click',()=>show(index+1));
  dots.forEach((dot,i)=>dot.addEventListener('click',()=>show(i)));if(slides.length)show(0);
  document.querySelectorAll('[data-compare]').forEach(box=>{const range=box.querySelector('.proof-range');const set=value=>{const n=Math.max(0,Math.min(100,Number(value)||0));box.style.setProperty('--split',n+'%');range.value=String(n);};range.addEventListener('input',()=>set(range.value));box.addEventListener('pointerdown',event=>{if(event.pointerType==='mouse'&&event.button!==0)return;box.setPointerCapture?.(event.pointerId);const r=box.getBoundingClientRect();set((event.clientX-r.left)/r.width*100);});box.addEventListener('pointermove',event=>{if(!box.hasPointerCapture?.(event.pointerId))return;const r=box.getBoundingClientRect();set((event.clientX-r.left)/r.width*100);});});
  const shell=document.querySelector('[data-showcase]');if(!shell)return;
  const trigger=shell.querySelector('[data-load-3d]'),status=shell.querySelector('[data-showcase-status]'),host=shell.querySelector('[data-showcase-canvas]'),cover=shell.querySelector('[data-showcase-cover]'),toolbar=shell.querySelector('[data-showcase-toolbar]');
  const script=document.currentScript;const base=new URL('.',script.src);
  const viewerUrl=new URL('viewer.js',base);viewerUrl.searchParams.set('v',new URL(script.src).searchParams.get('ver')||'1.3.1');
  const modelUrl=new URL('elikon.glb',base);modelUrl.searchParams.set('v','730f0a9111277729');
  let viewer=null,loading=false,controller=null;
  async function load({focus=false}={}){if(loading||viewer)return;loading=true;cover.hidden=false;toolbar.hidden=true;trigger.hidden=false;trigger.disabled=true;trigger.textContent='Sahne yükleniyor…';status.textContent='Elikon projesi hazırlanıyor. Birazdan standı kendiniz döndürebileceksiniz.';shell.dataset.state='loading';controller=new AbortController();
    try{const {createViewer}=await import(viewerUrl.href);viewer=await createViewer({container:host,url:modelUrl.href,signal:controller.signal,onProgress:percent=>{status.textContent=percent==null?'3D sahne hazırlanıyor…':`3D sahne hazırlanıyor: %${percent}`;}});cover.hidden=true;trigger.hidden=true;toolbar.hidden=false;shell.dataset.state='ready';status.textContent='Gerçek Elikon projesi · Döndürmek için sürükleyin, yakınlaştırmak için kaydırın veya iki parmağınızı kullanın.';if(focus)host.querySelector('canvas')?.focus({preventScroll:true});}
    catch(error){viewer?.dispose();viewer=null;shell.dataset.state='error';trigger.disabled=false;trigger.textContent='Tekrar dene';status.textContent='3D sahne açılamadı. Proje görselini inceleyebilir veya yeniden deneyebilirsiniz.';}
    finally{loading=false;}
  }
  trigger.addEventListener('click',()=>load({focus:true}));
  toolbar.querySelector('[data-view-home]').addEventListener('click',()=>viewer?.home());
  toolbar.querySelector('[data-view-left]').addEventListener('click',()=>viewer?.orbit(-1));
  toolbar.querySelector('[data-view-right]').addEventListener('click',()=>viewer?.orbit(1));
  toolbar.querySelector('[data-view-in]').addEventListener('click',()=>viewer?.zoom(1));
  toolbar.querySelector('[data-view-out]').addEventListener('click',()=>viewer?.zoom(-1));
  addEventListener('pagehide',()=>{controller?.abort();viewer?.dispose();});
  addEventListener('pageshow',event=>{if(event.persisted){viewer=null;loading=false;void load();}});
  requestAnimationFrame(()=>{void load();});
})();

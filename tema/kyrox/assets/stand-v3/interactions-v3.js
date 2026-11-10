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
  const ada=shell.dataset.showcaseProject==='simes-ada';
  const modelUrl=new URL(ada?'simes-ada.glb':'elikon.glb',base);modelUrl.searchParams.set('v',ada?'5f3fbde69d4df62b':'730f0a9111277729');
  const project=ada?'3DM + SİMES':'Elikon';
  let viewer=null,pending=null,controller=null,active=true,opening=false,resume=false;
  host.inert=true;
  function prepare(){
    if(viewer)return Promise.resolve(viewer);
    if(pending)return pending;
    const attempt=new AbortController();controller=attempt;
    pending=(async()=>{
      const {createViewer}=await import(viewerUrl.href);
      attempt.signal.throwIfAborted();
      const result=await createViewer({container:host,url:modelUrl.href,signal:attempt.signal,projectLabel:project,cornerView:ada,onProgress:percent=>{
        if(opening&&!attempt.signal.aborted)status.textContent=percent==null?'3D sahne hazırlanıyor…':`3D sahne hazırlanıyor: %${percent}`;
      }});
      if(attempt.signal.aborted){result.dispose();attempt.signal.throwIfAborted();}
      viewer=result;
      return result;
    })().finally(()=>{if(controller===attempt){pending=null;controller=null;}});
    return pending;
  }
  function display(focus=false){
    host.inert=false;cover.hidden=true;trigger.hidden=true;toolbar.hidden=false;shell.dataset.state='ready';
    status.textContent=`Gerçek ${project} projesi · Döndürmek için sürükleyin, yakınlaştırmak için kaydırın veya iki parmağınızı kullanın.`;
    if(focus)host.querySelector('canvas')?.focus({preventScroll:true});
  }
  async function reveal({focus=true}={}){
    if(opening||shell.dataset.state==='ready')return;
    opening=true;trigger.disabled=true;trigger.textContent='Sahne yükleniyor…';shell.dataset.state='loading';
    status.textContent=`${project} projesi hazırlanıyor. Birazdan standı kendiniz döndürebileceksiniz.`;
    const job=prepare();
    try{
      const result=await job;
      if(!active||viewer!==result)return;
      display(focus);
    }catch(error){
      if(!active||error.name==='AbortError')return;
      shell.dataset.state='error';trigger.disabled=false;trigger.textContent='Tekrar dene';
      status.textContent='3D sahne açılamadı. Proje görselini inceleyebilir veya yeniden deneyebilirsiniz.';
    }finally{opening=false;}
  }
  function warm(){
    const connection=navigator.connection;
    if(connection?.saveData||/^(slow-)?2g$/.test(connection?.effectiveType??''))return;
    const run=()=>{
      if(!active||document.hidden||shell.dataset.state!=='idle')return;
      void prepare().then(result=>{
        if(active&&viewer===result&&shell.dataset.state==='idle')display(document.activeElement===trigger);
      }).catch(()=>{});
    };
    if('requestIdleCallback' in window)window.requestIdleCallback(run,{timeout:1200});else window.setTimeout(run,250);
  }
  trigger.addEventListener('click',()=>void reveal());
  if(document.readyState==='complete')warm();else addEventListener('load',warm,{once:true});
  document.addEventListener('visibilitychange',()=>{if(!document.hidden&&document.readyState==='complete')warm();});
  toolbar.querySelector('[data-view-home]').addEventListener('click',()=>viewer?.home());
  toolbar.querySelector('[data-view-left]').addEventListener('click',()=>viewer?.orbit(-1));
  toolbar.querySelector('[data-view-right]').addEventListener('click',()=>viewer?.orbit(1));
  toolbar.querySelector('[data-view-in]').addEventListener('click',()=>viewer?.zoom(1));
  toolbar.querySelector('[data-view-out]').addEventListener('click',()=>viewer?.zoom(-1));
  const initialStatus=status.textContent,initialLabel=trigger.textContent;
  addEventListener('pagehide',()=>{
    resume=opening||shell.dataset.state==='ready';active=false;controller?.abort();controller=null;pending=null;
    viewer?.dispose();viewer=null;
  });
  addEventListener('pageshow',event=>{
    if(!event.persisted)return;
    active=true;opening=false;host.inert=true;cover.hidden=false;trigger.hidden=false;trigger.disabled=false;toolbar.hidden=true;
    shell.dataset.state='idle';status.textContent=initialStatus;trigger.textContent=initialLabel;
    if(resume)void reveal({focus:false});else warm();
  });
})();

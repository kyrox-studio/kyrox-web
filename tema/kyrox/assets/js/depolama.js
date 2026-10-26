(() => {
  'use strict';
  const key = 'kyrox.storage-preference.v1';
  const cacheName = 'kyrox-stand-scenes-v1';
  const lifetime = 180 * 24 * 60 * 60 * 1000;
  let memory = null;
  let storageAvailable = true;
  function read() {
    if (!storageAvailable) return memory;
    try {
      const value = JSON.parse(localStorage.getItem(key) || 'null');
      return value?.version === 1 && typeof value.modelCache === 'boolean' &&
        Number.isFinite(value.expires) && value.expires > Date.now() ? value : null;
    } catch { storageAvailable = false; return memory; }
  }
  async function clearCache() {
    try { await globalThis.caches?.delete(cacheName); return true; }
    catch { return false; }
  }
  function allowed() { return read()?.modelCache === true; }
  globalThis.kyroxSceneStorage = Object.freeze({ allowed });
  const prompt = document.querySelector('[data-storage-prompt]');
  const dialog = document.querySelector('[data-storage-dialog]');
  const checkbox = document.querySelector('[data-storage-model]');
  const status = document.querySelector('[data-storage-status]');
  let opener = null;
  function sync() {
    const value = read();
    if (prompt) prompt.hidden = value !== null;
    if (checkbox) checkbox.checked = value?.modelCache === true;
  }
  async function save(modelCache) {
    memory = { version: 1, modelCache, expires: Date.now() + lifetime };
    try { localStorage.setItem(key, JSON.stringify(memory)); }
    catch { storageAvailable = false; }
    const cleared = modelCache || await clearCache();
    sync();
    if (prompt) prompt.hidden = true;
    if (status) status.textContent = !cleared
      ? 'Tercih kaydedildi; eski 3D önbelleği tarayıcı tarafından silinemedi. Site verileri ayarlarından temizleyebilirsiniz.'
      : storageAvailable ? 'Tercih kaydedildi.' : 'Tarayıcı depolaması kapalı; tercih yalnız bu sayfa için geçerli.';
    if (dialog?.open) { dialog.close(); opener?.focus(); }
  }
  document.querySelectorAll('[data-storage-allow]').forEach(button => button.addEventListener('click', () => void save(true)));
  document.querySelectorAll('[data-storage-deny]').forEach(button => button.addEventListener('click', () => void save(false)));
  document.querySelectorAll('[data-storage-open]').forEach(button => button.addEventListener('click', () => {
    sync(); opener = button; dialog?.showModal();
  }));
  document.querySelector('[data-storage-save]')?.addEventListener('click', () => void save(Boolean(checkbox?.checked)));
  document.querySelector('[data-storage-close]')?.addEventListener('click', () => { dialog?.close(); opener?.focus(); });
  dialog?.addEventListener('cancel', () => { queueMicrotask(() => opener?.focus()); });
  addEventListener('storage', event => {
    if (event.key === key || event.key === null) { sync(); if (!allowed()) void clearCache(); }
  });
  sync();
  if (!allowed()) void clearCache();
})();

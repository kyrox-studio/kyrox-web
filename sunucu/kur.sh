#!/usr/bin/env bash
#
# kyrox.studio WordPress kurulum sihirbazı (sunucu).
# Önce GitHub'daki yeni değişiklikleri sorarak çeker (git pull).
# Adım adım sorar: alan adı, yönetici hesabı, SSL. Tekrar çalıştırılabilir; yapılmış
# adımları atlar (kurulu WordPress'e, yazılmış nginx sitesine, alınmış sertifikaya dokunmaz).
#
# Kullanım (repo /opt/kyrox/kyrox-web altında):
#   sudo bash /opt/kyrox/kyrox-web/sunucu/kur.sh
#
set -Eeuo pipefail

SUNUCU_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${SUNUCU_DIR}/.env"
NGINX_SITE="/etc/nginx/sites-available/kyrox-studio"
NGINX_LINK="/etc/nginx/sites-enabled/kyrox-studio"
NGINX_PROXY="/etc/nginx/kyrox-web-proxy.conf"
PORT=8090
PROMPT_INPUT="${PROMPT_INPUT:-/dev/tty}"
TOTAL_STEPS=7

log() { echo "[kurulum] $*"; }
die() { echo "[kurulum] HATA: $*" >&2; exit 1; }
trap 'echo "[kurulum] HATA: satır ${LINENO}: ${BASH_COMMAND}" >&2' ERR

step_title() {
  echo
  echo "────────────────────────────────────────────────────────────"
  echo " ADIM $1/${TOTAL_STEPS}: $2"
  echo "────────────────────────────────────────────────────────────"
}

ask() {
  local label="$1" current="$2" answer=""
  read -r -p "${label} [${current}]: " answer < "$PROMPT_INPUT"
  printf '%s' "${answer:-$current}"
}

ask_yes_no() {
  local label="$1" default="$2" answer="" hint
  if [[ "$default" == "E" ]]; then hint="E/h"; else hint="e/H"; fi
  while true; do
    read -r -p "${label} [${hint}]: " answer < "$PROMPT_INPUT"
    answer="${answer:-$default}"
    case "${answer,,}" in
      e|evet|y|yes) return 0 ;;
      h|hayır|hayir|n|no) return 1 ;;
      *) echo "  Lütfen e (evet) ya da h (hayır) yazın." ;;
    esac
  done
}

compose() { docker compose --project-directory "$SUNUCU_DIR" -f "${SUNUCU_DIR}/docker-compose.yml" "$@"; }
wp() { compose run --rm -T cli wp "$@"; }

env_get() { grep -E "^$1=" "$ENV_FILE" 2>/dev/null | head -1 | cut -d= -f2-; }
env_set() {
  if grep -qE "^$1=" "$ENV_FILE"; then
    sed -i "s#^$1=.*#$1=$2#" "$ENV_FILE"
  else
    echo "$1=$2" >> "$ENV_FILE"
  fi
}
gizli() { openssl rand -base64 48 | tr -d '\n/+=' | cut -c1-64; }

# GitHub'daki yeni değişiklikleri sorarak çeker (yalnız ileri sarma; yerel değişiklik
# varsa dokunmaz). kur.sh'nin kendisi değiştiyse yeni sürümle baştan başlar.
guncelle_repo() {
  local repo once sonra geride
  repo="$(dirname "$SUNUCU_DIR")"
  if [[ -n "${KUR_GUNCELLENDI:-}" ]]; then
    log "Kod güncel ($(git -C "$repo" log -1 --format='%h %s'))."
    return 0
  fi
  if ! command -v git >/dev/null || ! git -C "$repo" rev-parse --git-dir >/dev/null 2>&1; then
    log "UYARI: ${repo} bir git deposu değil; güncelleme denetimi atlandı."
    return 0
  fi
  if ! git -C "$repo" fetch --quiet 2>/dev/null; then
    log "UYARI: GitHub'a ulaşılamadı; mevcut kodla devam ediliyor."
    return 0
  fi
  if ! git -C "$repo" rev-parse --verify --quiet '@{u}' >/dev/null; then
    log "UYARI: $(git -C "$repo" branch --show-current) dalının GitHub karşılığı yok; güncelleme atlandı."
    return 0
  fi
  geride="$(git -C "$repo" rev-list --count 'HEAD..@{u}')"
  if (( geride == 0 )); then
    log "Kod güncel ($(git -C "$repo" log -1 --format='%h %s'))."
    return 0
  fi
  echo "  GitHub'da ${geride} yeni değişiklik var:"
  git -C "$repo" log -n 10 --format='    %h %s' 'HEAD..@{u}'
  if [[ -n "$(git -C "$repo" status --porcelain --untracked-files=no)" ]]; then
    log "UYARI: ${repo} içinde kaydedilmemiş değişiklikler var; güncelleme atlandı:"
    git -C "$repo" status --short --untracked-files=no | sed 's/^/    /'
    return 0
  fi
  ask_yes_no "Bu değişiklikler çekilsin mi (git pull)?" "E" || { log "Güncelleme atlandı; mevcut kodla devam ediliyor."; return 0; }
  once="$(git -C "$repo" hash-object "${SUNUCU_DIR}/kur.sh")"
  git -C "$repo" pull --ff-only --quiet || die "git pull başarısız (dal GitHub'dan ayrılmış olabilir); ${repo} içinde elle bakın"
  log "Güncellendi: $(git -C "$repo" log -1 --format='%h %s')"
  sonra="$(git -C "$repo" hash-object "${SUNUCU_DIR}/kur.sh")"
  if [[ "$once" != "$sonra" ]]; then
    log "Sihirbazın kendisi de güncellendi; yeni sürümle baştan başlatılıyor."
    KUR_GUNCELLENDI=1 exec bash "${SUNUCU_DIR}/kur.sh" "$@"
  fi
}

# ─────────────────────────────────────────────────────────────
step_title 1 "Ön kontroller"
[[ "$(id -u)" -eq 0 ]] || die "root olarak çalıştırın: sudo bash $0"
{ : < "$PROMPT_INPUT"; } 2>/dev/null || die "Soru sorulabilecek bir terminal yok. ssh ile bağlanırken -t kullanın."
command -v docker >/dev/null || die "docker kurulu değil"
docker compose version >/dev/null 2>&1 || die "docker compose eklentisi yok"
command -v nginx >/dev/null || die "nginx kurulu değil"
command -v openssl >/dev/null || die "openssl kurulu değil"
guncelle_repo "$@"
bos_mb="$(free -m | awk '/^Mem:/ {print $7}')"
log "Kullanılabilir bellek: ${bos_mb} MB (WordPress + MariaDB yaklaşık 300 MB kullanır)"
(( bos_mb >= 400 )) || ask_yes_no "Bellek az görünüyor. Yine de devam edilsin mi?" "H" || die "İptal edildi"
if ss -tln | grep -qE "127\.0\.0\.1:${PORT}\b" && ! docker ps --format '{{.Names}}' | grep -qx kyrox-web; then
  die "127.0.0.1:${PORT} başka bir uygulama tarafından kullanılıyor"
fi
log "Tamam."

# ─────────────────────────────────────────────────────────────
step_title 2 "Alan adı ve e-posta"
ALAN="$(ask "Sitenin alan adı (www olmadan)" "kyrox.studio")"
[[ "$ALAN" =~ ^[a-z0-9.-]+\.[a-z]{2,}$ ]] || die "Geçersiz alan adı: ${ALAN}"
EPOSTA="$(ask "Yönetici ve SSL bildirimleri için e-posta" "hello@kyrox.studio")"
[[ "$EPOSTA" == *@*.* ]] || die "Geçersiz e-posta: ${EPOSTA}"
SERTIFIKA="/etc/letsencrypt/live/${ALAN}/fullchain.pem"
if [[ -f "$SERTIFIKA" ]]; then SEMA=https; else SEMA=http; fi

# ─────────────────────────────────────────────────────────────
step_title 3 "Ayar dosyası (.env)"
if [[ -f "$ENV_FILE" ]]; then
  log "${ENV_FILE} zaten var; şifreler korunuyor."
else
  umask 077
  cp "${SUNUCU_DIR}/.env.example" "$ENV_FILE"
  env_set WP_DB_PASSWORD "$(gizli)"
  env_set WP_DB_ROOT_PASSWORD "$(gizli)"
  for k in WP_AUTH_KEY WP_SECURE_AUTH_KEY WP_LOGGED_IN_KEY WP_NONCE_KEY WP_AUTH_SALT WP_SECURE_AUTH_SALT WP_LOGGED_IN_SALT WP_NONCE_SALT; do
    env_set "$k" "$(gizli)"
  done
  log "${ENV_FILE} oluşturuldu; veritabanı şifreleri ve WordPress anahtarları rastgele üretildi."
fi
chmod 600 "$ENV_FILE"
env_set WP_PORT "$PORT"
env_set WP_HOME "${SEMA}://${ALAN}"
log "Site adresi: ${SEMA}://${ALAN}"

# ─────────────────────────────────────────────────────────────
step_title 4 "WordPress ve MariaDB kapsayıcıları"
compose up -d db wordpress
for _ in $(seq 1 60); do
  kod="$(curl -s -o /dev/null -w '%{http_code}' -H "Host: ${ALAN}" "http://127.0.0.1:${PORT}/wp-login.php" || true)"
  [[ "$kod" =~ ^(200|302)$ ]] && break
  sleep 2
done
[[ "$kod" =~ ^(200|302)$ ]] || die "WordPress yanıt vermiyor (HTTP ${kod}); docker logs kyrox-web ile bakın"
log "WordPress çalışıyor (127.0.0.1:${PORT})."

# ─────────────────────────────────────────────────────────────
step_title 5 "WordPress kurulumu ve yönetici hesabı"
if wp core is-installed >/dev/null 2>&1; then
  log "WordPress zaten kurulu; yönetici hesabına dokunulmadı."
else
  BASLIK="$(ask "Site adı" "Kyrox Studio")"
  while true; do
    KULLANICI="$(ask "Yönetici kullanıcı adı (admin olmasın)" "kyrox")"
    if [[ ! "$KULLANICI" =~ ^[a-zA-Z0-9._-]+$ ]]; then
      echo "  Kullanıcı adında yalnız harf, rakam, nokta, alt çizgi ve tire olabilir; @ ve boşluk olmaz."
      echo "  (E-posta adresi değil; girişte kullanıcı adı yerine e-postayı da yazabilirsiniz.)"
      continue
    fi
    if (( ${#KULLANICI} < 3 )); then echo "  Kullanıcı adı en az 3 karakter olmalı."; continue; fi
    if [[ "${KULLANICI,,}" == "admin" ]]; then echo "  'admin' tahmin edilmesi kolay; başka bir ad seçin."; continue; fi
    break
  done
  echo "  Yönetici şifresini girin (en az 12 karakter; ekranda görünmez)."
  while true; do
    read -r -s -p "  Şifre: " SIFRE < "$PROMPT_INPUT"; echo
    read -r -s -p "  Şifre (tekrar): " SIFRE2 < "$PROMPT_INPUT"; echo
    if [[ "$SIFRE" != "$SIFRE2" ]]; then echo "  Şifreler aynı değil, tekrar deneyin."; continue; fi
    if (( ${#SIFRE} < 12 )); then echo "  Şifre en az 12 karakter olmalı."; continue; fi
    break
  done
  # Şifre komut satırına yazılmaz; WP-CLI onu standart girdiden okur.
  printf '%s' "$SIFRE" | wp core install --url="${SEMA}://${ALAN}" --title="$BASLIK" \
    --admin_user="$KULLANICI" --admin_email="$EPOSTA" --skip-email --prompt=admin_password >/dev/null
  unset SIFRE SIFRE2
  log "WordPress kuruldu; yönetici: ${KULLANICI}"
fi
wp language core install tr_TR --activate >/dev/null 2>&1 || log "UYARI: Türkçe dil paketi kurulamadı (sonra Ayarlar > Genel'den seçilebilir)"
if [[ "$(wp theme list --status=active --field=name 2>/dev/null)" != "kyrox" ]]; then
  wp theme activate kyrox >/dev/null
  log "Kyrox Studio teması etkinleştirildi (sayfalar, örnek yazı ve iş kartları kuruldu)."
else
  log "Kyrox Studio teması zaten etkin."
fi
wp rewrite flush >/dev/null

# ─────────────────────────────────────────────────────────────
step_title 6 "nginx (${ALAN} ve www.${ALAN})"
sed "s/__PORT__/${PORT}/g" "${SUNUCU_DIR}/nginx-proxy.conf" > "$NGINX_PROXY"
if [[ -f "$NGINX_SITE" ]]; then
  log "${NGINX_SITE} zaten var; üzerine yazılmadı (certbot'un eklediği HTTPS ayarları korunur)."
else
  sed -e "s/__ALAN__/${ALAN}/g" -e "s/__PORT__/${PORT}/g" "${SUNUCU_DIR}/nginx-site.conf" > "$NGINX_SITE"
  ln -sf "$NGINX_SITE" "$NGINX_LINK"
  log "${NGINX_SITE} yazıldı ve etkinleştirildi."
fi
if ! nginx -t 2>/dev/null; then
  nginx -t || true
  die "nginx ayarı hatalı; nginx yeniden yüklenmedi, mevcut siteler etkilenmedi"
fi
systemctl reload nginx
log "nginx yeniden yüklendi."

# ─────────────────────────────────────────────────────────────
step_title 7 "SSL (Let's Encrypt)"
if [[ -f "$SERTIFIKA" ]]; then
  log "Sertifika zaten var: ${SERTIFIKA}"
else
  sunucu_ip="$(curl -s -4 --max-time 10 https://api.ipify.org || true)"
  dns_ip="$(getent ahostsv4 "$ALAN" | awk 'NR==1 {print $1}')"
  www_ip="$(getent ahostsv4 "www.${ALAN}" | awk 'NR==1 {print $1}')"
  log "Sunucu IP: ${sunucu_ip:-?}  ${ALAN} → ${dns_ip:-?}  www.${ALAN} → ${www_ip:-?}"
  if [[ -z "$sunucu_ip" || "$dns_ip" != "$sunucu_ip" || "$www_ip" != "$sunucu_ip" ]]; then
    log "DNS henüz bu sunucuyu göstermiyor; SSL atlandı. A kayıtları yayılınca sihirbazı tekrar çalıştırın."
  elif ! command -v certbot >/dev/null; then
    log "certbot kurulu değil; SSL atlandı (apt-get install certbot python3-certbot-nginx)."
  else
    echo "  Let's Encrypt'ten ${ALAN} ve www.${ALAN} için ücretsiz sertifika alınacak."
    echo "  Kullanım koşulları: https://letsencrypt.org/repository/"
    if ask_yes_no "Let's Encrypt kullanım koşullarını kabul edip sertifika alınsın mı?" "E"; then
      certbot --nginx -d "$ALAN" -d "www.${ALAN}" -m "$EPOSTA" --agree-tos --no-eff-email --redirect --non-interactive
      env_set WP_HOME "https://${ALAN}"
      compose up -d wordpress
      log "SSL kuruldu; site adresi https://${ALAN} olarak güncellendi."
    else
      log "SSL atlandı."
    fi
  fi
fi

# ─────────────────────────────────────────────────────────────
echo
SITE="$(env_get WP_HOME)"
log "Kurulum tamamlandı."
log "Site:   ${SITE}/"
log "Panel:  ${SITE}/wp-admin/"
log "Yedek:  WordPress veritabanını ve dosyalarını gece yedeğine eklemek için yedek sihirbazını tekrar çalıştırın:"
log "        sudo bash /opt/kyrox/kyrox-fair-crm/scripts/server/backup/install-backup.sh"

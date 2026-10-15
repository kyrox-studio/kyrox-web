#!/bin/sh
# Temadaki bütün PHP dosyalarının sözdizimini kontrol eder (php -l). CI ve yerelde kullanılır.
set -e
dizin="${1:-$(dirname "$0")/kyrox}"
hata=0
for f in $(find "$dizin" -name '*.php' | sort); do
  if ! php -l "$f" >/dev/null 2>&1; then
    php -l "$f" || true
    hata=1
  fi
done
[ "$hata" -eq 0 ] && echo "PHP sözdizimi: tamam ($(find "$dizin" -name '*.php' | wc -l) dosya)"
exit "$hata"

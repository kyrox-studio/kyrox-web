# Yapay zekâ ile görsel üretmek için hazır komutlar

Midjourney, ChatGPT (görsel), Gemini veya benzeri bir araçta kullanmak için. Komutlar İngilizce, çünkü bu araçlar İngilizcede daha tutarlı sonuç veriyor. Her komutun sonuna aynı **stil eki** geliyor; böylece bütün görseller aynı ışıkta, aynı renklerde çıkar ve sitenin koyu-bakır havasına oturur.

## Önce kural: ne üretilir, ne üretilmez

| Üretilebilir (atmosfer) | Üretilmez (gerçek fotoğraf olmalı) |
|---|---|
| Açılıştaki CRAFT tarafı: ahşap, talaş, tezgâh | İşler kartlarındaki ürünler: ahşap rakle, kutu harf |
| Atölyeden genel kareler | "Bunu biz yaptık" diye gösterilen her iş |
| Blog yazılarının kapak görselleri | Müşteri işleri, fuar standları |
| Malzeme yakın çekimleri (ahşap, metal, filament) | Ekip ve kişi fotoğrafları |

Sebep basit: müşteri gerçek ürünü gördüğünde fark eder ve güven kaybolur. Ürün gerçekten çıkana kadar sitedeki teknik çizimler kalır.

## Stil eki (her komutun sonuna ekleyin)

```
moody workshop photography, dark charcoal background (#111210), warm copper side light, deep shadows, natural materials, brushed steel, oiled walnut and oak wood, dark brown leather, shallow depth of field, shot on 50mm, editorial, minimal, quiet, premium, realistic textures, no text, no logos, no watermark, no people's faces
```

Midjourney kullanıyorsanız sona ayrıca şunu ekleyin: `--style raw --ar 16:9` (oran her görselde aşağıda yazıyor).

## 1. Açılış, CRAFT tarafı

Sitede: açılıştaki sürüklenen ekranın sağ yarısı. Oran 16:9, en az 1920 × 1080.

```
close-up of a workbench edge with a freshly planed walnut board, curled wood shavings, a steel try square resting on the wood, light falling from the right side, [STİL EKİ]
```

Alternatif:

```
macro of hands-free woodworking scene, oak plank with visible grain on a dark workbench, fine sawdust in a beam of warm light, a few copper-colored brass screws, [STİL EKİ]
```

## 2. Atölyeden genel kare

Sitede: ileride eklenecek "Atölyeden" şeridi. Oran 4:3.

```
small modern craft studio at dusk, a 3D printer and a compact CNC router softly out of focus in the background, wooden workbench in the foreground with tools neatly arranged, single warm lamp, [STİL EKİ]
```

## 3. 3D yazıcı

Sitede: "Atölyede neler var?" bölümündeki 3D baskı kutusu. Oran 4:3.

```
macro shot of an FDM 3D printer nozzle laying a copper-colored filament layer, visible layer lines on a partially printed thick letter shape, dark enclosure, [STİL EKİ]
```

## 4. CNC

Sitede: CNC & Ahşap kutusu. Oran 4:3.

```
CNC router bit cutting a curved line into a plywood sheet, fine dust in the air, motion frozen, steel gantry partly visible, [STİL EKİ]
```

## 5. Yazılım

Sitede: Yazılım kutusu. Oran 4:3.

```
laptop on a wooden workbench showing an abstract dark dashboard with copper accents, blurred wood shavings and a caliper next to it, the screen content is abstract shapes only, [STİL EKİ]
```

## 6. Blog kapakları

Oran 1200 × 630 (yatay). Yazının konusuna göre baştaki nesneyi değiştirin.

```
flat lay on dark charcoal surface: [KONU NESNESİ], arranged with generous empty space on the left, [STİL EKİ]
```

`[KONU NESNESİ]` için örnekler:

- Kutu harf yazısı: `a few thick 3D printed letter pieces and an offcut of opal acrylic`
- CNC yazısı: `a plywood offcut with a clean curved cut and two router bits`
- Yazılım yazısı: `a notebook with pencil sketches of a screen layout and a steel ruler`

## Görseli siteye koymak

1. Görseli JPG olarak, en fazla 2000 px genişlikte kaydedin. Boyut 300 KB'ı geçmesin; geçerse squoosh.app gibi bir araçla sıkıştırın.
2. Dosya adı Türkçe karakter ve boşluk içermesin: `craft-ceviz-tezgah.jpg`
3. Atmosfer görselleri `public/gorsel/`, blog görselleri `public/blog-gorsel/` klasörüne gider.
4. Hangi görselin nereye gideceğini Claude'a söyleyin, yerleştirsin.

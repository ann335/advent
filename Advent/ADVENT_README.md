![ROSME Logo](https://www.rosme.com/image/catalog/logo3.svg)

# Adventes Kalendārs - ROSME

Interaktīvs adventes kalendārs ar admin paneli, sniega animāciju un dinamisku saturu.

## Mapju struktūra
```
advent/
├── php/
│   ├── index.php                 (galvenā kalendāra lapa)
│   ├── admin.php                 (admin panelis)
│   ├── admin_advent_index.php    (preview režīms)
│   └── advent.sql                (datubāzes struktūra)
├── css/
│   ├── style.css                 (kalendāra stils)
│   └── style_for_admin.css       (admin paneļa stils)
├── js/
│   └── script_for_admin.js       (admin funkcionalitāte)
└── img/
    ├── ribbon.png                (dekoratīvās lentes)
    ├── day1.png ... day24.png    (dienu ikonas)
    └── popup_day1.jpeg ... popup_day24.jpeg  (popup attēli)
```

## Galvenās funkcijas

### Adventes kalendārs (index.php)
- **24 dienas** ar dinamisku saturu no datubāzes
- **Datumu kontrole** - dienas kļūst aktīvas pēc iestatītā sākuma datuma
- **Inactive stāvoklis** - nākotnes dienas ir pelēkas un neaktīvas
- **Popup logs** - attēls + virsraksts + apakšvirsraksts + saturs + footer
- **Sniega animācija** - Canvas elements ar pielāgojamu daudzumu un ātrumu
- **Dekoratīvie elementi** - lentes (ribbon) ar pozicionēšanu un rotāciju
- **Responsīvs dizains**:
  - Desktop: 5 kolonnas (12vw kvadrāti)
  - Tablet: 4 kolonnas (18vw kvadrāti)
  - Mobile: 3 kolonnas (25vw kvadrāti)

### Admin panelis (admin.php)
- **Trīs galvenās cilnes**:
  - **Dienas saturs** - rediģē katru dienu atsevišķi
  - **Globālie stili** - vispārējie iestatījumi
  - **Priekšskatījums** - atver preview logu
  
- **Dienas rediģēšana**:
  - Dropdown izvēle - pārslēdzas starp 24 dienām
  - Attēla augšupielāde - popup_dayX.jpeg
  - Attēla dzēšana - ar AJAX
  - Virsraksts (title)
  - Apakšvirsraksts (subtitle)
  - Rich text editor saturam (contenteditable)
  - Rich text editor kājenei (footer)
  - Dienas fona krāsa (color picker)
  - Dienas teksta krāsa (color picker)
  
- **Globālie stili**:
  - Kalendāra fons
  - Popup fons
  - Kalendāra sākuma datums (date picker)
  - Fontu ģimene
  - Sniega daudzums (0-500)
  - Sniega ātrums (0.1-5)
  
- **AJAX saglabāšana** - bez lapas pārlādēšanas

### Preview režīms (admin_advent_index.php)
- Pilnfunkcionāls kalendārs
- Visas dienas ir aktīvas (nav datumu ierobežojuma)
- Testa režīms pirms publicēšanas

## Datubāze

**Tabula: `day_content`**
- day_id (1-24)
- popup_image - ceļš uz attēlu
- title - virsraksts
- subtitle - apakšvirsraksts
- content - HTML saturs (rich text)
- footer - HTML kājene

**Tabula: `day_styles`**
- day_id (1-24)
- day_bg_color - dienas fons (#hex)
- day_text_color - dienas teksts (#hex)
- icon_path - ikonas fails (day1.png...day24.png)

**Tabula: `global_styles`**
- name/value pāri:
  - calendar_background - kalendāra fons
  - popup_background - popup fons
  - font_family - fontu ģimene
  - snow_count - sniega daudzums
  - snow_speed - sniega ātrums
  - calendar_start_date - kalendāra sākuma datums

## Vizuālie elementi

### Sniega animācija
- Canvas elements ar fiksētu pozicionēšanu
- Dinamiskas pārsniņas ar:
  - Random x/y pozīcijām
  - Random rādiusu (1-5px)
  - Sinusoidālu kustību (šūpošanās)
  - Vertikālu kritienu ar ātrumu
- Pielāgojams daudzums un ātrums no admin paneļa

### Dekoratīvās lentes (ribbons)
- 2 lentes kalendāra malās
- PNG attēli ar transform:
  - Kreisā: `scaleX(-1) rotate(25deg)`
  - Labā: `rotate(25deg)`
- Responsīvs pozicionējums

### Dienu ikonas
- day1.png ... day24.png
- Pozicionētas ar absolute:
  - Top/left procentos
  - Rotācija dažādos leņķos (-25° līdz +25°)
- Pielāgojami izmēri (4vw/6vw/8vw)

## Datumu loģika

```php
// Kalendāra sākuma datums no DB
$calendarStartDate = "2026-02-01";
$startDate = new DateTime($calendarStartDate);

// Katrai dienai
for ($i = 1; $i <= 24; $i++) {
    $dayDate = clone $startDate;
    $dayDate->modify('+' . ($i - 1) . ' days');
    
    // Inactive ja vēl nav iestājusies
    $inactive = ($dayDate > $today) ? 'inactive' : '';
}
```

## Rich Text Editor

Admin panelī izmanto `contenteditable` div:
```html
<div id="editor" class="editor" contenteditable="true">
  <!-- HTML saturs -->
</div>
<textarea name="content" id="content" hidden></textarea>
```

Sinhronizācija pirms submit:
```javascript
function syncContent() {
    document.getElementById('content').value = 
        document.getElementById('editor').innerHTML;
}
```

## Instalācija

## 1. Lejupielāde

1. Lejupielādē **visus projekta failus** no repozitorija vai zip arhīva.  
2. Pārliecinies, ka visi faili un mapes ir saglabāti vienā vietā, saglabājot to struktūru: 
```
advent/
  ├── php/
    ├── index.php                 (galvenā kalendāra lapa)
    ├── admin.php                 (admin panelis)
    ├── admin_advent_index.php    (preview režīms)
    └── advent.sql                (datubāzes struktūra)
  ├── css/
    ├── style.css                 (kalendāra stils)
    └── style_for_admin.css       (admin paneļa stils)
  ├── js/
    └── script_for_admin.js       (admin funkcionalitāte)
  └── img/
    ├── ribbon.png                (dekoratīvās lentes)
    ├── day1.png ... day24.png    (dienu ikonas)
    └── popup_day1.jpeg ... popup_day24.jpeg  (popup attēli)
```
## 2. Servera izvēle

Šim projektam nepieciešams lokāls PHP + MySQL serveris. Ieteicams izmantot:  

- **Serveris:** XAMPP  
- **Versija:** 7.2.34  
- **Darba vide:** Windows  
- **PHP rediģēšana:** Visual Studio Code (versija 1.109.2)

## 3. Servera palaišana

1. Instalē **XAMPP** (ja vēl nav instalēts).  
2. Palaid **XAMPP Control Panel**.  
3. Ieslēdz:  
- **Apache**  
- **MySQL**  
4. Pārliecinies, ka nav konfliktu ar citiem serveriem vai portiem.


## 4. Projekta ievietošana serverī

1. Atver XAMPP instalācijas mapi, parasti: 

`C:\xampp\`

2. Atver mapi **htdocs**: 

`C:\xampp\htdocs\`

3. Kopē visu projekta mapi `Advent` uz **htdocs**: 

`C:\xampp\htdocs\Advent\`

4. Pārbaudi, ka struktūra saglabāta, piemēram:
  
```
htdocs/
  advent/
      ├── php/
      ├── css/
      ├── img/
      ├── js/
   
```

## 5. Projekta palaišana
Atver pārlūkprogrammu un dodies uz galveno lapu:
http://localhost/Advent/php/index.php

Lai piekļūtu administrācijas panelim:
http://localhost/Advent/php/admin.php

## 6. Rediģēšana un apskate
Visus PHP, CSS un JS failus var apskatīt un rediģēt ar Visual Studio Code.

## 7. Konfigurācija

1. Atver **PHP failus**, kas izmanto datubāzi (`index.php`, `admin.php`, utt.)  
2. Ja vajag rediģēq **DB kredenciālus**, lai tie atbilstu XAMPP iestatījumiem:  

```php
$host = 'localhost';
$db   = 'advent'; 
$user = 'root';
$pass = ''; 
```
---

## Responsive breakpoints

- **Desktop (>824px)**: 5 kolonnas, 12vw kvadrāti
- **Tablet (481-824px)**: 4 kolonnas, 18vw kvadrāti
- **Mobile (<480px)**: 3 kolonnas, 25vw kvadrāti



---



<?php
date_default_timezone_set('Europe/Riga');

$host = 'localhost';
$db   = 'advent';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset"; 
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $dbExists = $pdo->query("SHOW DATABASES LIKE '$db'")->fetch();

    if (!$dbExists) {
     
        $pdo->exec("CREATE DATABASE `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "Datubāze izveidota.<br>";
    }

    
    $pdo->exec("USE `$db`");

    
    $sqlFile = 'advent.sql'; 
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $pdo->exec($sql);
        echo "SQL fails izpildīts.<br>";
    } else {
        echo "SQL fails nav atrasts.<br>";
    }

} catch (\PDOException $e) {
    die("Savienojuma kļūda: " . $e->getMessage());
}



$global_styles = $pdo->query("SELECT name,value FROM global_styles")
                     ->fetchAll(PDO::FETCH_KEY_PAIR);

$day_styles = $pdo->query("SELECT * FROM day_styles ORDER BY day_id")
                  ->fetchAll(PDO::FETCH_UNIQUE);

$day_content = $pdo->query("SELECT * FROM day_content ORDER BY day_id")
                   ->fetchAll(PDO::FETCH_UNIQUE);

$snow_count = $global_styles['snow_count'] ?? 100;
$snow_speed = $global_styles['snow_speed'] ?? 1;


$calendarStartDate = $global_styles['calendar_start_date'] ?? "2026-01-02";

$today = new DateTime('today');

$startDate = new DateTime($calendarStartDate);
?>
<!DOCTYPE html>
<html lang="lv">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Adventes Kalendārs</title>
<link rel="stylesheet" href="../css/style.css">
<style>
body {
    background-color: <?= $global_styles['calendar_background'] ?? '#3E030A'; ?>;
    font-family: <?= $global_styles['font_family'] ?? 'Canelia Light'; ?>;
}
.letter-popup{background-color: <?= $global_styles['popup_background'] ?? '#FFDAE1'; ?>;}
</style>
</head>
<body>

<div class="decor-layer">
  <img src="../img/ribbon.png" class="ribbon">
  <img src="../img/ribbon.png" class="ribbon2">
</div>

<h1>Adventes Kalendārs</h1>

<div class="calendar-wrapper">
  <div class="calendar">
<?php
for ($i = 1; $i <= 24; $i++):
    $day_bg   = $day_styles[$i]['day_bg_color'] ?? '#fff';
    $day_text = $day_styles[$i]['day_text_color'] ?? '#000';
    $icon_file = $day_styles[$i]['icon_path'] ?? "day$i.png";

    
    $dayDate = clone $startDate;
    $dayDate->modify('+' . ($i - 1) . ' days');

    
    $inactive = ($dayDate > $today) ? 'inactive' : '';
?>
    <div class="day <?= $inactive ?>" data-day="<?= $i ?>"
         style="background-color: <?= $day_bg ?>; color: <?= $day_text ?>;">
        <img src="../img/<?= $icon_file ?>" class="icon<?= $i ?>" alt="icon<?= $i ?>">
        <span><?= $i ?></span>
    </div>
<?php endfor; ?>
  </div>
</div>

<div class="overlay" id="overlay">
  <div class="letter-popup">
    <button class="close" id="close">×</button>
    <img src="" class="letter-bg" id="popup-img">
    <div class="present">
      <h2 id="popup-title"></h2>
      <h3 id="popup-subtitle"></h3>
      <div class="popup-content" id="popup-content"></div>
    </div>
    <div class="footer" id="popup-footer"></div>
  </div>
</div>

<script>
const data = <?= json_encode($day_content) ?>;

// Funkcija kas atver dienu
function openDay(dayNumber) {

    const d = data[dayNumber];
    if (!d) return;

    document.getElementById('popup-img').src =
        d.popup_image ? d.popup_image : '';

    document.getElementById('popup-title').innerText = d.title || '';
    document.getElementById('popup-subtitle').innerHTML = d.subtitle || '';
    document.getElementById('popup-content').innerHTML = d.content || '';
    document.getElementById('popup-footer').innerHTML = d.footer || '';

    document.getElementById('overlay').style.display = 'flex';
}

document.querySelectorAll('.day').forEach(day => {
    day.addEventListener('click', function () {

        const dayNumber = this.getAttribute('data-day');

        // Ja diena nav pieejama
        if (this.classList.contains('inactive')) {
            alert("Šī diena vēl nav pieejama!");
            return;
        }

        openDay(dayNumber);
    });
});

// Aizvēršana
document.getElementById('close').onclick = () =>
    document.getElementById('overlay').style.display = 'none';

document.getElementById('overlay').onclick = e => {
    if (e.target.id === 'overlay')
        document.getElementById('overlay').style.display = 'none';
};
</script>

<canvas id="snowCanvas"
 style="position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:9999;">
</canvas>

<script>
const canvas = document.getElementById('snowCanvas');
const ctx = canvas.getContext('2d');
let width = canvas.width = window.innerWidth;
let height = canvas.height = window.innerHeight;

const flakesCount = <?= $snow_count ?>;
const speed = <?= $snow_speed ?>;

const flakes = [];
for (let i = 0; i < flakesCount; i++) {
  flakes.push({
    x: Math.random() * width,
    y: Math.random() * height,
    r: Math.random() * 4 + 1,
    d: Math.random() * flakesCount
  });
}

function drawFlakes() {
  ctx.clearRect(0, 0, width, height);
  ctx.fillStyle = "white";
  ctx.beginPath();
  for (const f of flakes) {
    ctx.moveTo(f.x, f.y);
    ctx.arc(f.x, f.y, f.r, 0, Math.PI * 2, true);
  }
  ctx.fill();
  moveFlakes();
}

let angle = 0;
function moveFlakes() {
  angle += 0.01;
  for (const f of flakes) {
    f.y += Math.cos(angle + f.d) + speed + f.r / 2;
    f.x += Math.sin(angle) * 2;

    if (f.y > height) { f.y = 0; f.x = Math.random() * width; }
    if (f.x > width) f.x = 0;
    if (f.x < 0) f.x = width;
  }
}

function update() {
  drawFlakes();
  requestAnimationFrame(update);
}

window.onresize = () => {
  width = canvas.width = window.innerWidth;
  height = canvas.height = window.innerHeight;
};

update();
</script>

</body>
</html>
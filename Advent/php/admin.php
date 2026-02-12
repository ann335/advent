<?php
$host = 'localhost';
$db   = 'advent';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset";
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);


$dbExists = $pdo->query("SHOW DATABASES LIKE '$db'")->fetch();

if (!$dbExists) {
    $pdo->exec("CREATE DATABASE `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
}


$pdo->exec("USE `$db`");

$pdo = new PDO(
    "mysql:host=localhost;dbname=advent;charset=utf8mb4",
    "root",
    "",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

// ================= SAVE =================
$response = ['saved'=>false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tab'])) {
    $tab = $_POST['tab'];

    if ($tab === 'day') {
        $day_id   = (int)$_POST['day_id'];
        $title    = $_POST['title'] ?? '';
        $subtitle = $_POST['subtitle'] ?? '';
        $content  = $_POST['content'] ?? '';
        $footer   = $_POST['footer'] ?? '';
        $day_bg_color = $_POST['day_bg_color'] ?? '#fff';
        $day_text_color = $_POST['day_text_color'] ?? '#000';
        $popup_image = $_POST['current_popup_image'] ?? '';

        
/// ================= загрузка новой картинки =================
           $image = $_POST['current_popup_image'] ?? ''; 

        if (isset($_FILES['popup_file']) && $_FILES['popup_file']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['popup_file']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp']; 
            if (in_array($ext, $allowed)) {
                $filename = 'popup_day'.$day_id.'.'.$ext;
                $uploadDir = __DIR__ . '/../img/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $targetPath = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['popup_file']['tmp_name'], $targetPath)) {
                    $image = '../img/' . $filename; 
                }
            }
        }

        
                if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
                
                $currentImage = $_POST['current_popup_image'] ?? '';
                if (!empty($currentImage)) {
                    
                    $fileToDelete = realpath(__DIR__ . '/../' . ltrim($currentImage, '/'));
                    if ($fileToDelete && file_exists($fileToDelete)) {
                        unlink($fileToDelete);
                    }
                }
                $image = ''; 
            }
        // --- Сохраняем день ---
        $check = $pdo->prepare("SELECT id FROM day_content WHERE day_id=?");
        $check->execute([$day_id]);

        if ($check->fetchColumn()) {
            $stmt = $pdo->prepare("
                UPDATE day_content
                SET popup_image=?, title=?, subtitle=?, content=?, footer=?
                WHERE day_id=?
            ");
            $stmt->execute([$image,$title,$subtitle,$content,$footer,$day_id]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO day_content (day_id,popup_image,title,subtitle,content,footer)
                VALUES (?,?,?,?,?,?)
            ");
            $stmt->execute([$day_id,$image,$title,$subtitle,$content,$footer]);
        }

        // --- Сохраняем стили дня ---
        $checkStyle = $pdo->prepare("SELECT id FROM day_styles WHERE day_id=?");
        $checkStyle->execute([$day_id]);

        if($checkStyle->fetchColumn()){
            $stmt = $pdo->prepare("UPDATE day_styles SET day_bg_color=?, day_text_color=? WHERE day_id=?");
            $stmt->execute([$day_bg_color,$day_text_color,$day_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO day_styles (day_id, day_bg_color, day_text_color) VALUES (?,?,?)");
            $stmt->execute([$day_id,$day_bg_color,$day_text_color]);
        }

        $response['saved'] = true;
    }

    if ($tab === 'styles') {
        $globals = [
            'calendar_background' => $_POST['calendar_background'] ?? '#3e030a',
            'popup_background'    => $_POST['popup_background'] ?? '#FFEFF4',
            'page_font'           => $_POST['page_font'] ?? '',
            'font_family'         => $_POST['font_family'] ?? '',
            'snow_count'          => $_POST['snow_count'] ?? 100,
            'snow_speed'          => $_POST['snow_speed'] ?? 1,
            'calendar_start_date'   => $_POST['calendar_start_date'] ?? '2026-02-01',
];

        foreach ($globals as $name => $value) {
            $check = $pdo->prepare("SELECT id FROM global_styles WHERE name=?");
            $check->execute([$name]);

            if ($check->fetchColumn()) {
                $stmt = $pdo->prepare("UPDATE global_styles SET value=? WHERE name=?");
                $stmt->execute([$value,$name]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO global_styles (name,value) VALUES (?,?)");
                $stmt->execute([$name,$value]);
            }
        }
        $response['saved'] = true;
    }

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest'){
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}


$day = $_GET['day'] ?? 1;
$stmt = $pdo->prepare("SELECT * FROM day_content WHERE day_id=?");
$stmt->execute([$day]);
$data = $stmt->fetch();

$day_styles = $pdo->query("SELECT * FROM day_styles ORDER BY day_id")->fetchAll(PDO::FETCH_UNIQUE);
$globals = $pdo->query("SELECT name,value FROM global_styles")->fetchAll(PDO::FETCH_KEY_PAIR);

$day_dates = [];
for($i=1;$i<=24;$i++){
    $day_dates[$i] = date('Y-m-d', strtotime("2026-02-$i"));

}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
<meta charset="UTF-8">
<title>Admin – Adventes Kalendārs</title>
<link rel="stylesheet" href="../css/style_for_admin.css">
</head>
<body>

<h1>🎄 Adventa kalendāra administrēšana</h1>


<div class="tabs">
    <div class="tab active" data-tab="day">Dienas saturs</div>
    <div class="tab" data-tab="styles">Globālie stili</div>
    <div class="tab" data-tab="preview">Priekšskatījums</div>
</div>

<div class="tab-content active" id="tab-day">
<form id="dayForm" enctype="multipart/form-data">
<input type="hidden" name="tab" value="day">
<label>Izvēlieties dienu</label>
<select name="day_id" id="daySelect">
<?php for($i=1;$i<=24;$i++): ?>
    if $toda
<option value="<?= $i ?>" <?= $i==$day?'selected':'' ?>><?= $i ?></option>

<?php endfor; ?>
</select>

<input type="hidden" name="current_popup_image" value="<?= htmlspecialchars($data['popup_image'] ?? '') ?>">

<div class="box">
<h2>Dienas saturs</h2>

<label>Augšupielādēt attēlu</label>
<input type="file" name="popup_file" accept="image/*">
<?php if(!empty($data['popup_image'])): ?>
<div style="margin-top:6px;">
<img src="<?= htmlspecialchars($data['popup_image']) ?>" style="max-width:150px; display:block; margin-bottom:4px;">
<button type="button" onclick="deleteImage()">Dzēst attēlu</button>
</div>
<?php endif; ?>

<label>Virsraksts</label>
<input type="text" name="title" value="<?= htmlspecialchars($data['title'] ?? '') ?>">

<label>Apakšvirsraksts</label>
<input type="text" name="subtitle" value="<?= htmlspecialchars($data['subtitle'] ?? '') ?>">

<label>Saturs</label>
<div id="editor" class="editor" contenteditable="true"><?= $data['content'] ?? '' ?></div>
<textarea name="content" id="content" hidden></textarea>

<label>Kājene</label>
<div id="editor-footer" class="editor" contenteditable="true"><?= $data['footer'] ?? '' ?></div>
<textarea name="footer" id="footer" hidden></textarea>

<label>Dienas fons</label>
<input type="color" name="day_bg_color" value="<?= htmlspecialchars($day_styles[$day]['day_bg_color'] ?? '#fff') ?>">

<label>Dienas teksts</label>
<input type="color" name="day_text_color" value="<?= htmlspecialchars($day_styles[$day]['day_text_color'] ?? '#000') ?>">

</div>
<button type="submit">Saglabāt</button>
</form>
</div>


<div class="tab-content" id="tab-styles">
<form id="stylesForm">
<input type="hidden" name="tab" value="styles">

<div class="box">
<h2>Globālie stili</h2>

<label>Kalendāra fons</label>
<input type="color" name="calendar_background" value="<?= $globals['calendar_background'] ?? '#3e030a' ?>">

<label>Popup fons</label>
<input type="color" name="popup_background" value="<?= $globals['popup_background'] ?? '#FFEFF4' ?>">


<label>Kalendāra sākuma datums</label>
<input type="date" name="calendar_start_date"
       value="<?= $globals['calendar_start_date'] ?? '2026-02-01' ?>">

<label>Lapas fonts</label>
<input type="text" name="font_family" value="<?= htmlspecialchars($globals['font_family'] ?? 'Canelia Light') ?>">

<label>❄ Sniega daudzums</label>
<input type="number" name="snow_count" value="<?= $globals['snow_count'] ?? 100 ?>">

<label>❄ Sniega ātrums</label>
<input type="number" step="0.1" name="snow_speed" value="<?= $globals['snow_speed'] ?? 1 ?>">
</div>

<button type="submit">Saglabāt</button>
</form>
</div>

<div class="tab-content" id="tab-preview">
<h2>Priekšskatījums</h2>
<div style="margin-bottom:12px;">
<button onclick="openPreview()">Atvērt kalendāru</button>
</div>
<div class="calendar-preview" id="calendar-preview"></div>
</div>

</body>
</html>

<script>
const dayDates = <?= json_encode($day_dates) ?>;
</script>
<script src="../js/script_for_admin.js"></script>
</body>
</html>
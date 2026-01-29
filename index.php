<?php
session_start();

// === Reset Game ===
if (isset($_POST['ulang'])) {
    session_unset();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$pemberitahuan = "";
$pemberitahuan2 = "";

// === HELPER FUNCTION ===
function buatSoalAritmatika($level) {
    if ($level === "mudah") {
        $ops = ['+', '-'];
        $a = rand(1, 10);
        $b = rand(1, 10);
    } elseif ($level === "sedang") {
        $ops = ['+', '-', '*'];
        $a = rand(5, 20);
        $b = rand(5, 20);
    } else {
        $ops = ['+', '-', '*', '/'];
        $a = rand(10, 50);
        $b = rand(1, 50);
    }

    $op = $ops[array_rand($ops)];

    if ($op === '/') {
        $a = $a * $b;
    }

    switch ($op) {
        case '+': $jawaban = $a + $b; break;
        case '-': $jawaban = $a - $b; break;
        case '*': $jawaban = $a * $b; break;
        case '/': $jawaban = $a / $b; break;
        default: $jawaban = 0;
    }

    $displayOp = $op;
    if ($op === '*') $displayOp = '×';
    if ($op === '/') $displayOp = '÷';

    $soal_display = "$a $displayOp $b";

    return [$soal_display, $jawaban];
}

if (!isset($_SESSION['game_over'])) {
    $_SESSION['game_over'] = false;
}

// === Pilih Tingkat/Batas ===
if (isset($_POST['mode']) && !isset($_POST['lanjut'])) {
    if (!isset($_SESSION['kesempatan'])) {
        $_SESSION['mode'] = $_POST['mode']; 
    } else {
        $_SESSION['mode'] = $_SESSION['mode']; 
    }
}

// === Mulai Game dengan Mode & Tingkat/Batas ===
if (isset($_POST['lanjut'])) {
    $_SESSION['kesempatan'] = 5;
    $_SESSION['game_over'] = false;
    $pemberitahuan2 = "";

    if (isset($_SESSION['mode']) && $_SESSION['mode'] === "angka") {
        $batas = (int)$_POST['range'];
        if ($batas < 1) $batas = 10;
        $_SESSION['angka_rahasia'] = rand(1, $batas);
        $_SESSION['batas'] = $batas;
        $pemberitahuan = "Game dimulai! Tebak angka antara 1 - $batas";

    } else {
        $level = isset($_POST['level']) ? $_POST['level'] : 'mudah'; 
        $_SESSION['level'] = $level;
        list($soal, $jawaban) = buatSoalAritmatika($level);

        $_SESSION['soal'] = $soal; 
        $_SESSION['jawaban'] = $jawaban;
        $pemberitahuan = "Game dimulai! Jawab soal berikut: <br><b>$soal = ?</b>";
    }
}

// === Proses Jawaban & Update Status Game ===
if (isset($_POST['tebakan']) && isset($_SESSION['kesempatan']) && $_SESSION['game_over'] === false) {
    $raw = trim($_POST['tebakan']);
    if ($raw === '') {
        $tebakan = null;
    } elseif (strpos($raw, '.') !== false) {
        $tebakan = (float)$raw;
    } else {
        $tebakan = (int)$raw;
    }

    if (isset($_SESSION['mode']) && $_SESSION['mode'] === 'angka') {
        $angka_rahasia = $_SESSION['angka_rahasia']; // simpan di var lokal
        if ($tebakan === $angka_rahasia) {
            $pemberitahuan = "🎉 Selamat! Tebakanmu benar: $angka_rahasia 🎉";
            $_SESSION['game_over'] = true;
        } else {
            $_SESSION['kesempatan']--;
            if ($_SESSION['kesempatan'] <= 0) {
                $pemberitahuan = "💀 Kesempatan habis! Angkanya adalah $angka_rahasia.";
                $_SESSION['game_over'] = true;
            } else {
                $pemberitahuan = "Tebak angka antara 1 - " . htmlspecialchars($_SESSION['batas']);
                $pemberitahuan2 = ($tebakan > $angka_rahasia) ? "❌ Terlalu besar!" : "❌ Terlalu kecil!";
            }
        }
    } elseif (isset($_SESSION['mode']) && $_SESSION['mode'] === 'aritmatika') {
        $jawaban = $_SESSION['jawaban'];

        if ($tebakan === $jawaban || (is_numeric($tebakan) && abs($tebakan - $jawaban) < 0.000001)) {
            $pemberitahuan = "🎉 Betul! " . htmlspecialchars($_SESSION['soal']) . " = $jawaban 🎉";
            $_SESSION['game_over'] = true;
        } else {
            $_SESSION['kesempatan']--;
            if ($_SESSION['kesempatan'] <= 0) {
                $pemberitahuan = "💀 Kesempatan habis! Jawaban benar: " . htmlspecialchars($_SESSION['soal']) . " = $jawaban.";
                $_SESSION['game_over'] = true;
            } else {
                $pemberitahuan = "Jawab soal berikut: <br><b>" . htmlspecialchars($_SESSION['soal']) . " = ?</b>";
                $pemberitahuan2 = "❌ Jawaban salah, coba lagi!";
            }
        }
    } else {
        $pemberitahuan = "Silakan pilih mode dulu.";
        $_SESSION['game_over'] = true;
    }
}

// === Set Tampilan Default Jika Belum Ada Input ===
if (isset($_SESSION['kesempatan']) && $pemberitahuan === "") {

    if (isset($_SESSION['mode']) && $_SESSION['mode'] === 'aritmatika' && isset($_SESSION['soal'])) {
        $pemberitahuan = "Jawab soal berikut: <br><b>" . htmlspecialchars($_SESSION['soal']) . " = ?</b>";
    } elseif (isset($_SESSION['mode']) && $_SESSION['mode'] === 'angka' && isset($_SESSION['batas'])) {
        $pemberitahuan = "Tebak angka antara 1 - " . htmlspecialchars($_SESSION['batas']);
    }
}
?>

</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Brain Teasser Game</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<iframe src="musik.php" name="musikFrame" style="display:none;" allow="autoplay"></iframe>

<div class="container">
    <h2>🎮 Brain Teasser Game</h2>

    <!-- Pilih Mode Game -->
    <?php if (!isset($_SESSION['kesempatan']) && !isset($_SESSION['mode'])): ?>
        <form method="post">
            <label>Pilih Mode Game:</label><br>
            <select name="mode" required>
                <option value="angka">Tebak Angka</option>
                <option value="aritmatika">Soal Aritmatika</option>
            </select><br>
            <button type="submit">Lanjut</button>
        </form>
    <?php endif; ?>

    <!-- Pilih Tingkat Kesulitan / Batas Angka -->
    <?php if (!isset($_SESSION['kesempatan']) && isset($_SESSION['mode'])): ?>
        <form method="post">
            <?php if ($_SESSION['mode'] === "angka"): ?>
                <label>Pilih Batas Angka:</label><br>
                <select name="range">
                    <option value="10">1 - 10</option>
                    <option value="20">1 - 20</option>
                    <option value="50">1 - 50</option>
                    <option value="100">1 - 100</option>
                </select><br>
            <?php else: ?>
                <label>Pilih Tingkat Kesulitan:</label><br>
                <select name="level">
                    <option value="mudah">Mudah</option>
                    <option value="sedang">Sedang</option>
                    <option value="sulit">Sulit</option>
                </select><br>
            <?php endif; ?>
            <button type="submit" name="lanjut">Mulai Game</button>
        </form>
    <?php endif; ?>

    <!-- Bermain Game / Tampil Hasil -->
    <?php if (isset($_SESSION['kesempatan'])): ?>
        <div class="pemberitahuan"><?= $pemberitahuan ?></div>
        <?php if ($pemberitahuan2 !== ""): ?>
            <div class="pemberitahuan2"><?= $pemberitahuan2 ?></div>
        <?php endif; ?>

        <?php if ($_SESSION['game_over'] === false): ?>
            <form method="post">
                <input type="number" name="tebakan" placeholder="Masukkan jawaban" required><br>

                <div class="kesempatan" aria-hidden="true">
                    <?php
                    $total_kesempatan = 5;
                    $sisa = isset($_SESSION['kesempatan']) ? (int)$_SESSION['kesempatan'] : 0;
                    for ($i = 1; $i <= $total_kesempatan; $i++):
                        $hilang = $i > $sisa ? "hilang" : "";
                    ?>
                        <div class="nyawa <?= $hilang ?>"></div>
                    <?php endfor; ?>
                </div>

                <button type="submit">Jawab</button>
            </form>
        <?php else: ?>
            <form method="post" class="inline">
                <button type="submit" name="ulang">🔄 Main Lagi</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
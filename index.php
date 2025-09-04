<?php
session_start();

// Main lagi
if (isset($_POST['ulang'])) {
    session_unset();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$pemberitahuan = "";
$pemberitahuan2 = "";

// soal aritmatika sesuai tingkat kesulitan
function buatSoalAritmatika($level) {
    if ($level === "mudah") {
        $ops = ['+', '-'];
        $a = rand(1, 10);
        $b = rand(1, 10);
    } elseif ($level === "sedang") {
        $ops = ['+', '-', '*'];
        $a = rand(5, 20);
        $b = rand(5, 20);
    } else { // sulit
        $ops = ['+', '-', '*', '/'];
        $a = rand(10, 50);
        $b = rand(1, 50);
    }

    $op = $ops[array_rand($ops)]; // ambil satu nilai acak dari array var ops

    // kalau sistem memilih pembagian hasilkan bil bulat, dgn cara melipatkan bil a/b
    if ($op === '/') {
        $a = $a * $b; //contoh 7 * 3 = 21
    }

    // hitung jawaban
    switch ($op) {
        case '+': $jawaban = $a + $b; break;
        case '-': $jawaban = $a - $b; break;
        case '*': $jawaban = $a * $b; break;
        case '/': $jawaban = $a / $b; break;
        default: $jawaban = 0;
    }

    // ganti operator untuk tampilan web
    $displayOp = $op;
    if ($op === '*') $displayOp = '×';
    if ($op === '/') $displayOp = '÷';

    $soal_display = "$a $displayOp $b";

    return [$soal_display, $jawaban]; //mengembalikan/menampilkan nilai intinya gini lah
}

// apakah belum ada var game_over || untuk menghindari undefined index
if (!isset($_SESSION['game_over'])) {
    $_SESSION['game_over'] = false; //artinya game masih berjalan
}

// STEP 1: Pilih mode game
if (isset($_POST['mode']) && !isset($_POST['lanjut'])) {
    // var kesempatan blm ada || belum memulai game
    if (!isset($_SESSION['kesempatan'])) {
        $_SESSION['mode'] = $_POST['mode']; // menyimpan mode || user bisa ganti mode
    } else {
        $_SESSION['mode'] = $_SESSION['mode']; // game sudah berjalan || tidak bisa ganti mode
    }
}

// STEP 2: Pilih tingkat kesulitan & mulai game
if (isset($_POST['lanjut'])) {
    // mulai permainan baru
    $_SESSION['kesempatan'] = 5;
    $_SESSION['game_over'] = false;
    $pemberitahuan2 = "";

    // user memilih mode angka
    if (isset($_SESSION['mode']) && $_SESSION['mode'] === "angka") {
        $batas = (int)$_POST['range']; // batas angka
        if ($batas < 1) $batas = 10; // user tidak memilih batas, default 10
        $_SESSION['angka_rahasia'] = rand(1, $batas); // angka rahasia
        $_SESSION['batas'] = $batas; // Simpan di session
        $pemberitahuan = "Game dimulai! Tebak angka antara 1 - $batas"; // pemberitahuan

    } else { // user memilih mode aritmatika
        $level = isset($_POST['level']) ? $_POST['level'] : 'mudah'; // user tidak memilih kesulitan, default mudah
        $_SESSION['level'] = $level; // Simpan di session
        list($soal, $jawaban) = buatSoalAritmatika($level); // membuat soal/jawaban
        // Simpan di session
        $_SESSION['soal'] = $soal; 
        $_SESSION['jawaban'] = $jawaban;
        $pemberitahuan = "Game dimulai! Jawab soal berikut: <br><b>$soal = ?</b>"; // pemberitahuan
    }
}

// STEP 3: Menangani jawaban
// user mengirim jawaban, permainan sedang berjalan, game blm selesai
if (isset($_POST['tebakan']) && isset($_SESSION['kesempatan']) && $_SESSION['game_over'] === false) {
    // ambil tebakan user dgn trim supaya tidak error
    $raw = trim($_POST['tebakan']);
    // tidak menerima desimal
    if ($raw === '') {
        $tebakan = null;
    } elseif (strpos($raw, '.') !== false) {
        $tebakan = (float)$raw;
    } else {
        $tebakan = (int)$raw;
    }

    // MODE TEBAK ANGKA
    // mode diplih?, mode tersebut tebak angka?
    if (isset($_SESSION['mode']) && $_SESSION['mode'] === 'angka') {
        $angka_rahasia = $_SESSION['angka_rahasia']; // simpan di var lokal
        if ($tebakan === $angka_rahasia) {
            $pemberitahuan = "🎉 Selamat! Tebakanmu benar: $angka_rahasia 🎉";
            $_SESSION['game_over'] = true;
            
        } else {
            $_SESSION['kesempatan']--; //kurangi kesempatan
            if ($_SESSION['kesempatan'] <= 0) {
                $pemberitahuan = "💀 Kesempatan habis! Angkanya adalah $angka_rahasia.";
                $_SESSION['game_over'] = true;
                
            } else {
                // pemberitahuan
                $pemberitahuan = "Tebak angka antara 1 - " . htmlspecialchars($_SESSION['batas']);
                $pemberitahuan2 = ($tebakan > $angka_rahasia) ? "❌ Terlalu besar!" : "❌ Terlalu kecil!";
            }
        }
        
    // MODE ARITMATIKA
    // mode diplih?, mode tersebut arimatika?
    } elseif (isset($_SESSION['mode']) && $_SESSION['mode'] === 'aritmatika') {
        $jawaban = $_SESSION['jawaban']; // simpan di var lokal

        // apa sama tebakan&jawaban / input angka & selisih sangat kecil, true
        if ($tebakan === $jawaban || (is_numeric($tebakan) && abs($tebakan - $jawaban) < 0.000001)) {
            $pemberitahuan = "🎉 Betul! " . htmlspecialchars($_SESSION['soal']) . " = $jawaban 🎉";
            $_SESSION['game_over'] = true;
        } else {
            $_SESSION['kesempatan']--; //kurangi kesempatan
            if ($_SESSION['kesempatan'] <= 0) {
                $pemberitahuan = "💀 Kesempatan habis! Jawaban benar: " . htmlspecialchars($_SESSION['soal']) . " = $jawaban.";
                $_SESSION['game_over'] = true;
            } else {
                // pemberitahuan
                $pemberitahuan = "Jawab soal berikut: <br><b>" . htmlspecialchars($_SESSION['soal']) . " = ?</b>";
                $pemberitahuan2 = "❌ Jawaban salah, coba lagi!";
            }
        }
    } else {
        $pemberitahuan = "Silakan pilih mode dulu.";
        $_SESSION['game_over'] = true;
    }
}

// True Permainan sedang berjalan?
if (isset($_SESSION['kesempatan']) && $pemberitahuan === "") {
    // mode arimatika & soal sudah ada
    if (isset($_SESSION['mode']) && $_SESSION['mode'] === 'aritmatika' && isset($_SESSION['soal'])) {
        $pemberitahuan = "Jawab soal berikut: <br><b>" . htmlspecialchars($_SESSION['soal']) . " = ?</b>";
        
        // mode tebak angka & soal sudah ada
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

    <!--- MEMBUAT TAMPILAN --->
    <!-- Step 1: Pilih Mode -->
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

    <!-- STEP 2: Pilih tingkat kesulitan & mulai game -->
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

    <!-- Step 3: Game sedang berjalan || selesai -->
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
            
        <?php else: // game over: tampil tombol Main Lagi ?>
            <form method="post" class="inline">
                <button type="submit" name="ulang">🔄 Main Lagi</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
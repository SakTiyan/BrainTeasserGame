# Brain Teasser Game

Brain Teasser Game adalah permainan berbasis web sederhana yang dibuat menggunakan **PHP native** dan **CSS native**.  
Game ini dirancang untuk melatih logika dan konsentrasi pemain melalui dua mode permainan yang berbeda.

## Fitur Utama
- Dua mode permainan:
  - **Tebak Angka**
  - **Soal Aritmatika**
- Sistem kesempatan (nyawa) terbatas
- Tingkat kesulitan bertahap
- Tampilan sederhana dan responsif
- Menggunakan session PHP untuk menyimpan state permainan
- Background musik otomatis saat game berjalan

## Mode Permainan

### 1. Tebak Angka
Pemain diminta menebak angka acak yang dihasilkan sistem dalam rentang tertentu.  
Setiap jawaban yang salah akan mengurangi kesempatan hingga permainan berakhir.

### 2. Soal Aritmatika
Pemain harus menjawab soal matematika acak berdasarkan tingkat kesulitan:
- Mudah
- Sedang
- Sulit  

Soal meliputi operasi penjumlahan, pengurangan, perkalian, dan pembagian.

## Teknologi yang Digunakan
- **PHP Native** (tanpa framework)
- **CSS Native**
- **HTML5**
- Session PHP

## Cara Instalasi

### 1. Persyaratan
- PHP versi **7.4** atau lebih baru  
- Web server (**Laragon**, **XAMPP**, atau sejenisnya)  
- Browser modern (Chrome, Firefox, Edge)

### 2. Instalasi Lokal
1. Clone repository atau unduh source code:
   ```bash
   git clone https://github.com/username/brain-teasser-game.git

2. Masuk folder sesuai direktori web server masing-masing:
   - **Laragon**
   ```bash
   C:\laragon\www\
   - **Xampp**
   ```bash
   C:\xampp\htdocs\

### 3. Pastikan semua file berada dalam satu folder project.

## Struktur File
```text
/
├── backsound.mp3  # File backsound
├── index.php      # Logika utama game & tampilan
├── style.css      # Styling tampilan
├── musik.php      # Pemutar backsound

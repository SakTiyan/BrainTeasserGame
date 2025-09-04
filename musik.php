<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<audio id="backsound" autoplay loop>
  <source src="backsound.mp3" type="audio/mpeg">
</audio>

<script>
const audio = document.getElementById("backsound");

// tangkap posisi saat ganti step
window.addEventListener("beforeunload", () => {
   localStorage.setItem("musicTime", audio.currentTime); // simpan posisi terakhir
});

// jalankan saat sudah ganti step
window.addEventListener("load", () => {
   const lastTime = localStorage.getItem("musicTime"); // ambil posisi terakhir
   if (lastTime) {
      audio.currentTime = parseFloat(lastTime); // lanjutkan dari posisi terakhir
   }
   audio.play(); // mainkan cuy
});
</script>
</body>
</html>

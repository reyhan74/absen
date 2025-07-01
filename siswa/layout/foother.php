<footer class="footer footer-transparent d-print-none">
</footer>

<!-- Libs JS -->
<script src="../../assets/libs/apexcharts/dist/apexcharts.min.js?1692870487" defer></script>
<script src="../../assets/libs/jsvectormap/dist/js/jsvectormap.min.js?1692870487" defer></script>
<script src="../../assets/libs/jsvectormap/dist/maps/world.js?1692870487" defer></script>
<script src="../../assets/libs/jsvectormap/dist/maps/world-merc.js?1692870487" defer></script>
<!-- Tabler Core -->
<script src="../../assets/js/tabler.min.js?1692870487" defer></script>
<script src="../../assets/js/demo.min.js?1692870487" defer></script>
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Alert Validasi -->
<?php if (isset($_SESSION['validasi']) && $_SESSION['validasi']) : ?>
<script>
    const Toast = Swal.mixin({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
      }
    });

    Toast.fire({
      icon: "error",
      title: "<?= $_SESSION['validasi']; ?>"
    });
</script>
<?php unset($_SESSION['validasi']); endif; ?>

<?php if (isset($_SESSION['sukses'])): ?>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '<?= htmlspecialchars($_SESSION['sukses'], ENT_QUOTES); ?>'
  });
</script>
<?php unset($_SESSION['sukses']); endif; ?>

<?php if (isset($_SESSION['gagal'])): ?>
<script>
  Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: '<?= htmlspecialchars($_SESSION['gagal'], ENT_QUOTES); ?>'
  });
</script>
<?php unset($_SESSION['gagal']); endif; ?>
</body>
</html>

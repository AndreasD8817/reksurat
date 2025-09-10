<?php
// templates/footer.php
?>
            </main> 
        </div> 
    </div>

    <script src="/assets/js/app.js"></script>
    
<?php
// Bagian Notifikasi SweetAlert dari Session (Tidak berubah)
if (isset($_SESSION['error_message'])) {
    $errorMessage = json_encode($_SESSION['error_message']);
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal Menyimpan!',
            text: {$errorMessage},
            showConfirmButton: true
        });
    </script>";
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    $successMessage = json_encode($_SESSION['success_message']);
    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: {$successMessage},
            timer: 2000,
            showConfirmButton: false
        });
    </script>";
    unset($_SESSION['success_message']);
}
?>
</body>
</html>
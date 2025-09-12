<?php
// templates/footer.php
?>
            </main> 
        </div> <!-- Penutup div flex-1 -->
    </div> <!-- Penutup div flex utama -->

    <!-- Modal untuk Detail Surat -->
    <div id="detail-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden transition-opacity duration-300 opacity-0">
        <div id="detail-modal-content" class="bg-white rounded-2xl shadow-xl w-full max-w-2xl transform transition-transform duration-300 scale-95">
            
            <div class="flex justify-between items-center p-5 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                    {/* Judul modal akan diisi oleh JavaScript */}
                </h3>
                <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <div class="p-6 space-y-4" id="modal-body-content">
                <div class="text-center p-8">
                    <i class="fas fa-spinner fa-spin text-primary text-3xl"></i>
                    <p class="mt-2 text-gray-500">Memuat data...</p>
                </div>
            </div>
            <div class="p-4 bg-gray-50 rounded-b-2xl border-t flex justify-end" id="modal-footer-content">
                {/* Tombol footer modal akan diisi oleh JavaScript */}
            </div>
        </div> 
    </div>
    
    <!-- PENTING: Ubah cara pemanggilan script menjadi module -->
    <script src="/assets/js/app.js" type="module"></script>
    
<?php
// Logika untuk menampilkan notifikasi SweetAlert dari session PHP
if (isset($_SESSION['error_message'])) {
    $errorMessage = json_encode($_SESSION['error_message']);
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
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

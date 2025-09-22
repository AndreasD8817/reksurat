<?php
// templates/footer.php
?>
            </main> 
        </div> <!-- Penutup div flex-1 -->
    </div> <!-- Penutup div flex utama -->

    <!-- Modal untuk Detail Surat -->
    <div id="detail-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-start sm:items-center justify-center p-4 z-50 hidden transition-opacity duration-300 opacity-0">
        <div id="detail-modal-content" class="bg-white rounded-2xl shadow-xl w-full max-w-2xl transform transition-transform duration-300 scale-95 max-h-[85vh] flex flex-col">
            
            <div class="flex justify-between items-center p-5 border-b border-gray-200 flex-shrink-0">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                    {/* Judul modal akan diisi oleh JavaScript */}
                </h3>
                <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto" id="modal-body-content">
                <div class="text-center p-8">
                    <i class="fas fa-spinner fa-spin text-primary text-3xl"></i>
                    <p class="mt-2 text-gray-500">Memuat data...</p>
                </div>
            </div>
            <div class="p-4 bg-gray-50 rounded-b-2xl border-t flex justify-end flex-shrink-0" id="modal-footer-content">
                {/* Tombol footer modal akan diisi oleh JavaScript */}
            </div>
        </div> 
    </div>

    <!-- Modal untuk PDF Viewer -->
    <div id="pdf-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center p-4 z-50 hidden transition-opacity duration-300 opacity-0">
        <div id="pdf-modal-content" class="bg-white rounded-2xl shadow-xl w-full max-w-4xl h-[90vh] flex flex-col transform transition-transform duration-300 scale-95">
            <div class="flex justify-between items-center p-4 border-b border-gray-200 bg-gray-50 rounded-t-2xl">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-file-pdf text-red-500 mr-3"></i>
                    <span id="pdf-modal-title">Tampilan Lampiran</span>
                </h3>
                <button id="close-pdf-modal-btn" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            <div class="flex-grow p-2 bg-gray-200">
                <embed id="pdf-embed" src="" type="application/pdf" width="100%" height="100%">
            </div>
            <div class="p-3 bg-gray-50 rounded-b-2xl border-t flex justify-end">
                <a id="pdf-download-link" href="#" download class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary text-sm"><i class="fas fa-download mr-2"></i>Unduh PDF</a>
            </div>
        </div>
    </div>
    
    <script src="/assets/js/app.js" type="module"></script>

    <!-- SCRIPT KHUSUS UNTUK FUNGSI CEK NOMOR DI HALAMAN EDIT -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        function setupCheckButton(buttonId, urutInputId, tahunSelectId, checkUrl, errorText) {
            const checkBtn = document.getElementById(buttonId);
            if (!checkBtn) return;

            checkBtn.addEventListener('click', function() {
                const nomorUrutInput = document.getElementById(urutInputId);
                const tahunSelect = document.getElementById(tahunSelectId);
                const urlParams = new URLSearchParams(window.location.search);
                const idSurat = urlParams.get('id');

                const nomorUrut = nomorUrutInput.value.trim();
                const tahun = tahunSelect.value;

                if (!nomorUrut) {
                    Swal.fire({ icon: 'error', title: 'Input Kosong', text: errorText });
                    return;
                }

                const formData = new FormData();
                formData.append('nomor_urut', nomorUrut);
                formData.append('tahun', tahun);
                formData.append('id', idSurat);

                fetch(checkUrl, {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Nomor Urut Sudah Digunakan!',
                            html: `Nomor Urut <strong>${data.nomor}</strong> untuk tahun <strong>${data.tahun}</strong> sudah terdaftar pada surat lain.`
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Nomor Urut Tersedia!',
                            html: `Nomor Urut <strong>${data.nomor}</strong> untuk tahun <strong>${data.tahun}</strong> dapat digunakan.`
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: 'Gagal melakukan pengecekan.' });
                });
            });
        }

        // Setup untuk Edit Surat Keluar
        setupCheckButton('checkNomorKeluarBtnEdit', 'nomor_urut_edit', 'tahun_penomoran_edit', '/ajax-check-nomor-keluar-edit', 'Silakan isi Nomor Urut yang ingin dicek.');
        
        // Setup untuk Edit Surat Keluar Dewan
        setupCheckButton('checkNomorKeluarDewanBtnEdit', 'nomor_urut_edit_dewan', 'tahun_penomoran_edit_dewan', '/ajax-check-nomor-keluar-dewan-edit', 'Silakan isi Nomor Urut yang ingin dicek.');
        
        // Setup untuk Edit Surat Masuk
        setupCheckButton('checkAgendaBtnEdit', 'agenda_urut_edit', 'tahun_penomoran_edit_masuk', '/ajax-check-nomor-edit', 'Silakan isi No. Urut Agenda yang ingin dicek.');

        // Setup untuk Edit Surat Masuk Dewan
        setupCheckButton('checkAgendaDewanBtnEdit', 'agenda_urut_edit_dewan', 'tahun_penomoran_edit_dewan', '/ajax-check-nomor-agenda-dewan-edit', 'Silakan isi No. Urut Agenda yang ingin dicek.');
    });
    </script>
    
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

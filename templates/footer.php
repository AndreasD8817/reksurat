<?php
// templates/footer.php
?>
            </main> </div> </div>

    <script>
      // Bagian 1: Logika untuk Toggle Sidebar Mobile
      const menuToggle = document.getElementById("menu-toggle");
      const sidebar = document.getElementById("sidebar");
      const overlay = document.getElementById("overlay");

      if(menuToggle) {
        menuToggle.addEventListener("click", function () {
          sidebar.classList.toggle("open");
          overlay.classList.toggle("open");
        });
      }

      if(overlay) {
        overlay.addEventListener("click", function () {
          sidebar.classList.remove("open");
          this.classList.remove("open");
        });
      }

      // -------------------------------------------------------------------

      function escapeHTML(str) {
          return str ? str.toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;') : '';
      }

      // --- FUNGSI BARU UNTUK KONFIRMASI HAPUS ---
function confirmDelete(type, id) {
    Swal.fire({
        title: 'Anda Yakin?',
        text: "Data surat ini akan dihapus secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Arahkan ke halaman hapus (Anda perlu membuat file ini nanti)
            window.location.href = `/hapus-surat-${type}?id=${id}`;
        }
    });
        }

      // Bagian 2: Logika untuk Pencarian AJAX di Halaman Surat Keluar
      if (document.getElementById('searchFormKeluar')) {
          
          const searchInput = document.getElementById('searchInputKeluar');
          const tableBody = document.getElementById('tableBodyKeluar');
          const paginationContainer = document.getElementById('paginationContainerKeluar');
          let debounceTimer;

          function fetchData(page = 1) {
              const query = searchInput.value;
              const url = `/pages/ajax-search-surat-keluar.php?search=${encodeURIComponent(query)}&p=${page}`;

              tableBody.innerHTML = '<tr><td colspan="5" class="text-center p-8"><i class="fas fa-spinner fa-spin text-primary text-3xl"></i></td></tr>';
              paginationContainer.innerHTML = '';

              fetch(url)
                  .then(response => response.json())
                  .then(data => {
                      updateTable(data.surat_list);
                      updatePagination(data.pagination);
                  })
                  .catch(error => {
                      console.error('Error fetching data:', error);
                      tableBody.innerHTML = '<tr><td colspan="5" class="text-center p-8 text-red-500">Gagal memuat data.</td></tr>';
                  });
          }

          function updateTable(suratList) {
                tableBody.innerHTML = '';
                if (suratList.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center"><p class="text-lg">Data tidak ditemukan.</p></td></tr>';
                    return;
                }
                let rowsHTML = '';
                const isAdmin = "<?php echo $_SESSION['user_role'] ?? 'staff'; ?>" === 'admin';

                suratList.forEach(surat => {
                    let lampiranHtml = surat.file_lampiran 
                        ? `<a href="/uploads/${surat.file_lampiran}" target="_blank" class="text-primary hover:underline"><i class="fas fa-file-alt"></i> Lihat</a>`
                        : '<span class="text-gray-400">-</span>';
                    
                    let actionButtons = '';
                    if (isAdmin) {
                        actionButtons = `
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <a href="/edit-surat-keluar?id=${surat.id}" class="text-blue-500 hover:text-blue-700" title="Edit"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDelete('keluar', ${surat.id})" class="text-red-500 hover:text-red-700" title="Hapus"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>`;
                    }

                    rowsHTML += `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-primary">${escapeHTML(surat.nomor_surat_lengkap)}</td>
                            <td class="px-6 py-4">${escapeHTML(surat.tgl_formatted)}</td>
                            <td class="px-6 py-4">${escapeHTML(surat.perihal)}</td>
                            <td class="px-6 py-4">${escapeHTML(surat.tujuan)}</td>
                            <td class="px-6 py-4">${lampiranHtml}</td>
                            ${actionButtons}
                        </tr>`;
                });
                tableBody.innerHTML = rowsHTML;
            }

          function updatePagination(pagination) {
              paginationContainer.innerHTML = '';
              if (pagination.total_pages <= 1) return;

              let paginationHTML = '<div class="flex items-center justify-between">';
              paginationHTML += `<div>Halaman ${pagination.current_page} dari ${pagination.total_pages}</div>`;
              let buttonsHTML = '<div class="flex space-x-1">';
              if (pagination.current_page > 1) {
                  buttonsHTML += `<button onclick="fetchData(${pagination.current_page - 1})" class="px-4 py-2 rounded-lg border">Sebelumnya</button>`;
              }
              if (pagination.current_page < pagination.total_pages) {
                  buttonsHTML += `<button onclick="fetchData(${pagination.current_page + 1})" class="px-4 py-2 rounded-lg border">Selanjutnya</button>`;
              }
              buttonsHTML += '</div>';
              paginationHTML += buttonsHTML + '</div>';
              paginationContainer.innerHTML = paginationHTML;
          }
          
          searchInput.addEventListener('input', () => {
              clearTimeout(debounceTimer);
              debounceTimer = setTimeout(() => fetchData(1), 300);
          });
      }

      // Bagian 3: Logika untuk Cek Nomor Urut Agenda (Surat Masuk)
      if (document.getElementById('checkAgendaBtn')) {
          const checkAgendaBtn = document.getElementById('checkAgendaBtn');
          const checkNomorUrutAgenda = () => {
              const urut = document.getElementById('agenda_urut').value.trim();
              if (!urut) {
                  Swal.fire({ icon: 'error', title: 'Input Kosong', text: 'Isi "No. Urut" Agenda yang ingin dicek.' });
                  return;
              }
              fetch('/pages/ajax-check-nomor.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: `nomor_urut=${encodeURIComponent(urut)}`
              })
              .then(response => response.json())
              .then(data => {
                  if (data.exists) {
                      Swal.fire({ icon: 'warning', title: 'Nomor Urut Sudah Ada!', html: `No. Urut Agenda <strong>${data.nomor}</strong> sudah terdaftar.` });
                  } else {
                      Swal.fire({ icon: 'success', title: 'Nomor Urut Tersedia!', html: `No. Urut Agenda <strong>${data.nomor}</strong> dapat digunakan.` });
                  }
              });
          };
          checkAgendaBtn.addEventListener('click', checkNomorUrutAgenda);
      }

      // Bagian 4: Logika untuk Cek Nomor Urut Surat Keluar
      if (document.getElementById('checkNomorKeluarBtn')) {
          const checkNomorBtn = document.getElementById('checkNomorKeluarBtn');
          const checkNomorUrutKeluar = () => {
              const urut = document.getElementById('nomor_urut_keluar').value.trim();
              if (!urut) {
                  Swal.fire({ icon: 'error', title: 'Input Kosong', text: 'Isi "No. Urut" yang ingin dicek.' });
                  return;
              }
              fetch('/pages/ajax-check-nomor-keluar.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: `nomor_urut=${encodeURIComponent(urut)}`
              })
              .then(response => response.json())
              .then(data => {
                  if (data.exists) {
                      Swal.fire({ icon: 'warning', title: 'Nomor Urut Sudah Ada!', html: `No. Urut <strong>${data.nomor}</strong> sudah terdaftar.` });
                  } else {
                      Swal.fire({ icon: 'success', title: 'Nomor Urut Tersedia!', html: `No. Urut <strong>${data.nomor}</strong> dapat digunakan.` });
                  }
              });
          };
          checkNomorBtn.addEventListener('click', checkNomorUrutKeluar);
      }
      
      // Bagian 5: Logika untuk Pencarian AJAX di Halaman Surat Masuk
      if (document.getElementById('searchFormMasuk')) {
        
        const searchInputMasuk = document.getElementById('searchInputMasuk');
        const tableBodyMasuk = document.getElementById('tableBodyMasuk');
        const paginationContainerMasuk = document.getElementById('paginationContainerMasuk');
        let debounceTimerMasuk;

        function fetchDataMasuk(page = 1) {
            const query = searchInputMasuk.value;
            const url = `/pages/ajax-search-surat-masuk.php?search=${encodeURIComponent(query)}&p=${page}`;

            tableBodyMasuk.innerHTML = '<tr><td colspan="5" class="text-center p-8"><i class="fas fa-spinner fa-spin text-primary text-3xl"></i></td></tr>';
            paginationContainerMasuk.innerHTML = '';

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    updateTableMasuk(data.surat_list);
                    updatePaginationMasuk(data.pagination);
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    tableBodyMasuk.innerHTML = '<tr><td colspan="5" class="text-center p-8 text-red-500">Gagal memuat data.</td></tr>';
                });
        }

        function updateTableMasuk(suratList) {
            tableBodyMasuk.innerHTML = '';
            if (suratList.length === 0) {
                tableBodyMasuk.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500"><p>Data tidak ditemukan.</p></td></tr>';
                return;
            }
            let rowsHTML = '';
            const isAdmin = "<?php echo $_SESSION['user_role'] ?? 'staff'; ?>" === 'admin';

            suratList.forEach(surat => {
                let lampiranHtml = surat.file_lampiran 
                    ? `<a href="/uploads/${surat.file_lampiran}" target="_blank" class="text-primary hover:underline"><i class="fas fa-file-alt"></i> Lihat</a>`
                    : '<span class="text-gray-400">-</span>';

                let actionButtons = '';
                if (isAdmin) {
                    actionButtons = `
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="/edit-surat-masuk?id=${surat.id}" class="text-blue-500 hover:text-blue-700" title="Edit"><i class="fas fa-edit"></i></a>
                                <button onclick="confirmDelete('masuk', ${surat.id})" class="text-red-500 hover:text-red-700" title="Hapus"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>`;
                }

                rowsHTML += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-semibold text-gray-800">${escapeHTML(surat.nomor_agenda_lengkap)}</td>
                        <td class="px-6 py-4">${escapeHTML(surat.asal_surat)}</td>
                        <td class="px-6 py-4">${escapeHTML(surat.perihal)}</td>
                        <td class="px-6 py-4">${escapeHTML(surat.tgl_terima_formatted)}</td>
                        <td class="px-6 py-4">${lampiranHtml}</td>
                        ${actionButtons}
                    </tr>`;
            });
            tableBodyMasuk.innerHTML = rowsHTML;
        }

        function updatePaginationMasuk(pagination) {
            paginationContainerMasuk.innerHTML = '';
            if (pagination.total_pages <= 1) return;

            let paginationHTML = '<div class="flex items-center justify-between">';
            paginationHTML += `<div>Halaman ${pagination.current_page} dari ${pagination.total_pages}</div>`;
            
            let buttonsHTML = '<div class="flex space-x-1">';
            if (pagination.current_page > 1) {
                buttonsHTML += `<button onclick="fetchDataMasuk(${pagination.current_page - 1})" class="px-4 py-2 rounded-lg border text-sm">Sebelumnya</button>`;
            }
            if (pagination.current_page < pagination.total_pages) {
                buttonsHTML += `<button onclick="fetchDataMasuk(${pagination.current_page + 1})" class="px-4 py-2 rounded-lg border text-sm">Selanjutnya</button>`;
            }
            buttonsHTML += '</div>';

            paginationHTML += buttonsHTML + '</div>';
            paginationContainerMasuk.innerHTML = paginationHTML;
        }
        
        searchInputMasuk.addEventListener('input', () => {
            clearTimeout(debounceTimerMasuk);
            debounceTimerMasuk = setTimeout(() => fetchDataMasuk(1), 300);
        });
    }

    // --- TAMBAHAN BARU: Menampilkan nama file yang dipilih ---
    document.addEventListener('DOMContentLoaded', () => {
        const fileInputMasuk = document.getElementById('file-upload-masuk');
        const fileNameDisplayMasuk = document.getElementById('file-name-masuk');
        if (fileInputMasuk) {
            fileInputMasuk.addEventListener('change', (e) => {
                const fileName = e.target.files[0] ? e.target.files[0].name : 'Belum ada file dipilih';
                fileNameDisplayMasuk.textContent = fileName;
            });
        }

        const fileInputKeluar = document.getElementById('file-upload-keluar');
        const fileNameDisplayKeluar = document.getElementById('file-name-keluar');
        if (fileInputKeluar) {
            fileInputKeluar.addEventListener('change', (e) => {
                const fileName = e.target.files[0] ? e.target.files[0].name : 'Belum ada file dipilih';
                fileNameDisplayKeluar.textContent = fileName;
            });
        }
    });
    </script>
    
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
// Menunggu semua konten halaman dimuat sebelum menjalankan skrip
document.addEventListener("DOMContentLoaded", () => {
  // Bagian 1: Logika untuk Toggle Sidebar Mobile
  const menuToggle = document.getElementById("menu-toggle");
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("overlay");

  if (menuToggle) {
    menuToggle.addEventListener("click", () => {
      sidebar.classList.toggle("open");
      overlay.classList.toggle("open");
    });
  }

  if (overlay) {
    overlay.addEventListener("click", () => {
      sidebar.classList.remove("open");
      overlay.classList.remove("open");
    });
  }

  // --- LOGIKA MINIMIZE/MAXIMIZE FORM SURAT KELUAR ---
  const toggleBtn = document.getElementById("toggle-form-btn");
  const formBody = document.getElementById("form-keluar-body");
  const listContainer = document.getElementById("list-keluar-container");

  if (toggleBtn) {
    const icon = toggleBtn.querySelector("i");
    const applyState = (isMinimized) => {
      if (isMinimized) {
        formBody.style.maxHeight = "0";
        formBody.style.opacity = "0";
        formBody.style.overflow = "hidden";
        formBody.style.paddingTop = "0";
        formBody.style.paddingBottom = "0";
        formBody.style.marginTop = "0";
        listContainer.style.marginTop = "-1rem";
        icon.classList.replace("fa-chevron-up", "fa-chevron-down");
        localStorage.setItem("formKeluarMinimized", "true");
      } else {
        formBody.style.maxHeight = "1000px";
        formBody.style.opacity = "1";
        formBody.style.overflow = "visible";
        formBody.style.paddingTop = "";
        formBody.style.paddingBottom = "";
        formBody.style.marginTop = "";
        listContainer.style.marginTop = "2rem";
        icon.classList.replace("fa-chevron-down", "fa-chevron-up");
        localStorage.setItem("formKeluarMinimized", "false");
      }
    };

    toggleBtn.addEventListener("click", () => {
      const isMinimized =
        localStorage.getItem("formKeluarMinimized") === "true";
      applyState(!isMinimized);
    });

    applyState(localStorage.getItem("formKeluarMinimized") === "true");
  }

  // --- LOGIKA MINIMIZE/MAXIMIZE FORM SURAT MASUK ---
  const toggleBtnMasuk = document.getElementById("toggle-form-masuk-btn");
  const formBodyMasuk = document.getElementById("form-masuk-body");
  const listContainerMasuk = document.getElementById("list-masuk-container");

  if (toggleBtnMasuk) {
    const icon = toggleBtnMasuk.querySelector("i");
    const applyStateMasuk = (isMinimized) => {
      if (isMinimized) {
        formBodyMasuk.style.maxHeight = "0";
        formBodyMasuk.style.opacity = "0";
        formBodyMasuk.style.overflow = "hidden";
        formBodyMasuk.style.paddingTop = "0";
        formBodyMasuk.style.paddingBottom = "0";
        formBodyMasuk.style.marginTop = "0";
        listContainerMasuk.style.marginTop = "-1rem";
        icon.classList.replace("fa-chevron-up", "fa-chevron-down");
        localStorage.setItem("formMasukMinimized", "true");
      } else {
        formBodyMasuk.style.maxHeight = "1500px";
        formBodyMasuk.style.opacity = "1";
        formBodyMasuk.style.overflow = "visible";
        formBodyMasuk.style.paddingTop = "";
        formBodyMasuk.style.paddingBottom = "";
        formBodyMasuk.style.marginTop = "";
        listContainerMasuk.style.marginTop = "2rem";
        icon.classList.replace("fa-chevron-down", "fa-chevron-up");
        localStorage.setItem("formMasukMinimized", "false");
      }
    };

    toggleBtnMasuk.addEventListener("click", () => {
      const isMinimized = localStorage.getItem("formMasukMinimized") === "true";
      applyStateMasuk(!isMinimized);
    });

    applyStateMasuk(localStorage.getItem("formMasukMinimized") === "true");
  }

  // --- Menampilkan nama file yang dipilih ---
  const fileInputMasuk = document.getElementById("file-upload-masuk");
  if (fileInputMasuk) {
    fileInputMasuk.addEventListener("change", (e) => {
      const fileName = e.target.files[0]
        ? e.target.files[0].name
        : "Belum ada file dipilih";
      document.getElementById("file-name-masuk").textContent = fileName;
    });
  }

  const fileInputKeluar = document.getElementById("file-upload-keluar");
  if (fileInputKeluar) {
    fileInputKeluar.addEventListener("change", (e) => {
      const fileName = e.target.files[0]
        ? e.target.files[0].name
        : "Belum ada file dipilih";
      document.getElementById("file-name-keluar").textContent = fileName;
    });
  }

  // Bagian 2: Logika untuk Pencarian AJAX di Halaman Surat Keluar
  const searchFormKeluar = document.getElementById("searchFormKeluar");
  if (searchFormKeluar) {
    const searchInput = document.getElementById("searchInputKeluar");
    const tableBody = document.getElementById("tableBodyKeluar");
    const paginationContainer = document.getElementById(
      "paginationContainerKeluar"
    );
    let debounceTimer;

    const fetchData = (page = 1) => {
      const query = searchInput.value;
      const url = `/ajax-search-surat-keluar?search=${encodeURIComponent(
        query
      )}&p=${page}`;

      tableBody.innerHTML =
        '<tr><td colspan="6" class="text-center p-8"><i class="fas fa-spinner fa-spin text-primary text-3xl"></i></td></tr>';
      paginationContainer.innerHTML = "";

      fetch(url)
        .then((response) => response.json())
        .then((data) => {
          updateTable(data.surat_list);
          updatePagination(data.pagination);
        })
        .catch((error) => {
          console.error("Error fetching data:", error);
          tableBody.innerHTML =
            '<tr><td colspan="6" class="text-center p-8 text-red-500">Gagal memuat data.</td></tr>';
        });
    };

    const updateTable = (suratList) => {
      tableBody.innerHTML = "";
      if (suratList.length === 0) {
        tableBody.innerHTML =
          '<tr><td colspan="6" class="px-6 py-8 text-center"><p class="text-lg">Data tidak ditemukan.</p></td></tr>';
        return;
      }
      let rowsHTML = "";
      // Ambil role dari data-attribute di body
      const isAdmin = document.body.dataset.userRole === "admin";

      suratList.forEach((surat) => {
        const lampiranHtml = surat.file_lampiran
          ? `<a href="/uploads/${surat.file_lampiran}" target="_blank" class="text-primary hover:underline"><i class="fas fa-file-alt"></i> Lihat</a>`
          : '<span class="text-gray-400">-</span>';

        let actionButtons = "";
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
                        <td class="px-6 py-4 font-medium text-primary">${escapeHTML(
                          surat.nomor_surat_lengkap
                        )}</td>
                        <td class="px-6 py-4">${escapeHTML(
                          surat.tgl_formatted
                        )}</td>
                        <td class="px-6 py-4">${escapeHTML(surat.perihal)}</td>
                        <td class="px-6 py-4">${escapeHTML(surat.tujuan)}</td>
                        <td class="px-6 py-4">${lampiranHtml}</td>
                        ${actionButtons}
                    </tr>`;
      });
      tableBody.innerHTML = rowsHTML;
    };

    const updatePagination = (pagination) => {
      paginationContainer.innerHTML = "";
      if (pagination.total_pages <= 1) return;

      let paginationHTML = '<div class="flex items-center justify-between">';
      paginationHTML += `<div>Halaman ${pagination.current_page} dari ${pagination.total_pages}</div>`;
      let buttonsHTML = '<div class="flex space-x-1">';
      if (pagination.current_page > 1) {
        buttonsHTML += `<button onclick="document.getElementById('searchFormKeluar').fetchData(${
          pagination.current_page - 1
        })" class="px-4 py-2 rounded-lg border">Sebelumnya</button>`;
      }
      if (pagination.current_page < pagination.total_pages) {
        buttonsHTML += `<button onclick="document.getElementById('searchFormKeluar').fetchData(${
          pagination.current_page + 1
        })" class="px-4 py-2 rounded-lg border">Selanjutnya</button>`;
      }
      buttonsHTML += "</div>";
      paginationHTML += buttonsHTML + "</div>";
      paginationContainer.innerHTML = paginationHTML;
    };

    searchFormKeluar.fetchData = fetchData; // Attach function to form element
    searchInput.addEventListener("input", () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => fetchData(1), 300);
    });
  }

  // Bagian 3: Logika untuk Cek Nomor Urut Agenda (Surat Masuk)
  const checkAgendaBtn = document.getElementById("checkAgendaBtn");
  if (checkAgendaBtn) {
    checkAgendaBtn.addEventListener("click", () => {
      const urut = document.getElementById("agenda_urut").value.trim();
      if (!urut) {
        Swal.fire({
          icon: "error",
          title: "Input Kosong",
          text: 'Isi "No. Urut" Agenda yang ingin dicek.',
        });
        return;
      }
      fetch("/ajax-check-nomor", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `nomor_urut=${encodeURIComponent(urut)}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.exists) {
            Swal.fire({
              icon: "warning",
              title: "Nomor Urut Sudah Ada!",
              html: `No. Urut Agenda <strong>${data.nomor}</strong> sudah terdaftar.`,
            });
          } else {
            Swal.fire({
              icon: "success",
              title: "Nomor Urut Tersedia!",
              html: `No. Urut Agenda <strong>${data.nomor}</strong> dapat digunakan.`,
            });
          }
        });
    });
  }

  // Bagian 4: Logika untuk Cek Nomor Urut Surat Keluar
  const checkNomorKeluarBtn = document.getElementById("checkNomorKeluarBtn");
  if (checkNomorKeluarBtn) {
    checkNomorKeluarBtn.addEventListener("click", () => {
      const urut = document.getElementById("nomor_urut_keluar").value.trim();
      if (!urut) {
        Swal.fire({
          icon: "error",
          title: "Input Kosong",
          text: 'Isi "No. Urut" yang ingin dicek.',
        });
        return;
      }
      fetch("/ajax-check-nomor-keluar", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `nomor_urut=${encodeURIComponent(urut)}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.exists) {
            Swal.fire({
              icon: "warning",
              title: "Nomor Urut Sudah Ada!",
              html: `No. Urut <strong>${data.nomor}</strong> sudah terdaftar.`,
            });
          } else {
            Swal.fire({
              icon: "success",
              title: "Nomor Urut Tersedia!",
              html: `No. Urut <strong>${data.nomor}</strong> dapat digunakan.`,
            });
          }
        });
    });
  }

  // Bagian 5: Logika untuk Pencarian AJAX di Halaman Surat Masuk
  const searchFormMasuk = document.getElementById("searchFormMasuk");
  if (searchFormMasuk) {
    const searchInputMasuk = document.getElementById("searchInputMasuk");
    const tableBodyMasuk = document.getElementById("tableBodyMasuk");
    const paginationContainerMasuk = document.getElementById(
      "paginationContainerMasuk"
    );
    let debounceTimerMasuk;

    const fetchDataMasuk = (page = 1) => {
      const query = searchInputMasuk.value;
      const url = `/ajax-search-surat-masuk?search=${encodeURIComponent(
        query
      )}&p=${page}`;

      tableBodyMasuk.innerHTML =
        '<tr><td colspan="6" class="text-center p-8"><i class="fas fa-spinner fa-spin text-primary text-3xl"></i></td></tr>';
      paginationContainerMasuk.innerHTML = "";

      fetch(url)
        .then((response) => response.json())
        .then((data) => {
          updateTableMasuk(data.surat_list);
          updatePaginationMasuk(data.pagination);
        })
        .catch((error) => {
          console.error("Error fetching data:", error);
          tableBodyMasuk.innerHTML =
            '<tr><td colspan="6" class="text-center p-8 text-red-500">Gagal memuat data.</td></tr>';
        });
    };

    const updateTableMasuk = (suratList) => {
      tableBodyMasuk.innerHTML = "";
      if (suratList.length === 0) {
        tableBodyMasuk.innerHTML =
          '<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500"><p>Data tidak ditemukan.</p></td></tr>';
        return;
      }
      let rowsHTML = "";
      const isAdmin = document.body.dataset.userRole === "admin";

      suratList.forEach((surat) => {
        const lampiranHtml = surat.file_lampiran
          ? `<a href="/uploads/${surat.file_lampiran}" target="_blank" class="text-primary hover:underline"><i class="fas fa-file-alt"></i> Lihat</a>`
          : '<span class="text-gray-400">-</span>';

        let actionButtons = "";
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
                        <td class="px-6 py-4 font-semibold text-gray-800">${escapeHTML(
                          surat.nomor_agenda_lengkap
                        )}</td>
                        <td class="px-6 py-4">${escapeHTML(
                          surat.asal_surat
                        )}</td>
                        <td class="px-6 py-4">${escapeHTML(surat.perihal)}</td>
                        <td class="px-6 py-4">${escapeHTML(
                          surat.tgl_terima_formatted
                        )}</td>
                        <td class="px-6 py-4">${lampiranHtml}</td>
                        ${actionButtons}
                    </tr>`;
      });
      tableBodyMasuk.innerHTML = rowsHTML;
    };

    const updatePaginationMasuk = (pagination) => {
      paginationContainerMasuk.innerHTML = "";
      if (pagination.total_pages <= 1) return;

      let paginationHTML = '<div class="flex items-center justify-between">';
      paginationHTML += `<div>Halaman ${pagination.current_page} dari ${pagination.total_pages}</div>`;

      let buttonsHTML = '<div class="flex space-x-1">';
      if (pagination.current_page > 1) {
        buttonsHTML += `<button onclick="document.getElementById('searchFormMasuk').fetchDataMasuk(${
          pagination.current_page - 1
        })" class="px-4 py-2 rounded-lg border text-sm">Sebelumnya</button>`;
      }
      if (pagination.current_page < pagination.total_pages) {
        buttonsHTML += `<button onclick="document.getElementById('searchFormMasuk').fetchDataMasuk(${
          pagination.current_page + 1
        })" class="px-4 py-2 rounded-lg border text-sm">Selanjutnya</button>`;
      }
      buttonsHTML += "</div>";

      paginationHTML += buttonsHTML + "</div>";
      paginationContainerMasuk.innerHTML = paginationHTML;
    };

    searchFormMasuk.fetchDataMasuk = fetchDataMasuk; // Attach function to form element
    searchInputMasuk.addEventListener("input", () => {
      clearTimeout(debounceTimerMasuk);
      debounceTimerMasuk = setTimeout(() => fetchDataMasuk(1), 300);
    });
  }
}); // Akhir dari DOMContentLoaded

// Global functions
function escapeHTML(str) {
  return str
    ? str
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;")
    : "";
}

function confirmDelete(type, id) {
  Swal.fire({
    title: "Anda Yakin?",
    text: "Data surat ini akan dihapus secara permanen!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Ya, Hapus!",
    cancelButtonText: "Batal",
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = `/hapus-surat-${type}?id=${id}`;
    }
  });
}

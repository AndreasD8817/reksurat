// Menunggu semua konten halaman dimuat sebelum menjalankan skrip
document.addEventListener("DOMContentLoaded", () => {
  // Bagian 1: Logika untuk Toggle Sidebar Mobile (Tidak Berubah)
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

  // Bagian 2: Logika Modal Detail (Berlaku untuk semua jenis surat)
  const modal = document.getElementById("detail-modal");
  const closeModalBtn = document.getElementById("close-modal-btn");
  const modalContent = document.getElementById("detail-modal-content");
  const modalBody = document.getElementById("modal-body-content");
  const modalFooter = document.getElementById("modal-footer-content");

  const openModal = () => {
    modal.classList.remove("hidden");
    setTimeout(() => {
      modal.classList.remove("opacity-0");
      modalContent.classList.remove("scale-95");
    }, 10);
  };

  const closeModal = () => {
    modal.classList.add("opacity-0");
    modalContent.classList.add("scale-95");
    setTimeout(() => {
      modal.classList.add("hidden");
      // Reset footer modal setiap kali ditutup
      modalFooter.innerHTML = "";
    }, 300);
  };

  if (modal) {
    closeModalBtn.addEventListener("click", closeModal);
    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        closeModal();
      }
    });
  }

  // --- Event Delegation untuk semua link detail ---
  document.body.addEventListener("click", function (e) {
    const link = e.target.closest("a");
    if (!link) return;

    // A. Detail Surat Masuk SEKWAN
    if (link.classList.contains("detail-link")) {
      e.preventDefault();
      const suratId = link.dataset.id;
      document.querySelector(
        "#detail-modal h3"
      ).innerHTML = `<i class="fas fa-envelope-open-text text-primary mr-3"></i> Detail Surat Masuk`;
      fetchAndShowDetails(`/ajax-get-surat-details?id=${suratId}`, "masuk");
    }

    // B. Detail Surat Keluar SEKWAN
    if (link.classList.contains("detail-link-keluar")) {
      e.preventDefault();
      const suratId = link.dataset.id;
      document.querySelector(
        "#detail-modal h3"
      ).innerHTML = `<i class="fas fa-paper-plane text-primary mr-3"></i> Detail Surat Keluar`;
      fetchAndShowDetails(
        `/ajax-get-surat-keluar-details?id=${suratId}`,
        "keluar"
      );
    }

    // C. Detail Surat Keluar DEWAN (BARU)
    if (link.classList.contains("detail-link-keluar-dewan")) {
      e.preventDefault();
      const suratId = link.dataset.id;
      document.querySelector(
        "#detail-modal h3"
      ).innerHTML = `<i class="fas fa-user-tie text-primary mr-3"></i> Detail Surat Keluar Dewan`;
      // UBAH: Panggil endpoint baru untuk dewan
      fetchAndShowDetails(
        `/ajax-get-surat-keluar-details-dewan?id=${suratId}`,
        "keluar-dewan"
      );
    }

    // D. Detail Surat Masuk DEWAN (BARU)
    if (link.classList.contains("detail-link-masuk-dewan")) {
      e.preventDefault();
      const suratId = link.dataset.id;
      document.querySelector(
        "#detail-modal h3"
      ).innerHTML = `<i class="fas fa-user-tie text-primary mr-3"></i> Detail Surat Masuk Dewan`;
      fetchAndShowDetails(
        `/ajax-get-surat-details-dewan?id=${suratId}`,
        "masuk-dewan"
      );
    }
  });

  // Fungsi terpusat untuk fetch dan render detail di modal
  function fetchAndShowDetails(url, type) {
    modalBody.innerHTML = `<div class="text-center p-8"><i class="fas fa-spinner fa-spin text-primary text-3xl"></i><p class="mt-2 text-gray-500">Memuat data...</p></div>`;
    openModal();

    fetch(url)
      .then((response) => {
        if (!response.ok) throw new Error("Data tidak ditemukan");
        return response.json();
      })
      .then((data) => {
        let contentHTML = "";
        let footerHTML = "";
        const lampiranLink = data.file_lampiran
          ? `<a href="/uploads-dewan/${data.file_lampiran}" target="_blank" class="inline-flex items-center text-white bg-primary hover:bg-secondary px-4 py-2 rounded-lg text-sm"><i class="fas fa-file-download mr-2"></i> Lihat Lampiran</a>`
          : '<span class="text-gray-500">Tidak ada lampiran</span>';

        if (type === "masuk") {
          contentHTML = getSuratMasukDetailHTML(data, lampiranLink);
          footerHTML = `<a href="/cetak-disposisi?id=${data.id}" target="_blank" class="inline-flex items-center text-white bg-green-500 hover:bg-green-600 px-4 py-2 rounded-lg text-sm"><i class="fas fa-print mr-2"></i> Cetak Disposisi</a>`;
        } else if (type === "keluar" || type === "keluar-dewan") {
          contentHTML = getSuratKeluarDetailHTML(data, lampiranLink);
          // Surat keluar tidak ada footer
        }

        modalBody.innerHTML = contentHTML;
        modalFooter.innerHTML = footerHTML;
      })
      .catch((error) => {
        modalBody.innerHTML = `<div class="text-center p-8"><i class="fas fa-exclamation-triangle text-red-500 text-3xl"></i><p class="mt-2 text-red-600">Gagal memuat data.</p></div>`;
      });
  }

  // Bagian 3: Logika Pencarian, Minimize, dan Cek Nomor
  // Fungsi helper untuk inisialisasi fungsionalitas halaman
  function setupPageFunctionality(config) {
    // Toggle Form
    const toggleBtn = document.getElementById(config.toggleBtnId);
    const formBody = document.getElementById(config.formBodyId);
    const listContainer = document.getElementById(config.listContainerId);

    if (toggleBtn && formBody && listContainer) {
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
          localStorage.setItem(config.localStorageKey, "true");
        } else {
          formBody.style.maxHeight = "1500px";
          formBody.style.opacity = "1";
          formBody.style.overflow = "visible";
          formBody.style.paddingTop = "";
          formBody.style.paddingBottom = "";
          formBody.style.marginTop = "";
          listContainer.style.marginTop = "2rem";
          icon.classList.replace("fa-chevron-down", "fa-chevron-up");
          localStorage.setItem(config.localStorageKey, "false");
        }
      };
      toggleBtn.addEventListener("click", () =>
        applyState(localStorage.getItem(config.localStorageKey) !== "true")
      );
      applyState(localStorage.getItem(config.localStorageKey) === "true");
    }

    // File Input Name Display
    const fileInput = document.getElementById(config.fileInputId);
    if (fileInput) {
      fileInput.addEventListener("change", (e) => {
        const fileName = e.target.files[0]
          ? e.target.files[0].name
          : "Belum ada file dipilih";
        document.getElementById(config.fileNameId).textContent = fileName;
      });
    }

    // Check Nomor Urut
    const checkBtn = document.getElementById(config.checkBtnId);
    if (checkBtn) {
      checkBtn.addEventListener("click", () => {
        const urut = document.getElementById(config.urutInputId).value.trim();
        if (!urut) {
          Swal.fire({
            icon: "error",
            title: "Input Kosong",
            text: `Isi "${config.urutLabel}" yang ingin dicek.`,
          });
          return;
        }
        fetch(config.checkUrl, {
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
                html: `${config.urutLabel} <strong>${data.nomor}</strong> sudah terdaftar.`,
              });
            } else {
              Swal.fire({
                icon: "success",
                title: "Nomor Urut Tersedia!",
                html: `${config.urutLabel} <strong>${data.nomor}</strong> dapat digunakan.`,
              });
            }
          });
      });
    }

    // AJAX Search
    const searchForm = document.getElementById(config.searchFormId);
    if (searchForm) {
      const searchInput = document.getElementById(config.searchInputId);
      const tableBody = document.getElementById(config.tableBodyId);
      const paginationContainer = document.getElementById(
        config.paginationContainerId
      );
      let debounceTimer;

      const fetchData = (page = 1) => {
        const query = searchInput.value;
        const url = `${config.searchUrl}?search=${encodeURIComponent(
          query
        )}&p=${page}`;
        tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8"><i class="fas fa-spinner fa-spin text-primary text-3xl"></i></td></tr>`;
        paginationContainer.innerHTML = "";
        fetch(url)
          .then((response) => response.json())
          .then((data) => {
            config.updateTable(data.surat_list);
            config.updatePagination(data.pagination);
          })
          .catch((error) => {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8 text-red-500">Gagal memuat data.</td></tr>`;
          });
      };

      searchForm.fetchData = fetchData;
      searchInput.addEventListener("input", () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchData(1), 300);
      });
    }
  }

  // --- Inisialisasi untuk setiap halaman ---

  // Halaman Surat Keluar SEKWAN
  if (document.getElementById("form-keluar-container")) {
    setupPageFunctionality({
      toggleBtnId: "toggle-form-btn",
      formBodyId: "form-keluar-body",
      listContainerId: "list-keluar-container",
      localStorageKey: "formKeluarMinimized",
      fileInputId: "file-upload-keluar",
      fileNameId: "file-name-keluar",
      checkBtnId: "checkNomorKeluarBtn",
      urutInputId: "nomor_urut_keluar",
      urutLabel: "No. Urut",
      checkUrl: "/ajax-check-nomor-keluar",
      searchFormId: "searchFormKeluar",
      searchInputId: "searchInputKeluar",
      tableBodyId: "tableBodyKeluar",
      paginationContainerId: "paginationContainerKeluar",
      searchUrl: "/ajax-search-surat-keluar",
      updateTable: updateTableSuratKeluar,
      updatePagination: (p) =>
        updatePagination(p, "searchFormKeluar", "fetchData"),
    });
  }

  // Halaman Surat Masuk SEKWAN
  if (document.getElementById("form-masuk-body")) {
    setupPageFunctionality({
      toggleBtnId: "toggle-form-masuk-btn",
      formBodyId: "form-masuk-body",
      listContainerId: "list-masuk-container",
      localStorageKey: "formMasukMinimized",
      fileInputId: "file-upload-masuk",
      fileNameId: "file-name-masuk",
      checkBtnId: "checkAgendaBtn",
      urutInputId: "agenda_urut",
      urutLabel: "No. Urut Agenda",
      checkUrl: "/ajax-check-nomor",
      searchFormId: "searchFormMasuk",
      searchInputId: "searchInputMasuk",
      tableBodyId: "tableBodyMasuk",
      paginationContainerId: "paginationContainerMasuk",
      searchUrl: "/ajax-search-surat-masuk",
      updateTable: updateTableSuratMasuk,
      updatePagination: (p) =>
        updatePagination(p, "searchFormMasuk", "fetchDataMasuk"),
    });
  }

  // Halaman Surat Keluar DEWAN (BARU)
  if (document.getElementById("form-keluar-dewan-container")) {
    setupPageFunctionality({
      toggleBtnId: "toggle-form-dewan-btn",
      formBodyId: "form-keluar-dewan-body",
      listContainerId: "list-keluar-dewan-container",
      localStorageKey: "formKeluarDewanMinimized",
      fileInputId: "file-upload-keluar-dewan",
      fileNameId: "file-name-keluar-dewan",
      checkBtnId: "checkNomorKeluarDewanBtn",
      urutInputId: "nomor_urut_keluar_dewan",
      urutLabel: "No. Urut",
      checkUrl: "/ajax-check-nomor-keluar-dewan", // Endpoint baru
      searchFormId: "searchFormKeluarDewan",
      searchInputId: "searchInputKeluarDewan",
      tableBodyId: "tableBodyKeluarDewan",
      paginationContainerId: "paginationContainerKeluarDewan",
      searchUrl: "/ajax-search-surat-keluar-dewan", // Endpoint baru
      updateTable: updateTableSuratKeluarDewan, // Fungsi render tabel baru
      updatePagination: (p) =>
        updatePagination(p, "searchFormKeluarDewan", "fetchData"),
    });
  }

  // Halaman Surat Masuk DEWAN (BARU)
  if (document.getElementById("form-masuk-dewan-container")) {
    setupPageFunctionality({
      toggleBtnId: "toggle-form-masuk-dewan-btn",
      formBodyId: "form-masuk-dewan-body",
      listContainerId: "list-masuk-dewan-container",
      localStorageKey: "formMasukDewanMinimized",
      fileInputId: "file-upload-masuk-dewan",
      fileNameId: "file-name-masuk-dewan",
      checkBtnId: "checkAgendaDewanBtn",
      urutInputId: "agenda_urut_dewan",
      urutLabel: "No. Urut Agenda",
      checkUrl: "/ajax-check-nomor-agenda-dewan",
      searchFormId: "searchFormMasukDewan",
      searchInputId: "searchInputMasukDewan",
      tableBodyId: "tableBodyMasukDewan",
      paginationContainerId: "paginationContainerMasukDewan",
      searchUrl: "/ajax-search-surat-masuk-dewan",
      updateTable: updateTableSuratMasukDewan,
      updatePagination: (p) =>
        updatePagination(p, "searchFormMasukDewan", "fetchData"),
    });
  }
}); // Akhir dari DOMContentLoaded

// =======================================================
// --- FUNGSI-FUNGSI GLOBAL & TEMPLATE RENDERER ---
// =======================================================

// Fungsi untuk menghindari XSS
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

// Fungsi konfirmasi hapus
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

// Fungsi untuk membuat HTML pagination
function updatePagination(pagination, formId, fetchFunctionName) {
  const container = document.getElementById(
    `paginationContainer${formId.replace("searchForm", "")}`
  );
  container.innerHTML = "";
  if (pagination.total_pages <= 1) return;

  let paginationHTML = `<div class="flex items-center justify-between"><div class="text-sm text-gray-600">Halaman ${pagination.current_page} dari ${pagination.total_pages}</div><div class="flex space-x-1">`;
  if (pagination.current_page > 1) {
    paginationHTML += `<button onclick="document.getElementById('${formId}').${fetchFunctionName}(${
      pagination.current_page - 1
    })" class="px-4 py-2 rounded-lg border text-sm">Sebelumnya</button>`;
  }
  if (pagination.current_page < pagination.total_pages) {
    paginationHTML += `<button onclick="document.getElementById('${formId}').${fetchFunctionName}(${
      pagination.current_page + 1
    })" class="px-4 py-2 rounded-lg border text-sm">Selanjutnya</button>`;
  }
  paginationHTML += "</div></div>";
  container.innerHTML = paginationHTML;
}

// --- FUNGSI UNTUK MERENDER ISI TABEL ---

function updateTableSuratKeluar(suratList) {
  const tableBody = document.getElementById("tableBodyKeluar");
  renderTableRows(tableBody, suratList, getSuratKeluarRowHTML);
}

function updateTableSuratKeluarDewan(suratList) {
  const tableBody = document.getElementById("tableBodyKeluarDewan");
  renderTableRows(tableBody, suratList, getSuratKeluarDewanRowHTML);
}

function updateTableSuratMasuk(suratList) {
  const tableBody = document.getElementById("tableBodyMasuk");
  renderTableRows(tableBody, suratList, getSuratMasukRowHTML);
}

function renderTableRows(tableBody, list, rowRenderer) {
  tableBody.innerHTML = "";
  if (list.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500"><p>Data tidak ditemukan.</p></td></tr>`;
    return;
  }
  tableBody.innerHTML = list.map(rowRenderer).join("");
}

// --- FUNGSI UNTUK MEMBUAT SATU BARIS HTML (ROW) ---

const isAdmin = document.body.dataset.userRole === "admin";

function getActionButtons(type, id) {
  if (!isAdmin) return "";
  return `
    <td class="px-6 py-4">
        <div class="flex space-x-2">
            <a href="/edit-surat-${type}?id=${id}" class="text-blue-500 hover:text-blue-700" title="Edit"><i class="fas fa-edit"></i></a>
            <button onclick="confirmDelete('${type}', ${id})" class="text-red-500 hover:text-red-700" title="Hapus"><i class="fas fa-trash"></i></button>
        </div>
    </td>`;
}

function getSuratKeluarRowHTML(surat) {
  const lampiranHtml = surat.file_lampiran
    ? `<a href="/uploads/surat_keluar/${surat.file_lampiran}" target="_blank" class="text-primary hover:underline"><i class="fas fa-file-alt"></i> Lihat</a>`
    : '<span class="text-gray-400">-</span>';
  return `<tr class="hover:bg-gray-50">
        <td class="px-6 py-4 font-medium"><a href="#" class="text-primary hover:underline detail-link-keluar" data-id="${
          surat.id
        }">${escapeHTML(surat.nomor_surat_lengkap)}</a></td>
        <td class="px-6 py-4">${escapeHTML(surat.tgl_formatted)}</td>
        <td class="px-6 py-4">${escapeHTML(surat.perihal)}</td>
        <td class="px-6 py-4">${escapeHTML(surat.tujuan)}</td>
        <td class="px-6 py-4">${lampiranHtml}</td>
        ${getActionButtons("keluar", surat.id)}
    </tr>`;
}

function getSuratKeluarDewanRowHTML(surat) {
  const lampiranHtml = surat.file_lampiran
    ? `<a href="/uploads-dewan/surat_keluar_dewan/${surat.file_lampiran}" target="_blank" class="text-primary hover:underline"><i class="fas fa-file-alt"></i> Lihat</a>`
    : '<span class="text-gray-400">-</span>';
  return `<tr class="hover:bg-gray-50">
        <td class="px-6 py-4 font-medium"><a href="#" class="text-primary hover:underline detail-link-keluar-dewan" data-id="${
          surat.id
        }">${escapeHTML(surat.nomor_surat_lengkap)}</a></td>
        <td class="px-6 py-4">${escapeHTML(surat.tgl_formatted)}</td>
        <td class="px-6 py-4">${escapeHTML(surat.perihal)}</td>
        <td class="px-6 py-4">${escapeHTML(surat.tujuan)}</td>
        <td class="px-6 py-4">${lampiranHtml}</td>
        ${getActionButtons("keluar-dewan", surat.id)}
    </tr>`;
}

function getSuratMasukRowHTML(surat) {
  const lampiranHtml = surat.file_lampiran
    ? `<a href="/uploads/surat_masuk/${surat.file_lampiran}" target="_blank" class="text-primary hover:underline"><i class="fas fa-file-alt"></i> Lihat</a>`
    : '<span class="text-gray-400">-</span>';
  return `<tr class="hover:bg-gray-50">
        <td class="px-6 py-4 font-semibold"><a href="#" class="text-primary hover:underline detail-link" data-id="${
          surat.id
        }">${escapeHTML(surat.nomor_agenda_lengkap)}</a></td>
        <td class="px-6 py-4">${escapeHTML(surat.asal_surat)}</td>
        <td class="px-6 py-4">${escapeHTML(surat.perihal)}</td>
        <td class="px-6 py-4">${escapeHTML(surat.tgl_terima_formatted)}</td>
        <td class="px-6 py-4">${lampiranHtml}</td>
        ${getActionButtons("masuk", surat.id)}
    </tr>`;
}

// --- FUNGSI UNTUK MEMBUAT KONTEN MODAL DETAIL ---

function getSuratMasukDetailHTML(data, lampiranLink) {
  return `<div class="grid grid-cols-3 gap-x-6 gap-y-4 text-sm">
        <div class="col-span-1 text-gray-500">No. Agenda</div><div class="col-span-2 font-semibold text-gray-800">: ${escapeHTML(
          data.nomor_agenda_lengkap
        )}</div>
        <div class="col-span-1 text-gray-500">No. Surat</div><div class="col-span-2 font-medium text-gray-700">: ${escapeHTML(
          data.nomor_surat_lengkap
        )}</div>
        <div class="col-span-1 text-gray-500">Asal Surat</div><div class="col-span-2 text-gray-700">: ${escapeHTML(
          data.asal_surat
        )}</div>
        <div class="col-span-1 text-gray-500">Sifat Surat</div><div class="col-span-2 text-gray-700">: <span class="font-semibold px-2 py-1 bg-blue-100 text-blue-700 rounded-full">${escapeHTML(
          data.sifat_surat
        )}</span></div>
        <div class="col-span-1 text-gray-500">Tanggal Surat</div><div class="col-span-2 text-gray-700">: ${escapeHTML(
          data.tgl_surat_formatted
        )}</div>
        <div class="col-span-1 text-gray-500">Tanggal Diterima</div><div class="col-span-2 text-gray-700">: ${escapeHTML(
          data.tgl_diterima_formatted
        )}</div>
        <div class="col-span-3 pt-2 mt-2 border-t"></div>
        <div class="col-span-1 text-gray-500 self-start">Perihal</div><div class="col-span-2 text-gray-700 self-start">: ${escapeHTML(
          data.perihal
        )}</div>
        <div class="col-span-1 text-gray-500 self-start">Keterangan</div><div class="col-span-2 text-gray-700 self-start">: ${
          escapeHTML(data.keterangan) || "-"
        }</div>
        <div class="col-span-3 pt-2 mt-2 border-t"></div>
        <div class="col-span-1 text-gray-500">Lampiran</div><div class="col-span-2">${lampiranLink}</div>
        <div class="col-span-1 text-gray-500 mt-2">Dicatat pada</div><div class="col-span-2 text-gray-500 mt-2">: ${escapeHTML(
          data.tgl_input_formatted
        )}</div>
    </div>`;
}

function getSuratKeluarDetailHTML(data, lampiranLink) {
  return `<div class="grid grid-cols-3 gap-x-6 gap-y-4 text-sm">
        <div class="col-span-1 text-gray-500">No. Surat</div><div class="col-span-2 font-semibold text-gray-800">: ${escapeHTML(
          data.nomor_surat_lengkap
        )}</div>
        <div class="col-span-1 text-gray-500">Tujuan</div><div class="col-span-2 text-gray-700">: ${escapeHTML(
          data.tujuan
        )}</div>
        <div class="col-span-1 text-gray-500">Sifat Surat</div><div class="col-span-2 text-gray-700">: <span class="font-semibold px-2 py-1 bg-blue-100 text-blue-700 rounded-full">${escapeHTML(
          data.sifat_surat
        )}</span></div>
        <div class="col-span-1 text-gray-500">Tanggal Surat</div><div class="col-span-2 text-gray-700">: ${escapeHTML(
          data.tgl_surat_formatted
        )}</div>
        <div class="col-span-1 text-gray-500">Konseptor</div><div class="col-span-2 text-gray-700">: ${
          escapeHTML(data.konseptor) || "-"
        }</div>
        <div class="col-span-3 pt-2 mt-2 border-t"></div>
        <div class="col-span-1 text-gray-500 self-start">Perihal</div><div class="col-span-2 text-gray-700 self-start">: ${escapeHTML(
          data.perihal
        )}</div>
        <div class="col-span-1 text-gray-500 self-start">Keterangan</div><div class="col-span-2 text-gray-700 self-start">: ${
          escapeHTML(data.keterangan) || "-"
        }</div>
        <div class="col-span-1 text-gray-500 self-start">Hub. dgn Surat No.</div><div class="col-span-2 text-gray-700 self-start">: ${
          escapeHTML(data.hub_surat_no) || "-"
        }</div>
        <div class="col-span-3 pt-2 mt-2 border-t"></div>
        <div class="col-span-1 text-gray-500">Lampiran</div><div class="col-span-2">${lampiranLink}</div>
        <div class="col-span-1 text-gray-500 mt-2">Dicatat pada</div><div class="col-span-2 text-gray-500 mt-2">: ${escapeHTML(
          data.tgl_input_formatted
        )}</div>
    </div>`;
}

// --- FUNGSI UNTUK MERENDER ISI TABEL (Tambahan untuk Surat Masuk Dewan) ---
function updateTableSuratMasukDewan(suratList) {
  const tableBody = document.getElementById("tableBodyMasukDewan");
  renderTableRows(tableBody, suratList, getSuratMasukDewanRowHTML);
}

// --- FUNGSI UNTUK MEMBUAT SATU BARIS HTML (ROW) (Tambahan untuk Surat Masuk Dewan) ---
function getSuratMasukDewanRowHTML(surat) {
  const lampiranHtml = surat.file_lampiran
    ? `<a href="/uploads-dewan/${surat.file_lampiran}" target="_blank" class="text-primary hover:underline"><i class="fas fa-file-alt"></i> Lihat</a>`
    : '<span class="text-gray-400">-</span>';
  return `<tr class="hover:bg-gray-50">
        <td class="px-6 py-4 font-semibold"><a href="#" class="text-primary hover:underline detail-link-masuk-dewan" data-id="${
          surat.id
        }">${escapeHTML(surat.nomor_agenda_lengkap)}</a></td>
        <td class="px-6 py-4">${escapeHTML(surat.asal_surat)}</td>
        <td class="px-6 py-4">${escapeHTML(surat.perihal)}</td>
        <td class="px-6 py-4">${escapeHTML(surat.tgl_terima_formatted)}</td>
        <td class="px-6 py-4">${lampiranHtml}</td>
        ${getActionButtons("masuk-dewan", surat.id)}
    </tr>`;
}

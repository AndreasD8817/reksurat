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

  // Bagian 2: Logika Modal Detail
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

  // Event Delegation untuk semua link detail
  document.body.addEventListener("click", function (e) {
    const link = e.target.closest(
      "a.detail-link, a.detail-link-keluar, a.detail-link-keluar-dewan, a.detail-link-masuk-dewan"
    );
    if (!link) return;

    e.preventDefault();
    const suratId = link.dataset.id;
    let apiUrl, modalTitle, type;

    if (link.classList.contains("detail-link")) {
      modalTitle = `<i class="fas fa-envelope-open-text text-primary mr-3"></i> Detail Surat Masuk`;
      apiUrl = `/ajax-get-surat-details?id=${suratId}`;
      type = "masuk";
    } else if (link.classList.contains("detail-link-keluar")) {
      modalTitle = `<i class="fas fa-paper-plane text-primary mr-3"></i> Detail Surat Keluar`;
      apiUrl = `/ajax-get-surat-keluar-details?id=${suratId}`;
      type = "keluar";
    } else if (link.classList.contains("detail-link-keluar-dewan")) {
      modalTitle = `<i class="fas fa-user-tie text-primary mr-3"></i> Detail Surat Keluar Dewan`;
      apiUrl = `/ajax-get-surat-keluar-details-dewan?id=${suratId}`;
      type = "keluar-dewan";
    } else if (link.classList.contains("detail-link-masuk-dewan")) {
      modalTitle = `<i class="fas fa-user-tie text-primary mr-3"></i> Detail Surat Masuk Dewan`;
      apiUrl = `/ajax-get-surat-details-dewan?id=${suratId}`;
      type = "masuk-dewan";
    }

    if (apiUrl) {
      document.querySelector("#detail-modal h3").innerHTML = modalTitle;
      fetchAndShowDetails(apiUrl, type);
    }
  });

  function fetchAndShowDetails(url, type) {
    modalBody.innerHTML = `<div class="text-center p-8"><i class="fas fa-spinner fa-spin text-primary text-3xl"></i><p class="mt-2 text-gray-500">Memuat data...</p></div>`;
    openModal();

    fetch(url)
      .then((response) => {
        if (!response.ok)
          throw new Error("Data tidak ditemukan atau terjadi error server.");
        return response.json();
      })
      .then((data) => {
        let contentHTML = "";
        let footerHTML = "";

        let basePath = type.includes("dewan") ? "/uploads-dewan/" : "/uploads/";
        const lampiranLink = data.file_lampiran
          ? `<a href="${basePath}${data.file_lampiran}" target="_blank" class="inline-flex items-center text-white bg-primary hover:bg-secondary px-4 py-2 rounded-lg text-sm"><i class="fas fa-file-download mr-2"></i> Lihat Lampiran</a>`
          : '<span class="text-gray-500">Tidak ada lampiran</span>';

        if (type === "masuk") {
          contentHTML = getSuratMasukDetailHTML(data, lampiranLink);
          footerHTML = `<a href="/cetak-disposisi?id=${data.id}" target="_blank" class="inline-flex items-center text-white bg-green-500 hover:bg-green-600 px-4 py-2 rounded-lg text-sm"><i class="fas fa-print mr-2"></i> Cetak Disposisi</a>`;
        } else if (type === "masuk-dewan") {
          contentHTML = getSuratMasukDetailHTML(data, lampiranLink);
          footerHTML = `<a href="/cetak-disposisi-dewan?id=${data.id}" target="_blank" class="inline-flex items-center text-white bg-green-500 hover:bg-green-600 px-4 py-2 rounded-lg text-sm"><i class="fas fa-print mr-2"></i> Cetak Disposisi</a>`;
        } else if (type === "keluar" || type === "keluar-dewan") {
          contentHTML = getSuratKeluarDetailHTML(data, lampiranLink);
        }

        modalBody.innerHTML = contentHTML;
        modalFooter.innerHTML = footerHTML;
      })
      .catch((error) => {
        modalBody.innerHTML = `<div class="text-center p-8"><i class="fas fa-exclamation-triangle text-red-500 text-3xl"></i><p class="mt-2 text-red-600">${error.message}</p></div>`;
      });
  }

  // Bagian 3: Logika Pencarian, Minimize, dan Cek Nomor
  function setupPageFunctionality(config) {
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

    const fileInput = document.getElementById(config.fileInputId);
    if (fileInput) {
      fileInput.addEventListener("change", (e) => {
        const fileName = e.target.files[0]
          ? e.target.files[0].name
          : "Belum ada file dipilih";
        document.getElementById(config.fileNameId).textContent = fileName;
      });
    }

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
          })
          .catch(() =>
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "Gagal mengecek nomor urut.",
            })
          );
      });
    }

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
            config.updatePagination(
              data.pagination,
              config.searchFormId,
              "fetchData"
            );
          })
          .catch(() => {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8 text-red-500">Gagal memuat data.</td></tr>`;
          });
      };

      searchForm.fetchData = fetchData;
      searchInput.addEventListener("input", () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchData(1), 300);
      });
      searchForm.addEventListener("submit", (e) => {
        e.preventDefault();
        fetchData(1);
      });
    }
  }

  // --- Inisialisasi untuk setiap halaman ---
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
      updatePagination: updatePagination,
    });
  }

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
      updatePagination: updatePagination,
    });
  }

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
      checkUrl: "/ajax-check-nomor-keluar-dewan",
      searchFormId: "searchFormKeluarDewan",
      searchInputId: "searchInputKeluarDewan",
      tableBodyId: "tableBodyKeluarDewan",
      paginationContainerId: "paginationContainerKeluarDewan",
      searchUrl: "/ajax-search-surat-keluar-dewan",
      updateTable: updateTableSuratKeluarDewan,
      updatePagination: updatePagination,
    });
  }

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
      updatePagination: updatePagination,
    });
  }
});

// =======================================================
// --- FUNGSI-FUNGSI GLOBAL & TEMPLATE RENDERER ---
// =======================================================

function escapeHTML(str) {
  return str
    ? String(str).replace(/[&<>"']/g, (match) => {
        const map = {
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#039;",
        };
        return map[match];
      })
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

function updatePagination(pagination, formId, fetchFunctionName) {
  const containerId = `paginationContainer${formId.replace("searchForm", "")}`;
  const container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = "";
  if (pagination.total_pages <= 1) return;

  let paginationHTML = `<div class="flex items-center justify-between"><div class="text-sm text-gray-600">Halaman ${pagination.current_page} dari ${pagination.total_pages}</div><div class="flex space-x-1">`;
  if (pagination.current_page > 1) {
    paginationHTML += `<button type="button" onclick="document.getElementById('${formId}').fetchData(${
      pagination.current_page - 1
    })" class="px-4 py-2 rounded-lg border text-sm">Sebelumnya</button>`;
  }
  if (pagination.current_page < pagination.total_pages) {
    paginationHTML += `<button type="button" onclick="document.getElementById('${formId}').fetchData(${
      pagination.current_page + 1
    })" class="px-4 py-2 rounded-lg border text-sm">Selanjutnya</button>`;
  }
  paginationHTML += "</div></div>";
  container.innerHTML = paginationHTML;
}

function renderTableRows(tableBody, list, rowRenderer) {
  if (!tableBody) return;
  if (list.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500"><p>Data tidak ditemukan.</p></td></tr>`;
  } else {
    tableBody.innerHTML = list.map(rowRenderer).join("");
  }
}

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

function updateTableSuratMasukDewan(suratList) {
  const tableBody = document.getElementById("tableBodyMasukDewan");
  renderTableRows(tableBody, suratList, getSuratMasukDewanRowHTML);
}

function getSuratKeluarRowHTML(surat) {
  const lampiranHtml = surat.file_lampiran
    ? `<a href="/uploads/${surat.file_lampiran}" target="_blank" class="text-primary hover:underline"><i class="fas fa-file-alt"></i> Lihat</a>`
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
    ? `<a href="/uploads-dewan/${surat.file_lampiran}" target="_blank" class="text-primary hover:underline"><i class="fas fa-file-alt"></i> Lihat</a>`
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
    ? `<a href="/uploads/${surat.file_lampiran}" target="_blank" class="text-primary hover:underline"><i class="fas fa-file-alt"></i> Lihat</a>`
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

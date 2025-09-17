// assets/js/modules/page.js
import { updatePagination } from "./ui.js";

/**
 * Mengatur semua fungsionalitas interaktif untuk sebuah halaman (form, pencarian, dll).
 * @param {object} config Objek konfigurasi berisi ID elemen dan URL.
 */
export function setupPageFunctionality(config) {
  const toggleBtn = document.getElementById(config.toggleBtnId);
  const formBody = document.getElementById(config.formBodyId);
  const listContainer = document.getElementById(config.listContainerId);

  // Logika untuk minimize/expand form
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
        formBody.style.maxHeight = "1500px"; // Cukup besar untuk mengakomodasi form
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

  // Logika untuk menampilkan nama file yang di-upload
  const fileInput = document.getElementById(config.fileInputId);
  if (fileInput) {
    fileInput.addEventListener("change", (e) => {
      const fileName = e.target.files[0]
        ? e.target.files[0].name
        : "Belum ada file dipilih";
      document.getElementById(config.fileNameId).textContent = fileName;
    });
  }

  // Logika untuk tombol cek ketersediaan nomor
  const checkBtn = document.getElementById(config.checkBtnId);
  if (checkBtn) {
    checkBtn.addEventListener("click", () => {
      const urut = document.getElementById(config.urutInputId).value.trim();

      // --- MODIFIKASI: Mengambil tahun dari dropdown manual ---
      const form = checkBtn.closest("form");
      // Cari dropdown tahun berdasarkan 'name'
      const yearSelect = form.querySelector('select[name="tahun_penomoran"]');
      const tahun = yearSelect
        ? yearSelect.value
        : new Date().getFullYear().toString();
      // --- AKHIR MODIFIKASI ---

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
        // Kirim nomor urut dan tahun yang dipilih
        body: `nomor_urut=${encodeURIComponent(
          urut
        )}&tahun=${encodeURIComponent(tahun)}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.exists) {
            Swal.fire({
              icon: "warning",
              title: "Nomor Urut Sudah Ada!",
              html: `${config.urutLabel} <strong>${data.nomor}</strong> untuk tahun <strong>${data.tahun}</strong> sudah terdaftar.`,
            });
          } else {
            Swal.fire({
              icon: "success",
              title: "Nomor Urut Tersedia!",
              html: `${config.urutLabel} <strong>${data.nomor}</strong> untuk tahun <strong>${data.tahun}</strong> dapat digunakan.`,
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

  // Logika untuk pencarian data
  const searchForm = document.getElementById(config.searchFormId);
  if (searchForm) {
    const searchInput = document.getElementById(config.searchInputId);
    const tableBody = document.getElementById(config.tableBodyId);
    let debounceTimer;

    const fetchData = (page = 1) => {
      // Ambil nilai dari search input dan filter tahun
      const query = searchInput.value;
      const filterTahunEl = document.getElementById(config.filterTahunId);
      const startDateEl = document.getElementById(config.startDateId);
      const endDateEl = document.getElementById(config.endDateId);
      const year = filterTahunEl ? filterTahunEl.value : "";
      const startDate = startDateEl ? startDateEl.value : "";
      const endDate = endDateEl ? endDateEl.value : "";

      // Bangun URL dengan parameter search, page, dan year
      const url = `${config.searchUrl}?search=${encodeURIComponent(
        query
      )}&p=${page}&year=${encodeURIComponent(
        year
      )}&start_date=${encodeURIComponent(
        startDate
      )}&end_date=${encodeURIComponent(endDate)}`;

      tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8"><i class="fas fa-spinner fa-spin text-primary text-3xl"></i></td></tr>`;

      const paginationContainer = document.getElementById(
        config.paginationContainerId
      );
      if (paginationContainer) paginationContainer.innerHTML = "";

      fetch(url)
        .then((response) => response.json())
        .then((data) => {
          // Modifikasi: Cek berbagai kemungkinan nama properti data
          const listData = data.logs || data.disposisi_list || data.surat_list;
          config.updateTable(listData);

          if (data.pagination) {
            updatePagination(data.pagination, config.searchFormId);
          }
        })
        .catch(() => {
          tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8 text-red-500">Gagal memuat data.</td></tr>`;
        });
    };

    searchForm.fetchData = fetchData;
    if (searchInput) {
      searchInput.addEventListener("input", () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchData(1), 300);
      });
    }

    // Tambahkan event listener untuk filter tahun
    const filterTahunEl = document.getElementById(config.filterTahunId);
    if (filterTahunEl) {
      filterTahunEl.addEventListener("change", () => fetchData(1));
    }

    // Tambahkan event listener untuk filter rentang tanggal
    const startDateEl = document.getElementById(config.startDateId);
    const endDateEl = document.getElementById(config.endDateId);
    if (startDateEl) {
      startDateEl.addEventListener("change", () => fetchData(1));
    }
    if (endDateEl) {
      endDateEl.addEventListener("change", () => fetchData(1));
    }

    searchForm.addEventListener("submit", (e) => {
      e.preventDefault();
      fetchData(1);
    });
  }
}

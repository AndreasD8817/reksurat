// assets/js/app.js
import { initModal } from "./modules/modal.js";
import { setupPageFunctionality } from "./modules/page.js";
import {
  updateTableSuratKeluar,
  updateTableSuratKeluarDewan,
  updateTableSuratMasuk,
  updateTableSuratMasukDewan,
  updateTableDisposisi, // <- Impor fungsi baru
  confirmDelete,
} from "./modules/ui.js";

// Membuat fungsi confirmDelete bisa diakses secara global (dari atribut onclick di HTML)
window.confirmDelete = confirmDelete;

// Event listener utama yang berjalan setelah semua elemen HTML dimuat
document.addEventListener("DOMContentLoaded", () => {
  // Inisialisasi Sidebar Mobile
  const menuToggle = document.getElementById("menu-toggle");
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("overlay");
  if (menuToggle && sidebar && overlay) {
    menuToggle.addEventListener("click", () => {
      sidebar.classList.toggle("open");
      overlay.classList.toggle("open");
    });
    overlay.addEventListener("click", () => {
      sidebar.classList.remove("open");
      overlay.classList.remove("open");
    });
  }

  // Inisialisasi semua fungsionalitas modal
  initModal();

  // Inisialisasi fungsionalitas spesifik berdasarkan halaman yang aktif
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
    });
  }

  // **BARU**: Inisialisasi untuk halaman Disposisi Sekwan
  if (document.getElementById("searchFormDisposisi")) {
    setupPageFunctionality({
      toggleBtnId: null,
      formBodyId: null,
      listContainerId: null,
      localStorageKey: null,
      fileInputId: "file-upload-disposisi",
      fileNameId: "file-name-disposisi",
      checkBtnId: null,
      urutInputId: null,
      urutLabel: null,
      checkUrl: null,
      searchFormId: "searchFormDisposisi",
      searchInputId: "searchInputDisposisi",
      tableBodyId: "tableBodyDisposisi",
      paginationContainerId: "paginationContainerDisposisi",
      searchUrl: "/ajax-search-disposisi-sekwan",
      updateTable: updateTableDisposisi,
    });
  }
});

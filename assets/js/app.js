// assets/js/app.js
import { initModal } from "./modules/modal.js";
import { setupPageFunctionality } from "./modules/page.js";
import {
  updateTableSuratKeluar,
  updateTableSuratKeluarDewan,
  updateTableSuratMasuk,
  updateTableSuratMasukDewan,
  updateTableDisposisi,
  confirmDelete,
  showLogDetailModal,
} from "./modules/ui.js";

// Jadikan fungsi confirmDelete global agar bisa diakses dari HTML
window.confirmDelete = confirmDelete;

document.addEventListener("DOMContentLoaded", () => {
  // Inisialisasi Toggle Password
  const initPasswordToggle = () => {
    const toggleButtons = document.querySelectorAll(".toggle-password");
    toggleButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const passwordInput = this.parentElement.querySelector("input");
        const icon = this.querySelector("i");

        if (passwordInput && passwordInput.type === "password") {
          passwordInput.type = "text";
          icon.classList.remove("fa-eye");
          icon.classList.add("fa-eye-slash");
        } else if (passwordInput) {
          passwordInput.type = "password";
          icon.classList.remove("fa-eye-slash");
          icon.classList.add("fa-eye");
        }
      });
    });
  };
  initPasswordToggle();

  // --- MODIFIKASI DIMULAI DISINI ---
  // Inisialisasi Sidebar Mobile
  const menuToggle = document.getElementById("menu-toggle");
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("overlay");
  const body = document.body; // Ambil elemen body

  if (menuToggle && sidebar && overlay) {
    menuToggle.addEventListener("click", () => {
      sidebar.classList.toggle("open");
      overlay.classList.toggle("open");
      body.classList.toggle("sidebar-open"); // Tambah/Hapus class untuk lock scroll
    });
    overlay.addEventListener("click", () => {
      sidebar.classList.remove("open");
      overlay.classList.remove("open");
      body.classList.remove("sidebar-open"); // Selalu hapus class saat overlay diklik
    });
  }
  // --- MODIFIKASI SELESAI DISINI ---

  // Inisialisasi semua modal
  initModal();

  // Inisialisasi PDF Modal
  const pdfModal = document.getElementById("pdf-modal");
  if (pdfModal) {
    const closePdfModalBtn = document.getElementById("close-pdf-modal-btn");
    const pdfModalContent = document.getElementById("pdf-modal-content");
    const pdfEmbed = document.getElementById("pdf-embed");
    const pdfModalTitle = document.getElementById("pdf-modal-title");
    const pdfDownloadLink = document.getElementById("pdf-download-link");

    const openPdfModal = (pdfSrc, agendaNo) => {
      pdfEmbed.src = pdfSrc;
      pdfModalTitle.textContent = `Lampiran Surat - No. Agenda: ${agendaNo}`;
      pdfDownloadLink.href = pdfSrc;
      pdfModal.classList.remove("hidden");
      setTimeout(() => {
        pdfModal.classList.remove("opacity-0");
        pdfModalContent.classList.remove("scale-95");
      }, 10);
    };

    const closePdfModal = () => {
      pdfModal.classList.add("opacity-0");
      pdfModalContent.classList.add("scale-95");
      setTimeout(() => {
        pdfModal.classList.add("hidden");
        pdfEmbed.src = ""; // Kosongkan src untuk menghentikan pemuatan
      }, 300);
    };

    closePdfModalBtn.addEventListener("click", closePdfModal);
    pdfModal.addEventListener("click", (e) => {
      if (e.target === pdfModal) closePdfModal();
    });

    document.body.addEventListener("click", (e) => {
      const trigger = e.target.closest(".pdf-modal-trigger");
      if (!trigger) return;
      e.preventDefault();
      openPdfModal(trigger.dataset.pdfSrc, trigger.dataset.agendaNo);
    });
  }

  // Inisialisasi Fungsionalitas Halaman yang ada
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
      yearSelectId: "tahun_penomoran_keluar",
      suratType: "keluar",
      dateInputName: "tanggal_surat",
      searchFormId: "searchFormKeluar",
      searchInputId: "searchInputKeluar",
      tableBodyId: "tableBodyKeluar",
      paginationContainerId: "paginationContainerKeluar",
      searchUrl: "/ajax-search-surat-keluar",
      updateTable: updateTableSuratKeluar,
      filterTahunId: "filterTahunKeluar",
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
      yearSelectId: "tahun_penomoran_masuk",
      suratType: "masuk",
      dateInputName: "tanggal_diterima",
      searchFormId: "searchFormMasuk",
      searchInputId: "searchInputMasuk",
      tableBodyId: "tableBodyMasuk",
      paginationContainerId: "paginationContainerMasuk",
      searchUrl: "/ajax-search-surat-masuk",
      updateTable: updateTableSuratMasuk,
      filterTahunId: "filterTahunMasuk",
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
      yearSelectId: "tahun_penomoran_keluar_dewan",
      suratType: "keluar-dewan",
      dateInputName: "tanggal_surat",
      searchFormId: "searchFormKeluarDewan",
      searchInputId: "searchInputKeluarDewan",
      tableBodyId: "tableBodyKeluarDewan",
      paginationContainerId: "paginationContainerKeluarDewan",
      searchUrl: "/ajax-search-surat-keluar-dewan",
      updateTable: updateTableSuratKeluarDewan,
      filterTahunId: "filterTahunKeluarDewan",
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
      yearSelectId: "tahun_penomoran_masuk_dewan",
      suratType: "masuk-dewan",
      dateInputName: "tanggal_diterima",
      searchFormId: "searchFormMasukDewan",
      searchInputId: "searchInputMasukDewan",
      tableBodyId: "tableBodyMasukDewan",
      paginationContainerId: "paginationContainerMasukDewan",
      searchUrl: "/ajax-search-surat-masuk-dewan",
      updateTable: updateTableSuratMasukDewan,
      filterTahunId: "filterTahunMasukDewan",
    });
  }

  if (document.getElementById("searchFormDisposisi")) {
    setupPageFunctionality({
      toggleBtnId: "toggle-form-disposisi-btn",
      formBodyId: "form-disposisi-body",
      listContainerId: "list-disposisi-container",
      localStorageKey: "formDisposisiMinimized",
      fileInputId: "file-upload-disposisi",
      fileNameId: "file-name-disposisi",
      checkBtnId: null,
      urutInputId: null,
      urutLabel: null,
      checkUrl: null,
      dateInputName: null,
      searchFormId: "searchFormDisposisi",
      searchInputId: "searchInputDisposisi",
      tableBodyId: "tableBodyDisposisi",
      paginationContainerId: "paginationContainerDisposisi",
      searchUrl: "/ajax-search-disposisi-sekwan",
      updateTable: updateTableDisposisi,
      filterTahunId: "filterTahunDisposisi",
    });
  }

  // Event delegation untuk tombol detail log
  document.body.addEventListener("click", function (e) {
    if (e.target.classList.contains("detail-log-btn")) {
      showLogDetailModal(e.target.dataset.detail);
    }
  });
});

// assets/js/modules/modal.js
import { getSuratMasukDetailHTML, getSuratKeluarDetailHTML } from "./ui.js";

// Ambil elemen-elemen modal sekali saja saat modul dimuat
const modal = document.getElementById("detail-modal");
const closeModalBtn = document.getElementById("close-modal-btn");
const modalContent = document.getElementById("detail-modal-content");
const modalBody = document.getElementById("modal-body-content");
const modalFooter = document.getElementById("modal-footer-content");

// Fungsi untuk membuka modal dengan animasi
const openModal = () => {
  if (!modal) return;
  modal.classList.remove("hidden");
  setTimeout(() => {
    modal.classList.remove("opacity-0");
    modalContent.classList.remove("scale-95");
  }, 10);
};

// Fungsi untuk menutup modal dengan animasi
const closeModal = () => {
  if (!modal) return;
  modal.classList.add("opacity-0");
  modalContent.classList.add("scale-95");
  setTimeout(() => {
    modal.classList.add("hidden");
    // Kosongkan footer agar tidak muncul di pembukaan modal berikutnya
    modalFooter.innerHTML = "";
  }, 300);
};

/**
 * Mengambil data detail dari server dan menampilkannya di dalam modal.
 * @param {string} url URL API untuk mengambil detail.
 * @param {string} type Tipe surat ('masuk', 'keluar', 'masuk-dewan', 'keluar-dewan').
 */
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

      if (type === "masuk" || type === "masuk-dewan") {
        contentHTML = getSuratMasukDetailHTML(data, lampiranLink);
        const cetakUrl =
          type === "masuk-dewan"
            ? `/cetak-disposisi-dewan?id=${data.id}`
            : `/cetak-disposisi?id=${data.id}`;
        footerHTML = `<a href="${cetakUrl}" target="_blank" class="inline-flex items-center text-white bg-green-500 hover:bg-green-600 px-4 py-2 rounded-lg text-sm"><i class="fas fa-print mr-2"></i> Cetak Disposisi</a>`;
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

/**
 * Menginisialisasi semua event listener yang berhubungan dengan modal.
 */
export function initModal() {
  if (modal) {
    closeModalBtn.addEventListener("click", closeModal);
    modal.addEventListener("click", (e) => {
      if (e.target === modal) closeModal();
    });
  }

  // Menggunakan event delegation untuk menangani klik pada semua link detail surat
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
}

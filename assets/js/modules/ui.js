// assets/js/modules/ui.js

/**
 * Membersihkan string dari karakter HTML berbahaya untuk mencegah XSS.
 * @param {string} str String yang akan dibersihkan.
 * @returns {string} String yang sudah aman.
 */
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

/**
 * Menampilkan dialog konfirmasi sebelum menghapus item.
 * @param {string} type Tipe surat ('masuk', 'keluar', dll).
 * @param {number} id ID surat yang akan dihapus.
 */
export function confirmDelete(type, id) {
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

/**
 * Memperbarui tampilan kontrol paginasi.
 * @param {object} pagination Objek data paginasi dari server.
 * @param {string} formId ID form pencarian terkait.
 */
export function updatePagination(pagination, formId) {
  const containerId = `paginationContainer${formId.replace("searchForm", "")}`;
  const container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = "";
  if (pagination.total_pages <= 1) return;

  let paginationHTML = `<div class="flex items-center justify-between"><div class="text-sm text-gray-600">Halaman ${pagination.current_page} dari ${pagination.total_pages}</div><div class="flex space-x-1">`;
  if (pagination.current_page > 1) {
    paginationHTML += `<button type="button" onclick="document.getElementById('${formId}').fetchData(${
      pagination.current_page - 1
    })" class="px-4 py-2 rounded-lg border text-sm bg-white hover:bg-gray-50 transition-colors">Sebelumnya</button>`;
  }
  if (pagination.current_page < pagination.total_pages) {
    paginationHTML += `<button type="button" onclick="document.getElementById('${formId}').fetchData(${
      pagination.current_page + 1
    })" class="px-4 py-2 rounded-lg border text-sm bg-white hover:bg-gray-50 transition-colors">Selanjutnya</button>`;
  }
  paginationHTML += "</div></div>";
  container.innerHTML = paginationHTML;
}

/**
 * Fungsi generik untuk me-render baris-baris tabel.
 * @param {HTMLElement} tableBody Elemen tbody dari tabel.
 * @param {Array} list Daftar data untuk ditampilkan.
 * @param {Function} rowRenderer Fungsi untuk membuat HTML satu baris.
 */
function renderTableRows(tableBody, list, rowRenderer) {
  if (!tableBody) return;
  if (list.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500"><p>Data tidak ditemukan.</p></td></tr>`;
  } else {
    tableBody.innerHTML = list.map(rowRenderer).join("");
  }
}

const isAdmin = document.body.dataset.userRole === "admin";

/**
 * Menghasilkan HTML untuk tombol aksi (edit/hapus).
 * @param {string} type Tipe surat.
 * @param {number} id ID surat.
 * @returns {string} HTML untuk kolom aksi.
 */
function getActionButtons(type, id) {
  if (!isAdmin) return "";
  return `
    <td class="px-6 py-4">
        <div class="flex space-x-2">
            <a href="/edit-surat-${type}?id=${id}" class="text-blue-500 hover:text-blue-700" title="Edit"><i class="fas fa-edit"></i></a>
            <button onclick="window.confirmDelete('${type}', ${id})" class="text-red-500 hover:text-red-700" title="Hapus"><i class="fas fa-trash"></i></button>
        </div>
    </td>`;
}

// --- FUNGSI UPDATE TABEL ---
export function updateTableSuratKeluar(suratList) {
  renderTableRows(
    document.getElementById("tableBodyKeluar"),
    suratList,
    getSuratKeluarRowHTML
  );
}
export function updateTableSuratKeluarDewan(suratList) {
  renderTableRows(
    document.getElementById("tableBodyKeluarDewan"),
    suratList,
    getSuratKeluarDewanRowHTML
  );
}
export function updateTableSuratMasuk(suratList) {
  renderTableRows(
    document.getElementById("tableBodyMasuk"),
    suratList,
    getSuratMasukRowHTML
  );
}
export function updateTableSuratMasukDewan(suratList) {
  renderTableRows(
    document.getElementById("tableBodyMasukDewan"),
    suratList,
    getSuratMasukDewanRowHTML
  );
}

// --- FUNGSI RENDER BARIS TABEL ---
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

// --- FUNGSI RENDER DETAIL SURAT ---
export function getSuratMasukDetailHTML(data, lampiranLink) {
  const diteruskanHtml = data.diteruskan_kepada
    ? `<div class="col-span-1 text-gray-500 self-start">Diteruskan Kepada</div><div class="col-span-2 text-gray-700 self-start">: ${escapeHTML(
        data.diteruskan_kepada
      )}</div>`
    : "";
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
        ${diteruskanHtml}
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

export function getSuratKeluarDetailHTML(data, lampiranLink) {
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

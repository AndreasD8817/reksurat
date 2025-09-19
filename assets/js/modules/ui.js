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
 * Menampilkan dialog konfirmasi sebelum menghapus atau membatalkan.
 * @param {string} type Tipe data ('masuk', 'keluar', 'disposisi-sekwan', dll).
 * @param {number} id ID item yang akan dihapus/dibatalkan.
 */
export function confirmDelete(type, id) {
  const isDisposisi = type.includes("disposisi");
  const title = isDisposisi ? "Batalkan Disposisi Ini?" : "Anda Yakin?";
  const text = isDisposisi
    ? "Surat ini akan kembali ke daftar surat yang belum didisposisi."
    : "Data surat ini akan dihapus secara permanen!";
  const confirmButtonText = isDisposisi ? "Ya, Batalkan!" : "Ya, Hapus!";

  Swal.fire({
    title: title,
    text: text,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: confirmButtonText,
    cancelButtonText: "Tidak",
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = `/hapus-${type}?id=${id}`;
    }
  });
}

/**
 * Memperbarui tampilan pagination di halaman.
 * @param {object} pagination Objek data pagination dari server.
 * @param {string} formId ID dari form pencarian.
 */
export function updatePagination(pagination, formId) {
  const formIdentifier = formId.replace("searchForm", "");
  const containerId = `paginationContainer${
    formIdentifier === "Log" ? "Log" : formIdentifier
  }`;
  const container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = "";
  if (!pagination || pagination.total_pages <= 1) return;

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

// --- FUNGSI RENDER TABEL ---
function renderTableRows(tableBody, list, rowRenderer) {
  if (!tableBody) return;
  if (!list || list.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500"><p>Data tidak ditemukan.</p></td></tr>`;
  } else {
    tableBody.innerHTML = list.map(rowRenderer).join("");
  }
}

const isAdmin = document.body.dataset.userRole === "admin";
function getActionButtons(type, id) {
  // Tombol Edit selalu ada untuk role yang berwenang (ditentukan di PHP)
  let editButton = `<a href="/edit-surat-${type}?id=${id}" class="text-blue-500 hover:text-blue-700" title="Edit"><i class="fas fa-edit"></i></a>`;

  // Tombol Hapus hanya untuk admin
  let deleteButton = isAdmin
    ? `<button onclick="window.confirmDelete('surat-${type}', ${id})" class="text-red-500 hover:text-red-700" title="Hapus"><i class="fas fa-trash-alt"></i></button>`
    : "";

  return `
    <td class="px-6 py-4">
        <div class="flex space-x-3">
            ${editButton}
            ${deleteButton}
        </div>
    </td>`;
}

// --- FUNGSI UPDATE & RENDER SPESIFIK UNTUK SETIAP HALAMAN ---

export function updateTableSuratKeluar(suratList) {
  renderTableRows(
    document.getElementById("tableBodyKeluar"),
    suratList,
    getSuratKeluarRowHTML
  );
}
function getSuratKeluarRowHTML(surat) {
  // Dapatkan tombol aksi terlebih dahulu
  const actionButtons = getActionButtons("keluar", surat.id);

  return `<tr class="hover:bg-blue-50 transition-colors duration-200">
        <td class="px-4 md:px-6 py-3 md:py-4 font-medium">
            <a href="#" class="text-primary hover:underline detail-link-keluar" data-id="${
              surat.id
            }">
                ${escapeHTML(surat.nomor_surat_lengkap)}
            </a>
            <div class="md:hidden text-sm text-gray-600 mt-1">
                <div>${escapeHTML(surat.tgl_formatted)}</div>
                <div class="truncate">${escapeHTML(surat.perihal)}</div>
                <div>${escapeHTML(surat.tujuan)}</div>
            </div>
        </td>
        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">${escapeHTML(
          surat.tgl_formatted
        )}</td>
        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">${escapeHTML(
          surat.perihal
        )}</td>
        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">${escapeHTML(
          surat.tujuan
        )}</td>
        ${actionButtons.replace(
          '<td class="px-6 py-4">',
          '<td class="px-4 md:px-6 py-3 md:py-4">'
        )}
    </tr>`;
}

export function updateTableSuratKeluarDewan(suratList) {
  renderTableRows(
    document.getElementById("tableBodyKeluarDewan"),
    suratList,
    getSuratKeluarDewanRowHTML
  );
}
function getSuratKeluarDewanRowHTML(surat) {
  const userRole = document.body.dataset.userRole;
  const canShowActions =
    userRole === "admin" || userRole === "staff surat keluar";
  let actionButtonsHTML = "";

  if (canShowActions) {
    const editButton = `<a href="/edit-surat-keluar-dewan?id=${surat.id}" class="text-blue-500 hover:text-blue-700" title="Edit"><i class="fas fa-edit"></i></a>`;
    const deleteButton =
      userRole === "admin"
        ? `<button onclick="window.confirmDelete('surat-keluar-dewan', ${surat.id})" class="text-red-500 hover:text-red-700" title="Hapus"><i class="fas fa-trash"></i></button>`
        : "";

    actionButtonsHTML = `
        <!-- Tombol Aksi KHUSUS MOBILE -->
        <div class="md:hidden absolute top-4 right-4 flex space-x-3">
            ${editButton} 
            ${deleteButton} 
        </div>
        <!-- Kolom Aksi KHUSUS DESKTOP -->
        <td class="px-4 md:px-6 py-3 md:py-4 hidden md:table-cell">
            <div class="flex space-x-2">
                ${editButton} 
                ${deleteButton}
            </div>
        </td>`;
  }

  return `<tr class="hover:bg-blue-50 transition-colors duration-200 relative">
        <td class="px-4 pr-12 md:px-6 md:pr-6 py-3 md:py-4 font-medium">
            ${
              // [PERBAIKAN] Mengambil hanya bagian mobile dari actionButtonsHTML
              canShowActions
                ? actionButtonsHTML.match(/<div class="md:hidden.*?<\/div>/s)[0]
                : ""
            }
            <!-- Konten Utama (Nomor Surat & Detail) -->
            <a href="#" class="text-primary hover:underline detail-link-keluar-dewan" data-id="${
              surat.id
            }">
                ${escapeHTML(surat.nomor_surat_lengkap)}
            </a>
            <!-- Detail surat yang hanya tampil di mobile -->
            <div class="md:hidden text-sm text-gray-600 mt-1">
                <div>${escapeHTML(surat.tgl_formatted)}</div> 
                <div class="truncate">${escapeHTML(surat.perihal)}</div>
                <div>${escapeHTML(surat.tujuan)}</div> 
            </div>
        </td>

        <!-- Kolom-kolom ini hanya akan muncul di DESKTOP -->
        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">${escapeHTML(
          surat.tgl_formatted
        )}</td>
        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">${escapeHTML(
          surat.perihal
        )}</td>
        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">${escapeHTML(
          surat.tujuan
        )}</td>
        ${
          // [PERBAIKAN] Mengambil hanya bagian desktop (kolom <td>) dari actionButtonsHTML
          canShowActions
            ? actionButtonsHTML.match(/<td class="px-4.*?<\/td>/s)[0]
            : ""
        }
    </tr>`;
}

export function updateTableSuratMasuk(suratList) {
  renderTableRows(
    document.getElementById("tableBodyMasuk"),
    suratList,
    getSuratMasukRowHTML
  );
}
function getSuratMasukRowHTML(surat) {
  // Dapatkan tombol aksi terlebih dahulu
  const isAdmin = document.body.dataset.userRole === "admin";
  let actionButtons;

  if (surat.disposisi_id) {
    // Surat sudah terdisposisi, tombol dinonaktifkan
    const disabledEdit = `<span class="text-gray-300 cursor-not-allowed" title="Tidak dapat diedit/dihapus karena sudah terdisposisi"><i class="fas fa-edit"></i></span>`;
    const disabledDelete = isAdmin
      ? `<span class="text-gray-300 cursor-not-allowed" title="Tidak dapat diedit/dihapus karena sudah terdisposisi"><i class="fas fa-trash"></i></span>`
      : "";
    actionButtons = `
      <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap">
        <div class="flex items-center space-x-2">
          <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700" title="Surat ini sudah didisposisi pada ID Disposisi: ${surat.disposisi_id}"><i class="fas fa-check-circle mr-1"></i> Terdisposisi</span>
          ${disabledEdit}
          ${disabledDelete}
        </div>
      </td>`;
  } else {
    // Surat belum terdisposisi, tombol aktif
    actionButtons = getActionButtons("masuk", surat.id).replace(
      '<td class="px-6 py-4">',
      '<td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap">'
    );
  }
  return `<tr class="hover:bg-blue-50 transition-colors duration-200">
        <td class="px-4 md:px-6 py-3 md:py-4 font-semibold">
            <a href="#" class="text-primary hover:underline detail-link" data-id="${
              surat.id
            }">
                ${escapeHTML(surat.nomor_agenda_lengkap)}
            </a>
            <div class="md:hidden text-sm text-gray-600 mt-1 space-y-1">
                <div>Dari: ${escapeHTML(surat.asal_surat)}</div>
                <div class="truncate">${escapeHTML(surat.perihal)}</div>
                <div>Diterima: ${escapeHTML(surat.tgl_terima_formatted)}</div>
            </div>
        </td>
        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">${escapeHTML(
          surat.asal_surat
        )}</td>
        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">${escapeHTML(
          surat.perihal
        )}</td>
        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">${escapeHTML(
          surat.tgl_terima_formatted
        )}</td>
        ${actionButtons}
    </tr>`;
}

export function updateTableSuratMasukDewan(suratList) {
  renderTableRows(
    document.getElementById("tableBodyMasukDewan"),
    suratList,
    getSuratMasukDewanRowHTML
  );
}
function getSuratMasukDewanRowHTML(surat) {
  const isAdmin = document.body.dataset.userRole === "admin";
  const editButton = `<a href="/edit-surat-masuk-dewan?id=${surat.id}" class="text-blue-500 hover:text-blue-700" title="Edit"><i class="fas fa-edit"></i></a>`;
  const deleteButton = isAdmin
    ? `<button onclick="window.confirmDelete('surat-masuk-dewan', ${surat.id})" class="text-red-500 hover:text-red-700" title="Hapus"><i class="fas fa-trash"></i></button>`
    : "";

  // [PERBAIKAN] Menyamakan struktur dengan file PHP
  return `<tr class="hover:bg-blue-50 transition-colors duration-200 relative">
        <td class="px-4 pr-12 md:px-6 py-3 md:py-4 font-semibold">
            
            <!-- Tombol Aksi KHUSUS MOBILE -->
            <div class="md:hidden absolute top-4 right-4 flex space-x-3">
                ${editButton}
                ${deleteButton}
            </div>

            <!-- Konten Utama (Nomor Agenda & Detail) -->
            <a href="#" class="text-primary hover:underline detail-link-masuk-dewan" data-id="${
              surat.id
            }">
                ${escapeHTML(surat.nomor_agenda_lengkap)}
            </a>
            <!-- Detail surat yang hanya tampil di mobile -->
            <div class="md:hidden text-sm text-gray-600 mt-1 space-y-1">
                <div>Dari: ${escapeHTML(surat.asal_surat)}</div>
                <div class="truncate">${escapeHTML(surat.perihal)}</div>
                <div>Diterima: ${escapeHTML(surat.tgl_terima_formatted)}</div>
            </div>
        </td>

        <!-- Kolom-kolom ini hanya akan muncul di DESKTOP -->
        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">${escapeHTML(
          surat.asal_surat
        )}</td>
        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">${escapeHTML(
          surat.perihal
        )}</td>
        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">${escapeHTML(
          surat.tgl_terima_formatted
        )}</td>

        <!-- Kolom Aksi KHUSUS DESKTOP -->
        <td class="px-4 md:px-6 py-3 md:py-4 hidden md:table-cell">
            <div class="flex space-x-2">
                ${editButton}
                ${deleteButton}
            </div>
        </td>
    </tr>`;
}

// **MODIFIKASI**: Memperbarui render baris tabel disposisi
export function updateTableDisposisi(disposisiList) {
  const tableBody = document.getElementById("tableBodyDisposisi");
  renderTableRows(tableBody, disposisiList, getDisposisiRowHTML);
}
function getDisposisiRowHTML(disposisi) {
  const isAdminOrStaff =
    document.body.dataset.userRole === "admin" ||
    document.body.dataset.userRole === "staff surat masuk";

  const noAgendaHtml = disposisi.file_lampiran
    ? `<a href="#" class="text-primary hover:underline pdf-modal-trigger" data-pdf-src="/uploads/${escapeHTML(
        disposisi.file_lampiran
      )}" data-agenda-no="${escapeHTML(disposisi.nomor_agenda_lengkap)}">
          ${escapeHTML(disposisi.nomor_agenda_lengkap)}
       </a>`
    : `<span class="text-gray-800">${escapeHTML(
        disposisi.nomor_agenda_lengkap
      )}</span>`;

  let actionButtons = "";
  if (isAdminOrStaff) {
    let editButton = `<a href="/edit-disposisi-sekwan?id=${disposisi.id}" class="text-blue-500 hover:text-blue-700" title="Edit Disposisi"><i class="fas fa-edit"></i></a>`;
    let deleteButton = isAdmin
      ? `<button onclick="window.confirmDelete('disposisi-sekwan', ${disposisi.id})" class="text-red-500 hover:text-red-700" title="Batalkan Disposisi"><i class="fas fa-trash-alt"></i></button>`
      : "";
    actionButtons = `
      <td class="px-4 md:px-6 py-3 md:py-4 text-sm font-medium">
          <div class="flex space-x-2">
              ${editButton}
              ${deleteButton}
          </div>
      </td>`;
  }

  return `<tr class="hover:bg-blue-50 transition-colors duration-200">
        <td class="px-4 md:px-6 py-3 md:py-4 font-medium">
            ${noAgendaHtml}
            <div class="md:hidden text-sm text-gray-600 mt-1">
                <div class="truncate">Perihal: ${escapeHTML(
                  disposisi.perihal
                )}</div>
                <div>Tujuan: ${escapeHTML(disposisi.nama_pegawai)}</div>
                <div>Tgl: ${escapeHTML(disposisi.tgl_disposisi_formatted)}</div>
            </div>
        </td>
        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">${escapeHTML(
          disposisi.perihal
        )}</td>
        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">${escapeHTML(
          disposisi.nama_pegawai
        )}</td>
        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell text-sm">${escapeHTML(
          disposisi.tgl_disposisi_formatted
        )}</td>
        ${actionButtons}
    </tr>`;
}

export function updateTableLog(logs) {
  const tableBody = document.getElementById("tableBodyLog");
  if (!tableBody) return;
  if (!logs || logs.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500"><p>Data log tidak ditemukan.</p></td></tr>`;
  } else {
    let rowNumber =
      (document.getElementById("searchFormLog").currentPage - 1) * 15 + 1;
    tableBody.innerHTML = logs
      .map((log) => getLogRowHTML(log, rowNumber++))
      .join("");
  }
}

function getLogRowHTML(log, no) {
  const detailButton = log.detail
    ? `<button class="detail-log-btn text-primary hover:underline text-sm" data-detail='${escapeHTML(
        log.detail
      )}'>Lihat</button>`
    : "";

  return `<tr class="hover:bg-blue-50">
      <td class="px-6 py-4 font-medium text-gray-500">${no}</td>
      <td class="px-6 py-4 font-semibold text-gray-800">${escapeHTML(
        log.user_nama
      )}</td>
      <td class="px-6 py-4 text-gray-600">${escapeHTML(log.kegiatan)}</td>
      <td class="px-6 py-4 text-center">${detailButton}</td>
      <td class="px-6 py-4 text-gray-600">${escapeHTML(log.tanggal)}</td>
      <td class="px-6 py-4 text-gray-600">${escapeHTML(log.jam)}</td>
    </tr>`;
}

export function showLogDetailModal(detailJson) {
  try {
    const modal = document.getElementById("detail-modal");
    const modalContent = document.getElementById("detail-modal-content");
    if (!modal || !modalContent) {
      console.error("Elemen modal detail tidak ditemukan!");
      return;
    }

    const data = JSON.parse(detailJson);
    const { sebelum, sesudah, perubahan } = data;

    let contentHTML = "";
    let modalTitle = "";

    // Case 1: Edit action (has 'perubahan')
    if (perubahan) {
      modalTitle =
        '<i class="fas fa-exchange-alt text-primary mr-3"></i> Detail Perubahan Data';
      contentHTML =
        '<div class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-2 text-sm">';
      contentHTML += `<div class="md:col-span-1 font-bold text-gray-500 border-b pb-2">Kolom</div>`;
      contentHTML += `<div class="md:col-span-1 font-bold text-gray-500 border-b pb-2">Sebelum</div>`;
      contentHTML += `<div class="md:col-span-1 font-bold text-gray-500 border-b pb-2">Sesudah</div>`;

      if (Object.keys(perubahan).length === 0) {
        contentHTML += `<div class="col-span-3 text-center py-4 text-gray-500">Tidak ada perubahan data yang terdeteksi. Mungkin hanya file yang diubah.</div>`;
      } else {
        for (const key in perubahan) {
          const valSebelum = escapeHTML(sebelum[key] || "-");
          const valSesudah = escapeHTML(sesudah[key] || "-");
          contentHTML += `
                  <div class="md:col-span-1 font-semibold text-gray-800 py-2 border-b">${escapeHTML(
                    key
                  )}</div>
                  <div class="md:col-span-1 text-gray-600 py-2 border-b break-words">${valSebelum}</div>
                  <div class="md:col-span-1 text-green-600 font-medium py-2 border-b break-words">${valSesudah}</div>
                `;
        }
      }
      contentHTML += "</div>";
    }
    // Case 2: Add action (only 'sesudah' exists)
    else if (sesudah) {
      modalTitle =
        '<i class="fas fa-plus-circle text-green-500 mr-3"></i> Detail Data Ditambahkan';
      contentHTML =
        '<div class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-2 text-sm">';
      contentHTML += `<div class="md:col-span-1 font-bold text-gray-500 border-b pb-2">Kolom</div>`;
      contentHTML += `<div class="md:col-span-2 font-bold text-gray-500 border-b pb-2">Nilai</div>`;

      for (const key in sesudah) {
        const value = escapeHTML(sesudah[key] || "-");
        if (value === "-" || value === "") continue; // Skip empty values
        contentHTML += `<div class="md:col-span-1 font-semibold text-gray-800 py-2 border-b">${escapeHTML(
          key
        )}</div><div class="md:col-span-2 text-gray-700 py-2 border-b break-words">${value}</div>`;
      }
      contentHTML += "</div>";
    }
    // Case 3: Delete action (only 'sebelum' exists)
    else if (sebelum) {
      modalTitle =
        '<i class="fas fa-trash-alt text-red-500 mr-3"></i> Detail Data Dihapus';
      contentHTML =
        '<div class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-2 text-sm">';
      contentHTML += `<div class="md:col-span-1 font-bold text-gray-500 border-b pb-2">Kolom</div>`;
      contentHTML += `<div class="md:col-span-2 font-bold text-gray-500 border-b pb-2">Nilai</div>`;

      for (const key in sebelum) {
        const value = escapeHTML(sebelum[key] || "-");
        if (value === "-" || value === "") continue; // Skip empty values
        contentHTML += `<div class="md:col-span-1 font-semibold text-gray-800 py-2 border-b">${escapeHTML(
          key
        )}</div><div class="md:col-span-2 text-gray-700 py-2 border-b break-words">${value}</div>`;
      }
      contentHTML += "</div>";
    } else {
      modalTitle =
        '<i class="fas fa-info-circle text-gray-500 mr-3"></i> Detail Log';
      contentHTML =
        '<div class="text-center py-4 text-gray-500">Tidak ada detail untuk ditampilkan.</div>';
    }

    document.querySelector("#detail-modal h3").innerHTML = modalTitle;
    document.getElementById("modal-body-content").innerHTML = contentHTML;
    document.getElementById("modal-footer-content").innerHTML = ""; // Kosongkan footer

    // Tampilkan modal secara langsung tanpa animasi yang berkonflik
    modal.classList.remove("hidden", "opacity-0");
    modalContent.classList.remove("scale-95");
    // Pastikan class-class ini ada untuk reset
    modal.classList.add("opacity-100");
    modalContent.classList.add("scale-100");
  } catch (e) {
    console.error("Gagal parsing detail log:", e);
  }
}

// --- FUNGSI RENDER DETAIL MODAL (Tidak ada perubahan) ---
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

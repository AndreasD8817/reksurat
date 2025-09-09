<?php
// pages/partials/surat_keluar_rows.php
if (empty($surat_keluar_list)) {
    echo '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i><p class="text-lg">Data tidak ditemukan.</p></td></tr>';
} else {
    foreach ($surat_keluar_list as $surat) {
        echo '<tr class="hover:bg-gray-50">';
        echo '<td class="px-6 py-4 whitespace-nowrap font-medium text-primary">' . htmlspecialchars($surat['nomor_surat_lengkap']) . '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap">' . htmlspecialchars($surat['tgl_formatted']) . '</td>';
        echo '<td class="px-6 py-4">' . htmlspecialchars($surat['perihal']) . '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap">' . htmlspecialchars($surat['tujuan']) . '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap">' . htmlspecialchars($surat['konseptor']) . '</td>';
        echo '</tr>';
    }
}
?>
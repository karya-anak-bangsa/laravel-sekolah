<?php

namespace App\Support;

use RuntimeException;

/**
 * Aksi bisnis yang tidak boleh dilanjutkan (mis. menghapus data yang masih dipakai).
 * Pesannya ditampilkan ke pengguna sebagai notifikasi error pada halaman sebelumnya.
 */
class AksiDitolak extends RuntimeException {}

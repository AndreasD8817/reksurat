<?php
// config/secrets.php

// Kunci rahasia untuk hashing kode aktivasi tahunan.
// GANTI DENGAN STRING YANG SANGAT PANJANG DAN ACAK!
define('ACTIVATION_SECRET_SALT', 'T;D:7*.f-;`K:TLnMa5.W!k{g"aK(g),}p"vBK]$!>xcw9C~np$-~4oT}<x0)Y.e');

// Nama cookie untuk mengingat aktivasi di seluruh sesi.
define('ACTIVATION_COOKIE_NAME', 'app_activation_token');

// Kunci rahasia tambahan (salt) khusus untuk cookie aktivasi.
define('ACTIVATION_COOKIE_SALT', 'v(n;s]L4_z.2-!FpG\'C@*r`b+8K!Y#');
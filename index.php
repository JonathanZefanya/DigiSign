<?php

/**
 * Laravel - Subfolder Proxy
 * 
 * File ini meng-redirect semua request ke public/index.php
 * Digunakan saat deploy Laravel di subfolder shared hosting
 */

// Redirect ke public/index.php
require __DIR__.'/public/index.php';

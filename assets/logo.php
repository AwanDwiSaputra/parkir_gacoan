<?php
/**
 * assets/logo.php
 * Logo SVG orisinal "Parkir Gacoan" (lencana biru tua + emas)
 * Include file ini lalu panggil gacoanLogo($ukuranPx)
 */
function gacoanLogo($size = 44)
{
    return '<svg width="' . (int)$size . '" height="' . (int)$size . '" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
        <circle cx="24" cy="24" r="22" fill="#0b1f4d" stroke="#f4b400" stroke-width="2.5"/>
        <path d="M24 10.5c-1.6 2.1-2.7 3.8-2.7 5.7 0 1 .6 1.6 1.3 1 .5-.4.6-1.2.3-2 1.7 1.7 2.7 3.8 2.7 5.9a5.9 5.9 0 01-11.8 0c0-4.6 3.8-8.4 10.2-10.6z" fill="#f4b400"/>
        <path d="M14.5 27a9.5 9.5 0 0019 0" stroke="#f4b400" stroke-width="2.3" fill="none" stroke-linecap="round"/>
        <text x="24" y="41.5" text-anchor="middle" font-family="Arial, sans-serif" font-weight="800" font-size="8.5" fill="#f4b400">PARKIR</text>
    </svg>';
}

<?php
$path = __DIR__ . '/debug-error.txt';

if ( file_exists( $path ) ) {
    echo '<pre style="background:#fff;padding:20px;font-size:11px;word-wrap:break-word;">';
    echo htmlspecialchars( file_get_contents( $path ) );
    echo '</pre>';
} else {
    echo 'debug-error.txt not found at: ' . $path;
}
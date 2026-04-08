<?php
// Temporary debug file - delete after use
$log = ABSPATH . 'wp-content/debug.log';
if ( file_exists( $log ) ) {
    echo '<pre style="background:#fff;padding:20px;font-size:12px;">';
    echo htmlspecialchars( file_get_contents( $log ) );
    echo '</pre>';
} else {
    echo 'No debug.log found at: ' . $log;
}
<?php
$log = dirname( __FILE__ ) . '/debug-error.txt';
if ( file_exists( $log ) ) {
    echo '<pre style="background:#fff;padding:20px;font-size:12px;">';
    echo htmlspecialchars( file_get_contents( $log ) );
    echo '</pre>';
} else {
    echo 'No debug-error.txt found. Error may not have been caught yet.';
}
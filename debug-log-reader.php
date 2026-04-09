<?php
// Try wp-content/debug.log
$paths = [
    dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/debug.log',
    dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-content/debug.log',
];

foreach ( $paths as $path ) {
    if ( file_exists( $path ) ) {
        echo '<b>Found at: ' . $path . '</b><br><br>';
        echo '<pre style="background:#fff;padding:20px;font-size:11px;word-wrap:break-word;">';
        // Show last 200 lines only
        $lines = file( $path );
        $last  = array_slice( $lines, -200 );
        echo htmlspecialchars( implode( '', $last ) );
        echo '</pre>';
        exit;
    }
}

echo 'debug.log not found. WP_DEBUG_LOG may not be enabled.';
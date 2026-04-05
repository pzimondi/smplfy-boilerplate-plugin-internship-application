<?php

namespace SMPLFY\boilerplate;

class ESignatureAdapter {

    private ESignatureNotifications $eSignatureNotifications;

    public function __construct( ESignatureNotifications $eSignatureNotifications ) {
        $this->eSignatureNotifications = $eSignatureNotifications;

        $this->register_hooks();
    }

    private function register_hooks(): void {
        add_action(
            'esig_signature_saved',
            [ $this->eSignatureNotifications, 'handle_signature_saved' ],
            10,
            1
        );
    }
}
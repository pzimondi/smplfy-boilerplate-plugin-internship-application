<?php

namespace SMPLFY\boilerplate;

class ESignatureAdapter {

    private ESignatureNotificationsUsecase $eSignatureNotificationsUsecase;

    public function __construct( ESignatureNotificationsUsecase $eSignatureNotificationsUsecase ) {
        $this->eSignatureNotificationsUsecase = $eSignatureNotificationsUsecase;

        $this->register_hooks();
    }

    private function register_hooks(): void {
        add_action(
            'esig_signature_saved',
            [ $this->eSignatureNotificationsUsecase, 'handle_signature_saved' ],
            10,
            1
        );
    }
}
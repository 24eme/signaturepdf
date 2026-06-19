[globals]

; Path to which stored pdf to activate the mode of sharing a signature to several.
; To deactivate this mode, simply do not configure it or leave it empty
PDF_STORAGE_PATH=${PDF_STORAGE_PATH}

; Disable organization tab and routes
DISABLE_ORGANIZATION=${DISABLE_ORGANIZATION}

; Manage demo link pdf : true (by default, show), false (hide), or custom link
PDF_DEMO_LINK=${PDF_DEMO_LINK}

; Encryption activation (default activation if GPG is installed)
PDF_STORAGE_ENCRYPTION=${PDF_STORAGE_ENCRYPTION}

;NSS3 configuration (used to sign pdf with pdfsig)
NSS3_DIRECTORY=${NSS3_DIRECTORY}
NSS3_PASSWORD=${NSS3_PASSWORD}
NSS3_NICK=${NSS3_NICK}

; Authorize these IP to use debug mode (separate IP adresses with space ' ')
ADMIN_AUTHORIZED_IP=${ADMIN_AUTHORIZED_IP}

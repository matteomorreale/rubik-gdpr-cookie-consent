
jQuery(document).ready(function($) {
    // Handle consent actions
    $(document).on(
        'click',
        '#manus-gdpr-accept-button, #manus-gdpr-reject-button, #manus-gdpr-manage-button',
        function(e) {
            e.preventDefault();

            var consentStatus = $(this).data('consent-status');
            var consentData = {}; // This will be populated with granular consent later

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'manus_gdpr_consent_action',
                    manus_gdpr_consent_nonce: manus_gdpr_ajax.nonce,
                    consent_status: consentStatus,
                    consent_data: JSON.stringify(consentData)
                },
                success: function(response) {
                    if (response.success) {
                        $('#manus-gdpr-cookie-banner').fadeOut();
                        location.reload(); // Reload page to apply consent changes
                    } else {
                        console.error('Error:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }
    );
});



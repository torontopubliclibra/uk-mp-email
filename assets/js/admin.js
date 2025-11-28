// Admin JavaScript for UK MP Email Plugin

(function($) {
    'use strict';

    $(document).ready(function() {
        initAdminFeatures();
    });

    function initAdminFeatures() {
        // Test API connection
        $('#test-api-connection').on('click', function(e) {
            e.preventDefault();
            testApiConnection();
        });

        // Clear cache
        $('#clear-cache').on('click', function(e) {
            e.preventDefault();
            clearCache();
        });

        // Form validation
        $('form').on('submit', function() {
            return validateSettings();
        });
    }

    function testApiConnection() {
        const $button = $('#test-api-connection');
        const $status = $('#connection-status');
        
        // Disable button and show loading
        $button.prop('disabled', true);
        $status.removeClass('success error').addClass('loading')
               .text(ukmpApiAjax.strings.testing);

        $.ajax({
            url: ukmpApiAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'ukmp_email_test_connection',
                nonce: ukmpApiAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $status.removeClass('loading error').addClass('success')
                           .text(ukmpApiAjax.strings.connection_success);
                } else {
                    $status.removeClass('loading success').addClass('error')
                           .text(ukmpApiAjax.strings.connection_failed + ' ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                $status.removeClass('loading success').addClass('error')
                       .text(ukmpApiAjax.strings.connection_failed + ' ' + error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    }

    function clearCache() {
        const $button = $('#clear-cache');
        const $status = $('#cache-status');
        
        // Disable button and show loading
        $button.prop('disabled', true);
        $status.removeClass('success error').addClass('loading')
               .text(ukmpApiAjax.strings.clearing_cache);

        $.ajax({
            url: ukmpApiAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'ukmp_email_clear_cache',
                nonce: ukmpApiAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $status.removeClass('loading error').addClass('success')
                           .text(ukmpApiAjax.strings.cache_cleared);
                } else {
                    $status.removeClass('loading success').addClass('error')
                           .text(ukmpApiAjax.strings.cache_clear_failed + ' ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                $status.removeClass('loading success').addClass('error')
                       .text(ukmpApiAjax.strings.cache_clear_failed + ' ' + error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    }

    function validateSettings() {
        let isValid = true;
        const $emailTemplate = $('textarea[name="ukmp_email_settings[email_template]"]');
        
        // Validate email template
        if ($emailTemplate.length && !$emailTemplate.val().trim()) {
            alert('Email template is required.');
            $emailTemplate.focus();
            isValid = false;
        }
        
        // Validate cache duration
        const $cacheDuration = $('input[name="ukmp_email_settings[cache_duration]"]');
        if ($cacheDuration.length) {
            const duration = parseInt($cacheDuration.val());
            if (isNaN(duration) || duration < 60 || duration > 86400) {
                alert('Cache duration must be between 60 and 86400 seconds.');
                $cacheDuration.focus();
                isValid = false;
            }
        }
        
        return isValid;
    }

})(jQuery);
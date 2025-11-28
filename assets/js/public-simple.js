// Simplified JavaScript for UK MP Email Search Plugin

(function($) {
    'use strict';

    $(document).ready(function() {
        // Search form submission
        $('#ukmp-email-search-form').on('submit', function(e) {
            e.preventDefault();
            performSearch();
        });
        
        // Postcode formatting
        $('#ukmp-email-search-input').on('input', function() {
            formatPostcode(this);
        });
    });
    
    function formatPostcode(input) {
        let value = input.value.replace(/\s+/g, '').toUpperCase(); // Remove spaces and convert to uppercase
        
        // Apply UK postcode formatting (add space before last 3 characters)
        if (value.length > 3) {
            let outward = value.slice(0, -3);
            let inward = value.slice(-3);
            value = outward + ' ' + inward;
        }
        
        input.value = value;
    }

    function performSearch() {
        const $form = $('#ukmp-email-search-form');
        const $button = $('#ukmp-email-search-button');
        const $loading = $('#ukmp-email-search-loading');
        const $results = $('#ukmp-email-results');
        const searchQuery = $('#ukmp-email-search-input').val().trim();
        const userName = $('#ukmp-email-name-input').val().trim();
        const userEmail = $('#ukmp-email-user-email-input').val().trim();

        // Validate UK postcode
        if (!searchQuery) {
            alert('Please enter a UK postcode.');
            $('#ukmp-email-search-input').focus();
            return;
        }

        // Show loading state
        $button.prop('disabled', true).text('Searching...');
        $loading.show();
        $results.hide();

        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'ukmp_email_search');
        formData.append('nonce', ukmpemail.nonce);
        formData.append('search_query', searchQuery);
        formData.append('user_name', userName);
        formData.append('user_email', userEmail);

        $.ajax({
            url: ukmpemail.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    displayResults(response.data);
                } else {
                    showError(response.data || 'Search failed. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                showError('Connection error. Please check your internet connection and try again.');
            },
            complete: function() {
                $loading.hide();
                // Don't re-enable button if search was successful
                if (!$results.is(':visible') || $results.find('.results-content').html().indexOf('No MP found') !== -1) {
                    $button.prop('disabled', false).text('Find My MP');
                }
            }
        });
    }

    function displayResults(data) {
        const $results = $('#ukmp-email-results');
        const $content = $results.find('.results-content');
        const $button = $('#ukmp-email-search-button');

        // Show results container
        $results.show();

        // Update content with MP data
        if (data.mp && data.mp.name) {
            $content.html(data.html);
            
            // Disable the search button after successful search
            $button.prop('disabled', true).text('MP Found');
            
            // Set up email body textarea event listener
            setupEmailBodyEditor();
        } else {
            $content.html('<div style="padding: 20px; text-align: center; color: #666; background: #f9f9f9; border: 1px dashed #ddd; border-radius: 5px;">No MP found for this postcode. Please check your postcode and try again.</div>');
        }

        // Improved scroll positioning to account for styling updates
        setTimeout(function() {
            const resultsTop = $results.offset().top;
            // Account for sticky header: 90px on desktop, 80px on mobile
            const headerHeight = window.innerWidth > 768 ? 90 : 80;
            $('html, body').animate({
                scrollTop: resultsTop - headerHeight
            }, 500);
        }, 100);
    }

    function setupEmailBodyEditor() {
        const $textarea = $('.ukmp-email-body-preview');
        const $subjectInput = $('.ukmp-email-subject-preview');
        const $emailButton = $('.contact-mp-link');
        
        if ($textarea.length && $emailButton.length) {
            $textarea.on('input', function() {
                updateEmailURL();
            });
            
            // Add subject input event listener
            if ($subjectInput.length) {
                $subjectInput.on('input', function() {
                    updateEmailURL();
                });
            }
            
            // Add click handler to ensure email opens in new tab
            $emailButton.on('click', function(e) {
                e.preventDefault();
                const emailUrl = $(this).attr('href');
                window.open(emailUrl, '_blank');
            });
        }
    }

    function updateEmailURL() {
        const $textarea = $('.ukmp-email-body-preview');
        const $subjectInput = $('.ukmp-email-subject-preview');
        const $emailButton = $('.contact-mp-link');
        
        if ($textarea.length && $emailButton.length) {
            const mpEmail = $textarea.data('mp-email');
            const emailBody = $textarea.val();
            
            // Get subject from input field instead of preview text
            let subject = '';
            if ($subjectInput.length) {
                subject = $subjectInput.val().trim();
            }
            
            // Create new mailto URL
            const mailto = 'mailto:' + encodeURIComponent(mpEmail) + 
                          '?subject=' + encodeURIComponent(subject) + 
                          '&body=' + encodeURIComponent(emailBody);
            
            $emailButton.attr('href', mailto);
            // Ensure the button always opens in a new tab
            $emailButton.attr('target', '_blank');
        }
    }

    function showError(message) {
        const $results = $('#ukmp-email-results');
        const $content = $results.find('.results-content');
        
        $results.show();
        $content.html('<div style="padding: 15px; background: #ffebe8; border: 1px solid #dc3232; border-radius: 5px; color: #721c24; margin: 15px 0;">' + message + '</div>');
    }

})(jQuery);
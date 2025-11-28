// Public JavaScript for UK MP Email Plugin

(function($) {
    'use strict';

    let isSearching = false;

    $(document).ready(function() {
        initPublicFeatures();
    });

    function initPublicFeatures() {
        // Search form submission
        $('#ukmp-email-search-form').on('submit', function(e) {
            e.preventDefault();
            performSearch();
        });

        // Real-time postcode validation and formatting
        $('#ukmp-email-search-input').on('input', function() {
            let value = $(this).val().toUpperCase().replace(/[^A-Z0-9]/g, '');
            
            // Format UK postcode (add space if needed)
            if (value.length > 3) {
                let firstPart = value.substring(0, value.length - 3);
                let lastPart = value.substring(value.length - 3);
                value = firstPart + ' ' + lastPart;
            }
            
            $(this).val(value);
            
            // Real-time validation
            const isValid = validateUKPostcode(value);
            $(this).toggleClass('invalid', !isValid && value.length > 0);
        });
    }
    
    function validateUKPostcode(postcode) {
        const ukPostcodeRegex = /^[A-Z]{1,2}[0-9R][0-9A-Z]?\s*[0-9][ABD-HJLNP-UW-Z]{2}$/i;
        return ukPostcodeRegex.test(postcode.replace(/\s+/g, ''));
    }

    function performSearch() {
        if (isSearching) {
            return;
        }

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
        
        if (!validateUKPostcode(searchQuery)) {
            alert('Please enter a valid UK postcode (e.g. SW1A 1AA).');
            $('#ukmp-email-search-input').focus();
            return;
        }

        // Set searching state
        isSearching = true;
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
            error: function() {
                showError('Connection error. Please check your internet connection and try again.');
            },
            complete: function() {
                isSearching = false;
                $loading.hide();
                if (!$results.is(':visible') || $results.find('.results-content').html().indexOf('No MP found') !== -1) {
                    $button.prop('disabled', false).text('Find My MP');
                }
            }
        });
    }

    function displayResults(data) {
        const $results = $('#ukmp-email-results');
        const $content = $results.find('.results-content');

        // Show results container
        $results.show();

        // Update content with MP data
        if (data.mp && data.mp.name) {
            $content.html(data.html);
            // Disable the search button after successful search
            $('#ukmp-email-search-button').prop('disabled', true).text('MP Found');
            setupEmailBodyEditor();
        } else {
            $content.html('<div class="ukmp-email-empty">No MP found for this postcode. Please check your postcode and try again.</div>');
        }

        // Scroll to results
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
            $textarea.on('input', updateEmailURL);
            
            if ($subjectInput.length) {
                $subjectInput.on('input', updateEmailURL);
            }
            
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
            
            let subject = '';
            if ($subjectInput.length) {
                subject = $subjectInput.val().trim();
            }
            
            const mailto = 'mailto:' + encodeURIComponent(mpEmail) + 
                          '?subject=' + encodeURIComponent(subject) + 
                          '&body=' + encodeURIComponent(emailBody);
            
            $emailButton.attr('href', mailto).attr('target', '_blank');
        }
    }

    function showError(message) {
        const $results = $('#ukmp-email-results');
        const $content = $results.find('.results-content');
        
        $results.show();
        $content.html('<div class="ukmp-email-error">' + escapeHtml(message) + '</div>');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

})(jQuery);
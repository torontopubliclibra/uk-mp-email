# UK MP Email Search Plugin

## Overview

The UK MP Email Search plugin is a WordPress plugin that integrates with the [UK Parliament Members API](https://members-api.parliament.uk/index.html). It allows users to find their MP by entering a postcode, and provides a templated email system for contacting their MP directly.

## Installation

### Standard WordPress Installation

1. **Download/Upload**: Place the plugin files in `/wp-content/plugins/uk-mp-email/`
2. **Activate**: Go to Plugins → Installed Plugins and activate "UK MP Email Search"
3. **Configure**: Navigate to Settings → UK MP Email Settings
4. **Deploy**: Add shortcodes to your pages

### Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher
- **Internet Connection**: Required for UK Parliament API access

## Quick Start

### 1. Basic Implementation
Add these shortcodes to any page or post:

```html
[ukmp_email_search]
[ukmp_email_results]
```

### 2. Test the Plugin
Use the test shortcode to verify installation:

```html
[ukmp_email_test]
```

### 3. Try It Out
Visit your page and test with a UK postcode like "SW1A 1AA" (Houses of Parliament).

## Configuration

### Email Template Settings

Navigate to **Settings → UK MP Email Settings** to configure:

#### Email Template
Customize the message body using these placeholders:
- `{MP_NAME}` - MP's full name and title (e.g., "Mr John Smith MP")
- `{POSTCODE}` - User's postcode (formatted)
- `{MP_EMAIL}` - MP's email address
- `{LOCATION}` - MP's constituency
- `[Your name]` - User's entered name
- `[Your email]` - User's entered email address

#### Email Subject
Configure the subject line using the same placeholders.

#### Default Template
```
Dear {MP_NAME},

I am writing to you, my Member of Parliament, as a constituent of {LOCATION}.

[Your message here]

I would appreciate your response on this matter.

Sincerely,
[Your name]
[Your email]
```

### Cache Settings
- **Enable Cache**: Improves performance (recommended: enabled)
- **Cache Duration**: How long to store API responses (default: 3600 seconds/1 hour)
- **Clear Cache**: Admin tool for clearing cached data

### Admin Tools
- **Test API Connection**: Verify connectivity to UK Parliament API
- **Clear Cache**: Remove all cached API responses

## Shortcode Reference

### Primary Shortcodes

#### `[ukmp_email_search]`
Creates the main search form where users enter their details.

**Basic Usage:**
```html
[ukmp_email_search]
```

**Advanced Usage:**
```html
[ukmp_email_search 
    button_text="Find My MP"
    name_placeholder="Enter your full name"
    email_placeholder="Your email address"
    postcode_placeholder="UK postcode (e.g. SW1A 1AA)"]
```

**Parameters:**
- `button_text` - Text for the search button (default: "Find My MP")
- `name_placeholder` - Placeholder for name input field
- `email_placeholder` - Placeholder for email input field
- `postcode_placeholder` - Placeholder for postcode input field

#### `[ukmp_email_results]`
Creates the container where MP information and email preview are displayed.

```html
[ukmp_email_results]
```

#### `[ukmp_email_test]`
Simple status check to verify plugin is working.

```html
[ukmp_email_test]
```

## API Integration

### UK Parliament Members API

The plugin integrates with the official UK Parliament Members API:

**Endpoints Used:**
1. **Search**: `https://members-api.parliament.uk/api/Members/Search`
   - Purpose: Find MP by postcode location
   - Parameters: `Location` (postcode), `skip`, `take`
   
2. **Contact**: `https://members-api.parliament.uk/api/Members/{MP_ID}/Contact`
   - Purpose: Retrieve MP contact information including email

**No Authentication Required:** The Parliament API is publicly accessible

### Data Processing

**Postcode Handling:**
- Accepts postcodes with or without spaces
- Validates UK postcode format using regex
- Removes spaces for API calls
- Displays formatted version to users

**Data Extraction:**
- MP Name: `data.items[0].value.nameFullTitle`
- MP Email: `data.value[0].email`
- Constituency: `data.items[0].value.latestHouseMembership.membershipFromName`
- MP ID: `data.items[0].value.id`

## Development

### Hooks and Filters

**Available Actions:**
```php
// Plugin initialization
do_action('ukmp_email_plugin_loaded');

// Before API request
do_action('ukmp_email_before_api_request', $endpoint, $params);

// After successful search
do_action('ukmp_email_search_success', $mp_data, $postcode);
```

**Available Filters:**
```php
// Modify API request arguments
apply_filters('ukmp_email_api_request_args', $args, $endpoint);

// Customize email template processing
apply_filters('ukmp_email_template_content', $content, $mp_data);

// Modify search results
apply_filters('ukmp_email_search_results', $results, $postcode);
```

## Customization

### CSS Styling

Target these classes for custom styling:

```css
/* Main containers */
.ukmp-email-search-form { /* Search form wrapper */ }
.ukmp-email-results { /* Results container */ }
.ukmp-email-mp-profile { /* MP profile display */ }

/* Form elements */
.ukmp-email-search-form input[type="text"],
.ukmp-email-search-form input[type="email"] { /* Input fields */ }
.ukmp-email-search-form button { /* Search button */ }

/* MP information */
.mp-name { /* MP name styling */ }
.mp-details { /* MP details container */ }
.detail-item { /* Individual detail items */ }

/* Email preview */
.ukmp-email-preview { /* Email preview container */ }
.ukmp-email-subject-preview { /* Subject line preview */ }
.ukmp-email-body-preview { /* Email body preview */ }

/* Action buttons */
.contact-mp-link { /* Send email button */ }

/* States */
.ukmp-email-loading { /* Loading indicator */ }
.ukmp-email-error { /* Error messages */ }
```

The plugin is also setup to integrate with the [Not A Phase](https://notaphase.org) website's dark theme, using the parent class `.body--dark`.

### JavaScript Customization

The plugin provides JavaScript events for custom functionality:

```javascript
// Listen for successful MP search
document.addEventListener('ukmp_email_search_complete', function(event) {
    const mpData = event.detail;
    // Custom functionality
});

// Listen for email preview updates
document.addEventListener('ukmp_email_preview_updated', function(event) {
    const emailContent = event.detail;
    // Custom functionality
});
```

## Troubleshooting

### Potential Issues

**1. Plugin Not Visible/Working**
- Verify plugin is activated in Plugins → Installed Plugins
- Check for PHP errors in debug.log
- Test with `[ukmp_email_test]` shortcode

**2. Search Not Working**
- Verify internet connectivity
- Test with known working postcodes
- Check browser console for JavaScript errors
- Verify UK Parliament API availability

**3. No Results Found**
- Ensure postcode is valid UK format
- Some postcodes may not have MPs (e.g., non-residential areas)
- Check API response in browser developer tools

**4. Email Preview Not Showing**
- Verify email template is configured in admin settings
- Check that MP has email address in Parliament database
- Clear plugin cache if enabled

### Debug Mode

Enable WordPress debug mode for detailed error information:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Support

For technical support:
1. Check this documentation
2. Review debug logs
3. Test with minimal plugins/default theme
4. Contact plugin developer at [dana.r.teagle@gmail.com](mailto:dana.r.teagle@gmail.com)

## Security

### Security Measures

**Input Validation:**
- All user inputs sanitized using WordPress functions
- UK postcode format validation
- Email address validation

**API Security:**
- No sensitive data stored or transmitted
- Uses official UK Parliament public API
- Rate limiting compliance

**WordPress Security:**
- Nonce verification for all AJAX requests
- Capability checks for admin functions
- Output escaping for all displayed content
- SQL injection prevention through WordPress APIs

### Data Privacy

**Data Collection:**
- Only collects data temporarily for API requests
- No personal data stored in database
- No tracking or analytics

**Third-Party Data:**
- MP information sourced from UK Parliament API
- Data accuracy depends on Parliament's database
- No data shared with third parties

## Licensing and Attribution

### License

This plugin is licensed under the **GNU General Public License v2 or later**.

```
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

Full license text: https://www.gnu.org/licenses/gpl-2.0.html

### Authorship

This plugin was originally developed by [Dana Rosamund Teagle](https://danateagle.com) for [Not A Phase](https://notaphase.org) in November 2025.

## Changelog

### Version 1.0.0 (November 2025)
- Initial release
- Complete MP search and email functionality
- Email template system with placeholders
- Admin configuration interface
- Caching system implementation
- Responsive design
- Security and accessibility features
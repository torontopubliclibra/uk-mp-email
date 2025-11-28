# UK MP Email Search Plugin - Usage Guide

## How to Add to a WordPress Page

### Option 1: Using the WordPress Block Editor (Gutenberg)

1. **Edit your page or post**:
   - Go to your WordPress admin dashboard
   - Navigate to Pages → All Pages (or Posts → All Posts)
   - Click "Edit" on the page where you want to add the MP search

2. **Add a Shortcode Block**:
   - Click the "+" button to add a new block
   - Search for "Shortcode" and select it
   - Or use the keyboard shortcut: Type `/shortcode` and press Enter

3. **Add the search functionality**:
   ```
   [ukmp_email_search]
   [ukmp_email_results]
   ```

4. **Update/Publish** your page

### Option 2: Using the Classic Editor

1. **Edit your page or post** in the WordPress admin
2. **Add the shortcodes** directly in the content area:
   ```
   [ukmp_email_search]
   [ukmp_email_results]
   ```
3. **Update/Publish** your page

### Option 3: Using PHP Template Files (for developers)

Add this code to your theme template files:
```php
<?php
echo do_shortcode('[ukmp_email_search]');
echo do_shortcode('[ukmp_email_results]');
?>
```

## Available Shortcodes

### `[ukmp_email_search]` - Search Form
Creates the main search form where users enter their details.

**Basic usage:**
```
[ukmp_email_search]
```

**Advanced usage with custom text:**
```
[ukmp_email_search 
    button_text="Find My MP Now"
    name_placeholder="Enter your full name"
    email_placeholder="Your email address"
    postcode_placeholder="Your UK postcode (e.g. SW1A 1AA)"]
```

**What users see:**
- Name input field (required, adds user's name to template)
- Email input field (required, adds user's email to template)
- Postcode input field (required, finds the MP)
- Search button
- Real-time validation and loading indicators

### `[ukmp_email_results]` - Results Display
Creates the container where MP information and email preview appear.

**Usage:**
```
[ukmp_email_results]
```

**What users see after searching:**
- MP's full name with link to their Parliament profile
- MP's constituency/location
- MP's email address
- **Email preview** with subject line and message body
- **"Send" button** to open their email client with pre-filled content

### `[ukmp_email_test]` - Plugin Status Check
Displays a simple status message to verify the plugin is working.

**Usage:**
```
[ukmp_email_test]
```

**What it shows:**
- Green checkmark with "Plugin is active and working" message

## Troubleshooting

### Plugin Not Working?

1. **Check if the plugin is active**:
   - Go to Plugins → Installed Plugins
   - Make sure "UK MP Email Search" shows "Deactivate" (meaning it's active)

2. **Test the plugin**:
   - Add `[ukmp_email_test]` to any page
   - If you see a green box, the plugin is working

3. **Check for JavaScript errors**:
   - Press F12 in your browser
   - Look for red errors in the Console tab
   - Contact support if you see errors

### Shortcodes Not Rendering?

If you see `[ukmp_email_search]` as plain text instead of a form:

1. **Check plugin activation** (see above)
2. **Clear any caching plugins**
3. **Try on a different page**

### Search Not Working?

1. **Check your internet connection**
2. **Try a different postcode** (e.g., "SW1A 1AA")
3. **Check browser console for errors** (F12)

## Valid UK Postcode Examples

- SW1A 1AA (Houses of Parliament)
- M1 1AA (Manchester)
- B33 8TH (Birmingham)
- LS1 1UR (Leeds)
- G1 1AA (Glasgow)

## API Information

This plugin uses the official [UK Parliament Members API](https://members-api.parliament.uk/index.html):
- **No API key required**
- **Real-time data** from [parliament.uk](https://parliament.uk)
- **Two-step process**: 
  1. Search by postcode
  2. Fetch contact details
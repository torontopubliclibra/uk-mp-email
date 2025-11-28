# UK MP Email Search Plugin Template Implementation

## Overview
The UK MP Email Search plugin is a WordPress plugin that integrates with the [UK Parliament Members API](https://members-api.parliament.uk/index.html). It allows users to find their MP by entering a postcode, and provides a templated email system for contacting their MP directly.

## Email Template System
The plugin includes an email template system with the following features:

#### Available Placeholders
- `{MP_NAME}` - Full name and title of the MP (e.g., "John Smith MP")
- `{POSTCODE}` - The postcode entered by the user (formatted with spaces)
- `{MP_EMAIL}` - MP's email address from the Parliament API
- `{LOCATION}` - The constituency or area the MP represents
- `[Your name]` or `[Your Name]` - User's entered name (if provided)
- `[Your email]` or `[Your Email]` - User's entered email address (if provided)

#### Default Body Template
```
Dear {MP_NAME},

I am writing to you, my Member of Parliament, as a constituent of {LOCATION}.

[Your message here]

I would appreciate your response on this matter.

Sincerely,
[Your name]
[Your email]
```

### Updating the template
To update the email template, visit the admin interface at `/admin/class-ukmp-email-admin.php`, also located at Settings > UK MP Email Settings.
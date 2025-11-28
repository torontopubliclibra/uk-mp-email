<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

# UK Parliament Members API WordPress Plugin

This project is a WordPress plugin that integrates with the UK Parliament Members API to find MPs based on UK postcode input.

## Project Structure
- Main plugin file: `uk-mp-email.php`
- Admin interface: `admin/` directory
- Frontend components: `public/` directory
- API handling: `includes/api/` directory
- Assets: `assets/` directory

## Key Functionality
- **Postcode Search**: Users enter UK postcodes (e.g. "SW1A 1AA")
- **Data Processing**: Removes spaces from postcode input
- **Two-Step API Integration**: 
  1. Calls `https://members-api.parliament.uk/api/Members/Search?Location={POSTCODE}&skip=0&take=20`
  2. Then calls `https://members-api.parliament.uk/api/Members/{MP_ID}/Contact`
- **Data Display**: Shows MP name, email, and membership start date
- **Specific Data Points**:
  - `data.items[0].value.nameFullTitle` (from search API)
  - `data.value[0].email` (from contact API)
  - `data.items[0].value.latestHouseMembership.membershipFrom` (from search API)

## Development Guidelines
- Follow WordPress coding standards
- Use WordPress hooks and filters appropriately
- Sanitize all user inputs
- Use WordPress nonces for security
- Follow object-oriented programming patterns
- Use proper WordPress database API
- Implement proper error handling for API calls
- Use WordPress transients for caching API responses
- Validate UK postcode format with regex patterns
- Handle Parliament API response structure correctly

## UK Parliament API Specifics
- No API key required
- Base URL: https://members-api.parliament.uk/api/Members/Search
- Query parameters: Location (postcode), skip, take
- Response format: items array with value objects containing MP data
- Real-time postcode validation and formatting
- Direct links to Parliament member pages
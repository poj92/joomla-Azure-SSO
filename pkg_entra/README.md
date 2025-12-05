# Microsoft Entra SSO for Joomla 5

A complete Microsoft Entra (Azure AD) Single Sign-On (SSO) integration for Joomla 5.

## Features

- **Authentication Plugin**: Handles OAuth2 token exchange with Microsoft Entra
- **System Plugin**: Displays a "Login with Microsoft Entra" button on the Joomla login form
- **Seamless Integration**: Works with Joomla's native user system
- **Easy Configuration**: Simple plugin parameters for setup

## Installation

1. Download the package: `pkg_entra.zip`
2. In Joomla Admin, go to **System > Extensions > Install**
3. Upload the zip file
4. The package will automatically install both plugins

## Configuration

After installation, configure the plugins:

### Step 1: Get Azure AD Credentials
1. Go to [Azure Portal](https://portal.azure.com)
2. Create a new App Registration
3. Copy your **Client ID** and **Tenant ID**
4. Create a Client Secret and copy it
5. Set the Redirect URI to your Joomla site: `https://your-joomla-site.com/`

### Step 2: Configure in Joomla
1. Go to **System > Plugins**
2. Find and enable both plugins:
   - **Authentication - Entra**
   - **System - Entra**

3. Configure **Authentication - Entra**:
   - Client ID: (from Azure)
   - Client Secret: (from Azure)
   - Tenant ID: (from Azure)
   - Redirect URI: `https://your-joomla-site.com/`

4. Configure **System - Entra**:
   - Client ID: (from Azure)
   - Tenant ID: (from Azure)
   - Redirect URI: `https://your-joomla-site.com/`
   - Button Label: (optional, default: "Login with Microsoft Entra")

## How It Works

1. User visits the Joomla login page
2. The "Login with Microsoft Entra" button is displayed
3. User clicks the button and is redirected to Microsoft's login page
4. After successful authentication, user is redirected back to Joomla with an authorization code
5. The authentication plugin exchanges the code for a token
6. User is automatically logged into Joomla

## Requirements

- Joomla 5.0+
- PHP 7.4+
- cURL extension enabled
- Microsoft Entra tenant and App Registration

## Notes

- Users must already exist in Joomla to log in via SSO
- The plugin matches users by email address
- For production use, consider adding user auto-provisioning and enhanced error handling

## Support

For issues or feature requests, please contact the developer at peter.james@peterpanng.com or check the documentation.

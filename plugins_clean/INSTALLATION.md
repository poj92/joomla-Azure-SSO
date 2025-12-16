# PGS_Entra_SSO - Simple Joomla 5 Microsoft Entra Integration

A simple, clean authentication plugin that enables Microsoft Entra (Azure AD) Single Sign-On for Joomla 5.

## Installation

**Option 1: Install individual plugins (Recommended)**

1. Go to Joomla Admin → **System > Plugins**
2. Upload the two plugins:
   - `plg_authentication_pgs_entra.zip` (authentication plugin)
   - `plg_system_pgs_entra.zip` (UI/button plugin)

**Option 2: Copy files directly**

1. Copy `plugins_clean/authentication/pgs_entra/` to `{joomla_root}/plugins/authentication/pgs_entra/`
2. Copy `plugins_clean/system/pgs_entra/` to `{joomla_root}/plugins/system/pgs_entra/`
3. Refresh Joomla's extension cache

## Configuration

### Step 1: Enable Plugins

In Joomla Admin → **System > Plugins**:
- Find and enable **PGS_Entra_SSO**
- Find and enable **PGS_Entra_SSO - UI**

### Step 2: Get Azure Credentials

1. Go to [Azure Portal](https://portal.azure.com)
2. Navigate to **Azure AD > App registrations**
3. Create a new app registration or select existing one
4. Copy these values:
   - **Application (Client) ID** → `Client ID`
   - **Directory (Tenant) ID** → `Tenant ID`
5. Go to **Certificates & secrets** and create a new client secret:
   - Copy the secret value → `Client Secret`
6. Go to **Authentication > Redirect URIs** and add:
   - `https://your-joomla-site.com/`

### Step 3: Configure Plugins

**PGS_Entra_SSO (Authentication Plugin)**

Click to open the plugin and fill in:
- **Client ID**: From Azure
- **Client Secret**: From Azure (marked as password field for security)
- **Tenant ID**: From Azure
- **Redirect URI**: `https://your-joomla-site.com/` (must match Azure exactly)
- **Button Label**: (optional) Custom text for the button

Click **Save & Close**

**PGS_Entra_SSO - UI (System Plugin)**

Click to open the plugin and fill in:
- **Client ID**: Same as authentication plugin
- **Tenant ID**: Same as authentication plugin
- **Redirect URI**: Same as authentication plugin
- **Button Label**: (optional) Same as authentication plugin

Click **Save & Close**

## How It Works

1. User visits the Joomla login page
2. The UI plugin displays the **"Login with Microsoft Entra"** button
3. User clicks the button and is redirected to Microsoft
4. User authenticates with their Entra credentials
5. Microsoft redirects back with an authorization code
6. The authentication plugin exchanges the code for a token
7. Plugin verifies the user's email exists in Joomla
8. User is automatically logged in

## Requirements

- Joomla 5.0+
- PHP 7.4+
- cURL enabled
- Active Microsoft Entra tenant
- Existing Joomla users with matching email addresses

## Troubleshooting

**"User not found"** - Ensure a Joomla user exists with the same email as the Entra account.

**Redirect URI mismatch** - Make sure the exact URL (scheme/host/path) matches in both Azure and Joomla.

**Token exchange fails** - Verify Client ID, Client Secret, and Tenant ID are correct in both plugins.

**Button not showing** - Enable both plugins and ensure you're on the frontend login page.

## Security Notes

- The ID token is decoded but signature is not verified (uses HTTPS + state for CSRF protection)
- For production with high security requirements, add JWT signature validation
- Never expose Client Secret in frontend code (only use in plugins configured in admin)

## Support

For issues, verify:
1. Both plugins are enabled
2. Configuration matches Azure settings exactly
3. Joomla user exists with matching email
4. PHP cURL is enabled on the server

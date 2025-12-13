# Microsoft Entra SSO for Joomla 5

A complete Microsoft Entra (Azure AD) Single Sign-On (SSO) integration for Joomla 5.

## Features

- **Authentication Plugin**: Exchanges the Azure authorization code for tokens and validates the user by email
- **System Plugin**: Renders the "Login with Microsoft Entra" button on the Joomla login form and handles the Azure callback
- **State/CSRF protection**: Adds a per-request state value to the Azure authorize URL and checks it on return
- **Joomla-native login**: Uses the core authentication events to sign users in (no password prompt)
- **Simple setup**: A single package installs both plugins

## Installation (package)

1) Zip the `pkg_entra` folder into `pkg_entra.zip` (the zip root must contain `pkg_entra.xml`).
2) In Joomla Admin go to **System > Extensions > Install**, upload `pkg_entra.zip`.
3) Both plugins install in one step.

## Configuration

After installation, configure the plugins:

### Step 1: Azure App Registration
1. In [Azure Portal](https://portal.azure.com) create an App Registration.
2. Copy **Client ID** and **Tenant ID**.
3. Create a **Client Secret** and copy it.
4. Add a **Redirect URI** (web) that exactly matches your Joomla site, e.g. `https://your-joomla-site.com/` (include path if you use one).

### Step 2: Configure in Joomla
1. Go to **System > Plugins** and enable:
   - **Authentication - Entra**
   - **System - Entra**
2. Open **Authentication - Entra** and set: Client ID, Client Secret, Tenant ID, Redirect URI.
3. Open **System - Entra** and set: Client ID, Client Secret, Tenant ID, Redirect URI, Button Label (optional).

### Step 3: Test
1. Visit the Joomla login page on the frontend.
2. Click **Login with Microsoft Entra**.
3. Sign in at Microsoft; you will be redirected back and logged in automatically if a Joomla user with the same email exists.

## How It Works

1) User visits the Joomla login page; the system plugin injects the Entra button.
2) The button sends the user to Azure with `state` for CSRF protection.
3) Azure returns `code` (and `state`) to Joomla.
4) The system plugin checks `state`, exchanges `code` for tokens, decodes the ID token, and finds the Joomla user by email.
5) The plugin triggers Joomla’s login event and sets the identity; user is signed in without entering a Joomla password.

## Requirements

- Joomla 5.0+
- PHP 7.4+
- cURL extension enabled
- Microsoft Entra tenant and App Registration

## Notes

- Users must already exist in Joomla to log in via SSO (matched by email)
- Redirect URI must match exactly (scheme/host/path) with the Azure app setting
- Ensure PHP cURL is enabled on the server
- For production, add JWT signature validation (e.g., with the bundled OpenID Connect library) and consider user auto-provisioning

## Support

For issues or feature requests, please contact the developer at peter.james@peterpanng.com or check the documentation.

# Joomla 5 Microsoft Entra (Azure AD) SSO Plugin

## Installation

1. Zip the `entra` folder (containing `entra.php` and `entra.xml`).
2. In Joomla Admin, go to **System > Extensions > Install** and upload the zip file.
3. Enable the plugin in **System > Plugins > Authentication - Entra**.

## Configuration

- Set your Azure AD `Client ID`, `Client Secret`, `Tenant ID`, and `Redirect URI` in the plugin settings.
- The Redirect URI should match the one set in your Azure AD App Registration.

## Usage

- When users access the Joomla login page, redirect them to the Microsoft Entra login URL:
  `https://login.microsoftonline.com/{tenant_id}/oauth2/v2.0/authorize?client_id={client_id}&response_type=code&redirect_uri={redirect_uri}&scope=openid%20profile%20email`
- After successful login, users will be redirected back to Joomla and authenticated via SSO.

## Notes
- This is a simple example for demonstration. For production, add error handling, user creation, and security improvements.
- You may use the OpenID Connect PHP library for advanced features.

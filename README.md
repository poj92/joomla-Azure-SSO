# com_azuresso — Joomla 5 Azure (Entra) OpenID Connect SSO

This repository contains a scaffold for a Joomla 5 component `com_azuresso` that provides OpenID Connect (OIDC) Single Sign-On (SSO) with Microsoft Entra (Azure AD).

Important notes
- The code uses the maintained library `jumbojett/openid-connect-php` (declared in `composer.json`).
- **Vendor files are bundled** in the `vendor/` folder, so the component is ready to install directly into Joomla without requiring Composer.

Installation
**Option A: Via ZIP package (recommended)**
1. Download or use the pre-built `com_azuresso-0.1.0.zip` from this repository.
2. Go to Joomla Admin → Extensions → Manage → Install.
3. Upload the ZIP file.
4. The component will be installed and ready to use.

**Option B: Manual copy**
1. Copy the component into your Joomla installation:
   - `admin` folder → `administrator/components/com_azuresso`
   - `site` folder → `components/com_azuresso`
   - `vendor` folder → `components/com_azuresso/vendor`
   - `com_azuresso.xml` → ensure it's present for the extension manager

Development (optional)
- If you want to update dependencies in the future, ensure `composer.json` is present and run:

```bash
composer install --no-dev --prefer-dist
```

Azure (Entra) App Registration
1. Go to Azure Portal → Azure Active Directory → App registrations → New registration.
2. Name: `Joomla SSO` (or your choice).
3. Supported account types: pick single-tenant or multi-tenant as needed.
4. Redirect URI (Web): `https://your-joomla-site.example/index.php?option=com_azuresso&task=callback.handle`
5. Under `Authentication`, enable `ID tokens`.
6. Create a client secret and copy it.
7. Note the Application (client) ID and Directory (tenant) ID.

Component configuration (Joomla admin)
- Open Components → Azure SSO → Options and set:
  - Client ID
  - Client Secret
  - Tenant ID (or `common`)
  - Scopes (default: `openid profile email`)
  - Auto provision users: Yes/No
  - Default user group: `Registered` (default)
  - Post logout redirect URL (optional)

Admin Dashboard
- After installation, visit **Components → Azure SSO** to access:
  - Configuration instructions
  - Setup wizard (for Azure app registration)
  - Connection test button
  - Quick reference links

Behavior
- Site routes provided:
  - `index.php?option=com_azuresso&task=login.login` — starts authentication (redirects to Azure)
  - `index.php?option=com_azuresso&task=callback.handle` — handles the provider callback
  - `index.php?option=com_azuresso&task=logout.logout` — logs out locally and attempts RP-initiated logout at Azure

User provisioning
- If `Auto provision` is enabled, users are created with the name and email claims returned by Azure, assigned to the configured default user group.
- If auto provision is disabled, only existing Joomla users (found by email) may sign in.

Single Logout
- The component performs a simple RP-initiated logout by redirecting to Azure's logout endpoint. For a full federated SLO experience (front/back-channel logout), additional implementation on both sides is required.

Next steps I can do for you
- Bundle the actual `vendor/` library files so the component is installable without running Composer.
- Add an admin UI page in the component (Configuration/Logs/Test button) for easier testing.
- Improve user provisioning (map claims to fields, username collision handling, admin approval workflow).

If you want me to bundle `vendor/` now, say "bundle vendor" and I'll fetch and add the dependencies into `vendor/` and update the package so it's directly installable in Joomla without Composer.
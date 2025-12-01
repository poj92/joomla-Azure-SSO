<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>

<div id="j-main-container" class="span10">
  <h1><?php echo Text::_('Azure SSO Configuration & Testing'); ?></h1>

  <div class="row-fluid">
    <div class="span6">
      <fieldset class="adminform">
        <legend><?php echo Text::_('Component Configuration'); ?></legend>
        <p>Configure the Azure OAuth settings in <strong>Joomla Admin → Components → Azure SSO → Options</strong>.</p>
        <p>Required fields:</p>
        <ul>
          <li><strong>Client ID</strong> — Azure App (Client) ID</li>
          <li><strong>Client Secret</strong> — Azure Client secret</li>
          <li><strong>Tenant ID</strong> — Azure Tenant ID (or 'common')</li>
          <li><strong>Scopes</strong> — OpenID Connect scopes (default: 'openid profile email')</li>
          <li><strong>Auto provision</strong> — Create Joomla users automatically if enabled</li>
          <li><strong>Default user group</strong> — Group for new users (default: 'Registered')</li>
        </ul>
      </fieldset>
    </div>

    <div class="span6">
      <fieldset class="adminform">
        <legend><?php echo Text::_('Test Connection'); ?></legend>
        <form method="POST" action="index.php?option=com_azuresso&view=azuresso&layout=test">
          <?php echo HTMLHelper::_('form.token'); ?>
          <button type="submit" class="btn btn-primary">Test Azure Connection</button>
        </form>
        <p style="margin-top: 10px; font-size: 0.9em; color: #666;">
          This will attempt to discover Azure's OIDC configuration endpoint and verify settings.
        </p>
      </fieldset>
    </div>
  </div>

  <div class="row-fluid" style="margin-top: 20px;">
    <div class="span12">
      <fieldset class="adminform">
        <legend><?php echo Text::_('Setup Instructions'); ?></legend>
        <h3>1. Azure App Registration</h3>
        <ol>
          <li>Go to <strong>Azure Portal</strong> → <strong>Azure Active Directory</strong> → <strong>App registrations</strong> → <strong>New registration</strong></li>
          <li>Name: "Joomla SSO" (or your choice)</li>
          <li>Select <strong>Supported account types</strong> (single-tenant or multi-tenant)</li>
          <li>Add <strong>Redirect URI (Web)</strong>:
            <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">https://<?php echo $_SERVER['HTTP_HOST']; ?>/index.php?option=com_azuresso&task=callback.handle</pre>
          </li>
          <li>Under <strong>Authentication</strong>, enable <strong>ID tokens</strong></li>
          <li>Create a <strong>Client secret</strong></li>
          <li>Note the <strong>Application (Client) ID</strong> and <strong>Directory (Tenant) ID</strong></li>
        </ol>

        <h3>2. Configure in Joomla</h3>
        <ol>
          <li>Go to <strong>Components → Azure SSO → Options</strong></li>
          <li>Enter the Client ID and Client Secret</li>
          <li>Enter the Tenant ID</li>
          <li>Set <strong>Auto provision</strong> and <strong>Default user group</strong> as needed</li>
          <li>Save</li>
        </ol>

        <h3>3. Test & Deploy</h3>
        <ol>
          <li>Click the <strong>Test Azure Connection</strong> button above (once configured)</li>
          <li>Create a menu item linking to <code>index.php?option=com_azuresso&task=login.login</code></li>
          <li>Users can click that link to sign in with Azure</li>
        </ol>

        <h3>4. Logout (optional)</h3>
        <p>To log out, link to <code>index.php?option=com_azuresso&task=logout.logout</code></p>
      </fieldset>
    </div>
  </div>

    <div class="row-fluid" style="margin-top: 20px;">
      <div class="span12">
        <fieldset class="adminform">
          <legend><?php echo Text::_('Diagnostics / Logs'); ?></legend>
          <p>Last debug output captured by the component (most recent first).</p>
          <pre style="max-height: 240px; overflow:auto; background:#111; color:#d6e4ff; padding:10px; border-radius:6px;">
<?php
$logFile = JPATH_ADMINISTRATOR . '/components/com_azuresso/logs/debug.log';
if (file_exists($logFile)) {
    $lines = array_reverse(explode("\n", trim(file_get_contents($logFile))));
    $show = array_slice($lines, 0, 120);
    echo htmlspecialchars(implode("\n", $show));
} else {
    echo "(no debug log present yet)";
}
?>
          </pre>
        </fieldset>
      </div>
    </div>
</div>

<?php
if ($op == 'edit') {
  $title = $login;
  $breadcrumb = array('dns/servers' => _('Servers'), 'dns/edit/'.$id => _("Modifier"));
} else {
  $title = _("Add a server");
  $breadcrumb = array('dns/servers' => _('Servers'), 'dns/add' => _("Add"));
}


require VIEWS . '/header.php';
?>

<?php if(count($errors) > 0): ?>
<div class="flash error">
  <?php if(count($errors) == 1): ?>
  <p><?php echo $errors[0]; ?></p>
  <?php elseif(count($errors) > 1): ?>
  <ul>
    <?php foreach($errors as $err): ?>
    <li><?php echo $err; ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</div>
<?php endif; ?>


<form action="" method="post">
  <fieldset style="width: 60%;">
  <legend><?php if ($op=="add") __("Add a server in the DNS Manager"); else __("Edit a server in the DNS Manager"); ?></legend>
    <?php input('hostname', _("Hostname:"), 'text', $data['hostname']); ?>
    <?php input('ip', _("IPv4 Address:"), 'text', $data['ip']); ?>

    <?php input('enabled', _("Enable DNS synchronization using AlternC's protocol?"), 'checkbox', $data['enabled']); ?>
</fieldset>

    <p><?php __("If you enable the synchronization of DNS zones from your AlternC to this manager, you need to fill the field below. You must create the account in the administrator panel of AlternC"); ?></p>

  <fieldset style="width: 60%;">
    <legend><?php __("AlternC synchronisation"); ?></legend>
    <?php input('fqdn', _("Fqdn:"), 'text', $data['fqdn']); ?>
    <?php input('login', _("Login:"), 'text', $data['login']); ?>
    <?php input('password', _("Password:"), 'text', $data['password']); ?>

    <?php input('hasssl', _("Is it ssl-enabled?"), 'checkbox', $data['hasssl']); ?>
  </fieldset>
    <p><?php __("Your server must have AlternC panel on http(s)://fqdn/"); ?>
  <p class="submit"><input type="submit" value="<?php
if ($op == 'add') __("Add this server");
if ($op == 'edit') __("Edit this server");
?>" /> - <input type="button" onclick="javascript:history.go(-1)" value="<?php __("Cancel"); ?>" /></p>
</form>

<?php require VIEWS . '/footer.php'; ?>

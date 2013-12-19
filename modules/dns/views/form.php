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
  <fieldset style="width: 80%;">
  <legend><?php if ($op=="add") __("Add a server in the DNS Manager"); else __("Edit a server in the DNS Manager"); ?></legend>
    <?php input('hostname', _("Hostname:"), 'text', $data['hostname']); ?>
    <?php input('ip', _("IPv4 Address:"), 'text', $data['ip']); ?>

    <?php input('enabled', _("Enable DNS synchronization using AlternC's protocol?"), 'checkbox', $data['enabled']); ?>
  <p><?php __("The hostname usually don't contains any dot (.) and is the canonical name of the server. eg. <em>gandalf</em> or <em>brassens</em>."); ?></p>
</fieldset>

  <p><?php __("If you enable the synchronization of DNS zones from your AlternC to this manager, you need to fill the fields below."); ?></p>

  <fieldset style="width: 80%;">
    <legend><?php __("AlternC synchronisation"); ?></legend>
<?php input('url', _("URL:"), 'text', $data['url'],"largeinput"); ?>
<?php input('nosslcheck', _("Don't check the certificate"), 'checkbox', $data['nosslcheck']); ?>
<?php input('cacert', _("CA Certificate:"), 'textarea', $data['cacert'],"certificate"); ?>
<p><?php __("You must have created a SlaveDNS account in the <b>Admin Control Panel</b> of AlternC, in the <b>Manage slave DNS</b> menu."); ?>
<br/ ><?php __("The url is usually like <code>https://login:password@panel.yourserver.tld/domlist.php</code>"); ?>
<br/ ><?php __("If you don't have a proper CA-signed certificate, you can paste here your self-signed certificate, or check the box above to block any certificate check (not recommended)."); ?>
  </fieldset>
  <p class="submit"><input type="submit" value="<?php
if ($op == 'add') __("Add this server");
if ($op == 'edit') __("Edit this server");
?>" /> - <input type="button" onclick="javascript:history.go(-1)" value="<?php __("Cancel"); ?>" /></p>
</form>

<?php require VIEWS . '/footer.php'; ?>

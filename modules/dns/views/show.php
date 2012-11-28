<?php
$title = $server->hostname;
$breadcrumb = array('dns/servers' => _('Servers'), '' => $server->hostname);

if ($_REQUEST["msg"]) $errors[]=htmlentities($_REQUEST["msg"]);

require(VIEWS . '/header.php');
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


<h2><?php __("Server informations"); ?></h2>
<dl>
  <dt><?php __("Hostname"); ?></dt>
  <dd><?php echo $server->hostname; ?></dd>

  <dt><?php __("IPv4 Address"); ?></dt>
  <dd><?php echo $server->ip; ?></dd>

  <dt><?php __("AlternC synchronization enabled ?"); ?></dt>
  <dd><?php if ($server->enabled) __("Yes"); else __("No"); ?></dd>

  <dt><?php __("FQDN"); ?></dt>
  <dd><?php echo $server->fqdn; ?></dd>

  <dt><?php __("Login"); ?></dt>
  <dd><?php echo $server->login; ?></dd>

  <dt><?php __("Password"); ?></dt>
  <dd><?php echo $server->password; ?></dd>

  <dt><?php __("Is it SSL-Enabled?"); ?></dt>
  <dd><?php if ($server->hasssl) __("Yes"); else __("No"); ?></dd>
</dl>

<?php
   $infos = array($servers);
   Hooks::call('servers_show', $infos);
   array_shift($infos);
   echo implode("\n", $infos);
?>

<?php require VIEWS . '/footer.php'; ?>

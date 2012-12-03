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
    <style>
    td.col_enabled,
  td.col_admin,
  td.col_accounting,
  td.col_actions {
      text-align: center;
			     }
table.list col:first-child {background: #FF0}
</style>
<div style="float: left; padding-right: 50px">

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
</div>

<div style="float: left; padding-right: 50px">
<h2><?php __("Zone hosted in that server"); ?></h2>
<table class="list"><tr><th>Zone</th><th>Date</th></tr>
  <?php
foreach($zones as $z) {
  if ($z->enabled) {
    echo "<tr><td>".$z->zone."</td><td>".$z->datec."</td></tr>";
  } else {
    echo "<tr><td><span class=\"flash error\"".$z->zone." "._("DISABLED (conflict)")."</span></td><td>".$z->datec."</td></tr>";
  }
}
?>
</table>
</div>

<div style="float: left">
<h2><?php __("Last log for this server"); ?></h2>
<?php echo html_table_list($diffheaders, $diff); ?>
</div>

<?php require VIEWS . '/footer.php'; ?>

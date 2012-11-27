<?php
$title = $server->hostname;
$breadcrumb = array('dns/servers' => _('Servers'), '' => $server->hostname);_

$menu1 = html_list(_("Actions"),
		   array(l(_("Edit"), 'dns/edit/' . $user->uid),
			 l(_("Delete"), 'dns/delete/' . $user->uid),
			 ));
$infos = array($server);
Hooks::call('server_show_links', $infos);
array_shift($infos);
$menu2 = html_list(_("Other informations"), $infos);

$sidebar = $menu1 . $menu2;

require VIEWS . '/header.php';
?>

<h2><?php __("General informations"); ?></h2>
<dl>
  <dt><?php __("Login"); ?></dt>
  <dd><?php echo $user->login; ?></dd>

  <dt><?php __("Email address"); ?></dt>
  <dd><?php echo $user->email; ?></dd>

  <dt><?php __("Enabled?"); ?></dt>
  <dd><?php echo $user->enabled; ?></dd>

  <dt><?php __("Administrator?"); ?></dt>
  <dd><?php echo $user->admin; ?></dd>
</dl>

<?php
   $infos = array($user);
   Hooks::call('servers_show', $infos);
   array_shift($infos);
   echo implode("\n", $infos);
?>

<?php require VIEWS . '/footer.php'; ?>

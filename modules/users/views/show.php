<?php
$title = $user->login;
$breadcrumb = array('users' => 'Users', '' => $user->login);

$menu1 = html_list(_("Actions"),
		   array(l(_("Edit"), 'users/edit/' . $user->uid),
			 l(_("Delete"), 'users/delete/' . $user->uid),
			 l(_("Connect As"), 'users/impersonate/' . $user->uid))
		   );
$infos = array($user);
Hooks::call('users_show_links', $infos);
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
   Hooks::call('users_show', $infos);
   array_shift($infos);
   echo implode("\n", $infos);
?>

<?php require VIEWS . '/footer.php'; ?>

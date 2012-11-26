<?php
$title = $user->login;
$breadcrumb = array('users' => 'Utilisateurs', 'users/show/' . $user->uid => $user->login, '' => _("Supprimer"));

require VIEWS . '/header.php';
?>
<form action="" method="post">
  <p><?php echo sprintf(_("Do you really want to delete user '%s'?"), $user->login); ?></p>
  <p><?php __("This will delete all its associated media"); ?></p>
  <?php
     $informations = array($user);
     Hooks::call('users_delete_infos', $informations);
     array_shift($informations);
     echo implode($informations);
     ?>
  <p>
  <input type="submit" name="op" value="<?php __("Yes, delete it"); ?>" />
       <input type="submit" name="op" value="<?php __("No, don't do anything"); ?>" onclick="javascript:history.go(-1); return false;" />
  </p>
</form>

<?php require VIEWS . '/footer.php'; ?>

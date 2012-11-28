<?php
$title = $server->hostname;
$breadcrumb = array('servers' => 'Utilisateurs', 'servers/show/' . $server->id => $server->hostname, '' => _("Supprimer"));

require VIEWS . '/header.php';
?>
<form action="" method="post">
  <p><?php echo sprintf(_("Do you really want to delete server '%s'?"), $server->hostname); ?></p>
  <?php
     $informations = array($server);
     Hooks::call('servers_delete_infos', $informations);
     array_shift($informations);
     echo implode($informations);
     ?>
  <p>
  <input type="submit" name="op" value="<?php __("Yes, delete it"); ?>" />
       <input type="submit" name="op" value="<?php __("No, don't do anything"); ?>" onclick="javascript:history.go(-1); return false;" />
  </p>
</form>

<?php require VIEWS . '/footer.php'; ?>

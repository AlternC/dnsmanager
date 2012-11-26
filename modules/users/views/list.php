<?php
$title = _('Users');
$breadcrumb = array('' => 'Users');
$sidebar = '<p>→ ' . l(_("Create a user"), 'users/add') . '</p>';
require VIEWS . '/header.php';
?>
<style>
  td.col_enabled,
  td.col_admin,
  td.col_accounting,
  td.col_actions {
  text-align: center;
  }
table.list col:first-child {background: #FF0}
</style>

<?php echo html_table_list($headers, $users); ?>

<?php require VIEWS . '/footer.php'; ?>

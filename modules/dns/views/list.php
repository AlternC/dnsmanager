<?php
$title = _('Servers');
$breadcrumb = array('' => 'Servers');
$sidebar = '<p>→ ' . l(_("Add a server"), 'dns/add') . '</p>';
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

<?php echo html_table_list($headers, $servers); ?>

<?php require VIEWS . '/footer.php'; ?>

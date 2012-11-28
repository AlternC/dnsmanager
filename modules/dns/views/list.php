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

<?php echo html_table_list($headers, $servers); ?>

<?php require VIEWS . '/footer.php'; ?>

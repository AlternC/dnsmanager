<?php
if ($op == 'edit') {
  $title = $login;
  $breadcrumb = array('users' => 'Users', 'users/show/' . $login => $login, '' => _("Modifier"));
}
elseif ($op == 'meedit') {
  $title = _("Edit my account");
  $breadcrumb = array('users/me' => _("My account"), '' => _("Edit"));
}
else {
  $title = _("Create a user");
  $breadcrumb = array('users' => 'Users', '' => _("Add"));
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
  <fieldset style="width: 60%;">
    <legend><?php __("Identity"); ?></legend>
    <?php if ($op != 'meedit'): ?>
    <?php input('login', _("Login:"), 'text', $data['login']); ?>
    <?php endif; ?>

    <?php if ($op == 'edit' || $op == 'meedit'): ?>
    <?php $pass_options = array('details' => _("Keep this empty if you don't want to change the password")); ?>
    <?php endif; ?>
    <?php echo html_field('password', 'pass', _("Password:"), null, null, $pass_options); ?>
    <?php echo html_field('password', 'pass_confirm', _("Password (confirmation):"), null, null, $pass_options); ?>
    <?php if ($op == 'edit'): ?>
    <?php echo html_field('checkbox', 'notify_new_passwd', _("Send the password by mail")); ?>
    <br />
    <?php echo html_field('checkbox', 'notify_new_account', _("Send a mail telling about that new account")); ?>
    <?php endif; ?>
    <br />

    <?php input('email', _("Email address"), 'text', $data['email']); ?>
    <?php if ($op != 'meedit'): ?>
    <?php input('admin', _("Is it an administrator?"), 'checkbox', $data['admin']); ?>
    <?php input('enabled', _("Is it enabled?"), 'checkbox', $data['enabled']); ?>
    <?php endif; ?>
  </fieldset>

  <p class="submit"><input type="submit" value="<?php
if ($op == 'add') __("Add this user");
if ($op == 'edit') __("Edit this user");
if ($op == 'meedit') __("Change my infos");
?>" /> - <input type="button" onclick="javascript:history.go(-1)" value="<?php __("Cancel"); ?>" /></p>
</form>

<?php require VIEWS . '/footer.php'; ?>

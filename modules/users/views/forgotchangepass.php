<?php
  $title = _("Change my password");

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

    <p>&nbsp;</p>

    <form action="/users/reminder" method="post">
  <fieldset>
  <input type="hidden" name="id" value="<?php echo $id; ?>" />
  <input type="hidden" name="key" value="<?php echo $key; ?>" />
    <?php input('pass', _("Enter your new password:"), 'password'); ?>
    <?php input('pass2', _("Enter your new password (again):"), 'password'); ?>
  </fieldset>

  <p class="submit"><input type="submit" value="<?=_("Change my password"); ?>" /></p>
</form>

<?php require VIEWS . '/footer.php'; ?>

<?php
  $title = _("I forgot my password");

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

<form action="" method="post">
  <fieldset>
    <?php input('email', _("Enter your email:"), 'text', $email); ?>
  <p><?php __("If you forgot your login or password, please enter your email above, we will send you a link to reset your password."); ?></p>
  </fieldset>

  <p class="submit"><input type="submit" value="<?=_("Send me a password reminder"); ?>" /></p>
</form>

<?php require VIEWS . '/footer.php'; ?>

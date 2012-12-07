<?php
  $title = _("I forgot my password");

require VIEWS . '/header.php';
?>
<?php $this->render("flash",array("errors"=>$errors, "notice"=>$notice)); ?>
    <p>&nbsp;</p>

<form action="" method="post">
  <fieldset>
    <?php input('email', _("Enter your email:"), 'text', $email); ?>
  <p><?php __("If you forgot your login or password, please enter your email above, we will send you a link to reset your password."); ?></p>
  </fieldset>

  <p class="submit"><input type="submit" value="<?=_("Send me a password reminder"); ?>" /></p>
</form>

<?php require VIEWS . '/footer.php'; ?>

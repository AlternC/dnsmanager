<?php
  $title = _("I forgot my password");

require VIEWS . '/header.php';
?>
<?php $this->render("flash",array("errors"=>$errors, "notice"=>$notice)); ?>
    <p>&nbsp;</p>
<?php require VIEWS . '/footer.php'; ?>

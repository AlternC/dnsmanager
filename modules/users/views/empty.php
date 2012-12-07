<?php
$title = _("AlternC DNS Manager");

require VIEWS . '/header.php';
?>
<?php $this->render("flash",array("errors"=>$errors, "notice"=>$notice)); ?>
<?php require VIEWS . '/footer.php'; ?>

<?php

require VIEWS . '/header.php';
?>

<h2><?php __("Account Creation"); ?></h2>

<?php $this->render("flash",array("errors"=>$errors, "notice"=>$notice)); ?>

<?php if (count($notice)) { ?>
<p><a href="/users/me">Log into my account</a></p>
<?php } ?>

<?php require VIEWS . '/footer.php'; ?>

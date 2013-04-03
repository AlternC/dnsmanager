<?php

require VIEWS . '/header.php';
?>

<h2><?php __("Account Creation"); ?></h2>
<p><?php __("Your account has been created successfully, an email with a confirmation link has been sent to you"); ?></p>

<?php $this->render("flash"); ?>

<?php require VIEWS . '/footer.php'; ?>

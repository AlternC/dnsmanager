<?php
$title = _('Account Creation Form');
require VIEWS . '/header.php';
?>
<?php $this->render("flash",array("errors"=>$errors, "notice"=>$notice)); ?>
<?php

include("home.php");

require VIEWS . '/foot.php';

?>

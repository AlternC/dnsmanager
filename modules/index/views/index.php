<?php

$infos = array($user);
?>

<?php require_once VIEWS . '/header.php'; ?>

<h2><?php __("Welcome"); ?></h2>
<p><?php echo $msg; ?></p>

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


<?php
 Hooks::call('index_indexview');						 
?>

<?php require_once VIEWS . '/footer.php'; ?>

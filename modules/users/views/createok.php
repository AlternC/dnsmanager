<?php

require VIEWS . '/header.php';
?>

<h2><?php __("Account Creation"); ?></h2>
<p><?php __("Your account has been created successfully, an email with a confirmation link has been sent to you"); ?></p>

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


<?php require VIEWS . '/footer.php'; ?>

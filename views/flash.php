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

<?php if(count($notice) > 0): ?>
<div class="flash notice">
  <?php if(count($notice) == 1): ?>
  <p><?php echo $notice[0]; ?></p>
  <?php elseif(count($notice) > 1): ?>
  <ul>
    <?php foreach($notice as $err): ?>
    <li><?php echo $err; ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</div>
<?php endif; ?>

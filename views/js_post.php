<?php
// FIXME: make a minified cache of post-js files from modules/hooks
$files = array();
Hooks::call('post_js', $files);
?>
<?php foreach($files as $file): ?>
<script src="<?php echo STATIC_URL . 'js/' . $file; ?>"></script>
<?php endforeach; ?>

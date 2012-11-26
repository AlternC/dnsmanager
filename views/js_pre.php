<script src="<?php echo STATIC_URL . 'js/jquery.js'; ?>"></script>
<script src="<?php echo STATIC_URL . 'js/jquery.textlimit.min.js'; ?>"></script>
<script src="<?php echo STATIC_URL . 'js/visualize.jQuery.js'; ?>"></script>
<script src="<?php echo STATIC_URL . 'js/common.js'; ?>"></script>
<?php
// FIXME: make a minified cache of js files from modules/hooks
$files = array();
Hooks::call('pre_js', $files);
$files = array('jquery.tablesorter.min.js', 'tablesorter.js');
?>
<?php foreach($files as $file): ?>
<script src="<?php echo STATIC_URL . 'js/' . $file; ?>"></script>
<?php endforeach; ?>

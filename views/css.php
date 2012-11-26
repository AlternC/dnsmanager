<?php
$files = array();
$files[] = 'common.css';
$files[] = 'jquery-ui-1.8.21.custom.css';
$files[] = 'visualize.jQuery.css';
Hooks::call('css', $files);
// FIXME: make a minified cache of css files from modules/hooks
?>
<?php foreach($files as $file): ?>
<link rel="stylesheet" media="all" href="<?php echo STATIC_URL . 'css/' . $file; ?>" />
<?php endforeach; ?>

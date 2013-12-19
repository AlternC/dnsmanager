<?php
if (empty($title))
  $title = '';
?>
<!DOCTYPE html>
<html>
    <head>

   <link rel="shortcut icon" href="/static/favicon.ico" />
   <link rel="icon" type="image/vnd.microsoft.icon" href="/static/favicon.ico" />

        <meta charset="utf-8" />
        <title>AlternC dnsmanager<?php if (!empty($title)): ?> - <?php print $title; ?><?php endif; ?></title>
        <?php require VIEWS . '/css.php'; ?>
        <?php require VIEWS . '/js_pre.php'; ?>
        <?php Hooks::call('html_head'); ?>
    </head>
    <body>
        <div id="global">
            <div id="header">
	        <div id="top">
		  <?php
		     $html = array();
		     Hooks::call('content_top', $html);
		     echo implode($html, "\n");
		     ?>
<p class="login"> <a href="?setlang=fr_FR">Français</a> - <a href="?setlang=en_US">English</a> &nbsp;  <?php
		       if ($GLOBALS["me"]["login"]) {
			 printf(_("Connected as %s"),"<strong>".$GLOBALS["me"]["login"]."</strong>"); 
		       }
?></p>
		</div>
		<h1>AlternC dnsmanager<?php if (!empty($title)): ?> - <?php print $title; ?><?php endif; ?></h1>
                <div id="menu"><?php require VIEWS . '/menu.php'; ?></div>
            </div>
	    <div id="main">
	      <?php if (!empty($sidebar)): ?>
	      <div id="sidebar">
		<?php print $sidebar; ?>
	      </div>
	      <?php endif; ?>
              <div id="content"<?php if(!empty($sidebar)) echo ' class="with-sidebar"'; ?>>
		<?php if (!empty($breadcrumb)): ?>
		<ul id="breadcrumb">
		  <li class="first"><?php echo l('DNS Manager', '/'); ?></li>
		  <?php foreach ($breadcrumb as $url => $name): ?>
		  <li><?php echo l($name, $url); ?></li>
		  <?php endforeach; ?>
		</ul>
		<?php endif; ?>

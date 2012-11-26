<?php
$menu = array();
Hooks::call('menu', $menu);
$menu_plus = array();
Hooks::call('menu_plus', $menu_plus);
// TODO: gestion du lien actif
?>
<ul>
  <?php foreach($menu as $el): 
	$url = $el['url'];
	$name = $el['name'];
	?>
  <li><a href="<?php echo $url; ?>"><?php echo $name; ?></a></li>
  <?php endforeach; ?>
  <?php if (count($menu_plus) > 0): ?>
  <li id="menu_plus">
    <a href="#plus"><?php __("Plus ↓"); ?></a>
    <ul>
      <?php foreach($menu_plus as $el): 
	    $url = $el['url'];
	    $name = $el['name'];
	    ?>
      <li><a href="<?php echo $url; ?>"><?php echo $name; ?></a></li>
      <?php endforeach; ?>
    </ul>
  </li>
  <?php endif; ?>
</ul>

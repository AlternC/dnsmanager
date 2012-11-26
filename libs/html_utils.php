<?php

function show_messages() {
  global $view;
  if (isset($view["error"])) echo "<div class=\"flash error\">".$view["error"]."</div>"; 
  if (isset($view["warning"])) echo "<div class=\"flash warning\">".$view["warning"]."</div>"; 
  if (isset($view["message"])) echo "<div class=\"flash notice\">".$view["message"]."</div>"; 
}

// todo : change eher to ehe and make it use request !!
function eher($str) { echo htmlentities($str,ENT_COMPAT,"UTF-8"); } 

function ehe($str) { echo htmlentities($str,ENT_COMPAT,"UTF-8"); } 
function her($str) { return htmlentities($str,ENT_COMPAT,"UTF-8"); } 
function he($str) { return htmlentities($str,ENT_COMPAT,"UTF-8"); } 
function checked($bool) { if ($bool) echo " checked=\"checked\""; }

/* select_values($arr,$cur) echo des <option> du tableau $values ou de la table sql $values
   selectionne $current par defaut. Par defaut prends les champs 0 comme id et 1 comme 
   donnees pour la table. sinon utilise $info[0] et $info[1].
*/
function eoption($values,$cur,$info="") {
  if (is_array($values)) {
    foreach ($values as $k=>$v) {
      echo "<option value=\"$k\"";
      if ($k==$cur) echo " selected=\"selected\"";
      echo ">".$v."</option>";
    }
  } else {
    if (is_array($info)) {
      $r=mqlist("SELECT ".$info[0].", ".$info[1]." FROM $values ORDER BY ".$info[0].";");
    } else {
      $r=mqlist("SELECT * FROM $values ORDER BY 2;");
    }

    foreach ($r as $c) {
      echo "<option value=\"".$c[0]."\"";
      if ($c[0]==$cur) echo " selected=\"selected\"";
      echo ">".sts($c[1])."</option>";
    }
  }
}


/* Affiche un pager sous la forme suivante : 
  Page précédente 0 1 2 ... 16 17 18 19 20 ... 35 36 37 Page suivante
  Les arguments sont comme suit : 
  $offset = L'offset courant de la page.
  $count = Le nombre d'éléments affiché par page.
  $total = Le nombre total d'éléments dans l'ensemble
  $url = L'url à afficher. %%offset%% sera remplacé par le nouvel offset des pages.
  $before et $after sont le code HTML à afficher AVANT et APRES le pager SI CELUI CI DOIT ETRE AFFICHE ...
  TODO : ajouter un paramètre class pour les balises html A.
  */
function pager($offset,$count,$total,$url,$before="",$after="") {
  //  echo "PAGER : offset:$offset, count:$count, total:$total, url:$url<br />";
  $offset=intval($offset); 
  $count=intval($count); 
  $total=intval($total); 
  if ($offset<=0) $offset="0";
  if ($count<=1) $count="1";
  if ($total<=0) $total="0";
  if ($total<$offset) $offset=max(0,$total-$count);

  if ($total<=$count) { // When there is less element than 1 complete page, just don't do anything :-D
    return true;
  }
  echo $before;
  // Shall-we show previous page link ?
  if ($offset) {
    $o=max($offset-$count,0);
    echo "<a href=\"".str_replace("%%offset%%",$o,$url)."\" alt=\"(Ctl/Alt-p)\" title=\"(Alt-p)\" accesskey=\"p\">"._l("Previous")."</a> ";
  } else {
    echo _l("Previous")." ";
  }

  if ($total>(2*$count)) { // On n'affiche le pager central (0 1 2 ...) s'il y a au moins 2 pages.
    echo " - ";
    if (($total<($count*10)) && ($total>$count)) {  // moins de 10 pages : 
      for($i=0;$i<$total/$count;$i++) {
	$o=$i*$count;
	if ($offset==$o) {
	  echo $i." "; 
	} else {
	  echo "<a href=\"".str_replace("%%offset%%",$o,$url)."\">$i</a> ";
	}
      }
    } else { // Plus de 10 pages, on affiche 0 1 2 , 2 avant et 2 après la page courante, et les 3 dernieres
      for($i=0;$i<=2;$i++) {
	$o=$i*$count;
	if ($offset==$o) {
	  echo $i." "; 
	} else {
	  echo "<a href=\"".str_replace("%%offset%%",$o,$url)."\">$i</a> ";
	}
      }
      if ($offset>=$count && $offset<($total-2*$count)) { // On est entre les milieux ...
	// On affiche 2 avant jusque 2 après l'offset courant mais sans déborder sur les indices affichés autour
	$start=max(3,intval($offset/$count)-2);
	$end=min(intval($offset/$count)+3,intval($total/$count)-3);
	if ($start!=3) echo " ... ";
	for($i=$start;$i<$end;$i++) {
	  $o=$i*$count;
	  if ($offset==$o) {
	    echo $i." "; 
	  } else {
	    echo "<a href=\"".str_replace("%%offset%%",$o,$url)."\">$i</a> ";
	  }
	}
	if ($end!=intval($total/$count)-3) echo " ... ";
      } else {
	echo " ... ";
      }
      for($i=intval($total/$count)-3;$i<$total/$count;$i++) {
	$o=$i*$count;
	if ($offset==$o) {
	  echo $i." "; 
	} else {
	  echo "<a href=\"".str_replace("%%offset%%",$o,$url)."\">$i</a> ";
	}
      }
    echo " - ";
    } // More than 10 pages?
  }
  // Shall-we show the next page link ?
  if ($offset+$count<$total) {
    $o=$offset+$count;
    echo "<a href=\"".str_replace("%%offset%%",$o,$url)."\" alt=\"(Ctl/Alt-s)\" title=\"(Alt-s)\" accesskey=\"s\">"._l("Next")."</a> ";
  } else {
    echo _l("Next")." ";
  }
  echo $after;
}

function input($name, $label, $type, $value) {
  if (!empty($label))
    echo '<label class="associate" for="' . $name . '">' . $label . '</label>' . "\n";
  switch ($type) {
  case 'text':
  case 'password':
    echo '<input type="' . $type . '" name="' . $name . '" id="' . $name . '" value="' . htmlentities($value, ENT_QUOTES, 'UTF-8') . '" /><br />';
    break;
  case 'checkbox':
    $checked = ($value == 'on' || $value == '1') ? 'checked="checked" ' : '';
    echo '<input type="' . $type . '" name="' . $name . '" id="' . $name . '" ' . $checked . '/><br />';
    break;
  case 'textarea':
    echo '<textarea name="' . $name . '" id="' . $name . '">' . $value . '</textarea><br />';
    break;
  }
}

function check_plain($text) {
  //return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
  return htmlentities($text, ENT_QUOTES, 'UTF-8');
}

function html_field($type, $name, $label = '', $value = '', $possible_values = array(), $options = array()) {
  static $form_id_prefix = 'form_';

  $html_field = '';
  $field_id = '';
  if (!empty($label)) {
    $html_field .= '<label class="associate" for="' . $form_id_prefix . $name . '">' . $label . '</label>' . "\n";
    $field_id = ' id="' . $form_id_prefix . $name . '"';
  }

  if (!empty($options['class']))
    $class = ' class="' . $options['class'] . '"';

  if (!empty($options['style']))
    $style = ' style="' . $options['style'] . '"';

  switch ($type) {
  case 'text':
    $html_field .= '<input type="' . $type . '" name="' . $name . '"' . $field_id . ' value="' . check_plain($value) . '" />';
    break;

  case 'password':
    $html_field .= '<input type="' . $type . '" name="' . $name . '"' . $field_id . ' />';
    break;

  case 'textarea':
    $html_field .= '<textarea name="' . $name . '"' . $field_id . $class . ' '. $style .'>' . check_plain($value) . '</textarea>';
    break;

  case 'checkbox':
    $value = ($value == 'on' || $value == '1' || $value == true);
    $checked = ($value == true) ? ' checked="checked"' : '';
    $html_field .= '<input type="' . $type . '" name="' . $name . '"' . $field_id . $checked . ' '. $style . ' />';
    break;

  case 'select':
    /*
     * Note:
     *   '0' == 0 : true :)
     *   '' == 0 : true :(
     *   '0' === 0 : false :(
     *   '0' === (string)0 : true :)
     *   '' === (string)0 : false :)
     */
    $keys = array_keys($possible_values);
    if (count($possible_values) <= 3 && !empty($possible_values[$keys[0]])) {
      $html_field .= '<div class="select"' . $field_id . '>';
      foreach ($possible_values as $k => $v) {
	$checked = ((string)$k === $value) ? ' checked="checked"' : '';
	$radio_id = $form_id_prefix . '_' . $name . '_' . $k;
	$html_field .= '<input type="radio" id="' . $radio_id . '" name="' . $name . '" value="' . $k . '"' . $checked . ' /> ' .
	  '<label for="' . $radio_id . '">' . $v . '</label>';
      }
      $html_field .= '</div>';
    }
    else {
      $html_field .= '<select name="' . $name . '"' . $field_id . '>';
      if (!empty($options['add_empty_entry']) && $options['add_empty_entry'] === true)
	$html_field .= '<option value=""></option>';
      foreach ($possible_values as $k => $v) {
	$selected = ((string)$k === $value) ? ' selected="selected"' : '';
	$html_field .= '<option value="' . $k . '"' . $selected . '>' . $v . '</option>';
      }
      $html_field .= '</select>';
    }
    break;

  case 'select-multiple':
    if (true) {
      $html_field .= '<div class="input-select-multiple"' . $field_id . ' '. $style . '>';
      foreach ($possible_values as $k => $v) {
	if (is_array($value))
	  $checked = (in_array((string)$k, $value)) ? ' checked="checked"' : '';
	$optid = $form_id_prefix . $name . '_' . $k;
	$html_field .= '<input type="checkbox"' .
	  ' name="' . $name . '[]"' . ' id="' . $optid . '"' .
	  ' value="' . $k . '"' . $checked . ' /> ';
	$html_field .= '<label for="' . $optid . '">' . $v . '</label><br />';
      }
      $html_field .= '</div>';
    }
    else {
      $html_field .= '<select name="' . $name . '[]"' . $field_id . ' multiple="multiple">';
      foreach ($possible_values as $k => $v) {
	$selected = ($k == $value) ? ' selected="selected"' : '';
	$html_field .= '<option value="' . $k . '"' . $selected . '>' . $v . '</option>';
      }
      $html_field .= '</select>';
    }
    break;
  }

  if (!empty($options['details']))
    $html_field .= '<span class="field-details">' . $options['details'] . '</span>';

  $html_field .= '<br />';

  return $html_field;
}

function url($path) {
  if ($path == '')
    return '';
  elseif (substr($path, 0, 7) == 'http://' || substr($path, 0, 8) == 'https://') {
    return $path;
  }
  else {
    if ($path{0} == '/')
      $path = substr($path, 1);
    return BASE_URL . $path;
  }
}

function l($text, $path) {
  return '<a href="' . url($path) . '">' . $text . '</a>';
}

function html_list($title = '', $elements = array(), $text_if_empty = '', $type = 'ul') {
  $html_list = '<div class="list">';
  if (!empty($title)) {
    if (substr($title, 0, 2) != '<h')
      $html_list .= '<h3>' . $title . '</h3>';
    else
      $html_list .= $title;
  }

  if (empty($elements)) {
    if (!empty($text_if_empty))
      $html_list .= '<p>' . $text_if_empty . '</p>';
    else
      return '';
  }
  else {
    switch ($type) {
    case 'dl':
      $html_list .= '<dl>';
      foreach ($elements as $key => $val) {
	$html_list .= '<dt>' . $key . '</dt>';
	$html_list .= '<dd>' . $val . '</dd>';
      }
      $html_list .= '</dl>';
      break;
    case 'ul':
    default:
      $html_list .= '<ul>';
      foreach ($elements as $el) {
	$html_list .= '<li>' . $el . '</li>';
      }
      $html_list .= '</ul>';
    }
  }

  $html_list .= '</div>';

  return $html_list;
}

function html_assoc_list($title = '', $elements = array(), $text_if_empty = '') {
  return html_list($title, $elements, $text_if_empty, 'dl');
}

function html_table_list($headers = array(), $content = array(), $text_if_empty = "") {
  if (empty($content) && !empty($text_if_empty))
    return '<p>' . $text_if_empty . '</p>';

  $html_table_list = '<table class="list">';
  $html_table_list .= '<colgroup>';
  foreach ($headers as $key => $name) {
    $html_table_list .= '<col class="col_' . $key . '" />';
  }
  $html_table_list .= '</colgroup>';
  $html_table_list .= '<thead>';
  $html_table_list .= '<tr>';
  foreach ($headers as $name) {
    $html_table_list .= '<th>' . $name . '</th>';
  }
  $html_table_list .= '</tr>';
  $html_table_list .= '</thead>';
  $html_table_list .= '<tbody>';
  foreach ($content as $line_infos) {
    $html_table_list .= '<tr>';
    foreach ($line_infos as $key => $info) {
      if (isset($headers[$key])) {
	$html_table_list .= '<td class="col_' . $key . '">' . $info . '</td>';
      }
    }
    $html_table_list .= '</tr>';
  }
  $html_table_list .= '</tbody>';
  $html_table_list .= '</table>';

  return $html_table_list;
}

<?php
if (!defined('MONK_VERSION')) exit('Access is no allowed.');

function dump($mix){
    echo '<pre>';
    var_dump($mix);
    echo '</pre>';
}

function noslashes($s) {
  if(!get_magic_quotes_gpc())
    return $s;
  return stripslashes($s);
}






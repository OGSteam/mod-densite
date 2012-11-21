<?php

if (!defined('IN_SPYOGAME'))
{
  exit('Hacking attempt');
}

$mod_folder = 'densite';

if (!install_mod($mod_folder))
{
  echo '<script>alert(\'Une erreur est survenue pendant l\'installation du module "Densité".\')</script>';
}

?>

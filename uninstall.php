<?php

if (!defined('IN_SPYOGAME'))
{
  exit('Hacking Attempt');
}

global $db, $table_prefix;

$mod_uninstall_name = 'densite';
uninstall_mod ($mod_uninstall_name, $mod_uninstall_table);

?>

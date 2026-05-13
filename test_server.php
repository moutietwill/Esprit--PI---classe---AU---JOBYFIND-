<?php
if (in_array('mod_rewrite', apache_get_modules())) {
    echo "mod_rewrite is ENABLED";
} else {
    echo "mod_rewrite is DISABLED";
}
echo "<br>REQUEST_URI: " . $_SERVER['REQUEST_URI'];
echo "<br>SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'];
echo "<br>PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'N/A');

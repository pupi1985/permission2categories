<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
    header('Location: ../../');
    exit;
}

qa_register_plugin_overrides('p2c_overrides.php');
qa_register_plugin_layer('p2c_layer.php', 'Permissions 2 Categories Layer');
qa_register_plugin_module('process', 'p2c-module.php', 'p2c_category_permission', 'Permissions2Categories');

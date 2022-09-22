<?php

require_once '../../../library/load_ini.php';
require_once '../../../library/json_parser.php';
require_once '../../../mask/mask_editor.php';

try {
    define("DATA_PATH", get_data_by_type_path("parameters"));
    if (!DATA_PATH) {
        throw new Exception("Data type path is not set correctly");
    }
    define("PATH_TO_DATA_FILE", get_most_recent_json(DATA_PATH));
    if(!PATH_TO_DATA_FILE) {
        throw new Exception('Data path is not set correctly');
    }
    echo mask_to_html(PATH_TO_DATA_FILE, 2);
} catch (Exception $e) {
    echo $e->getMessage();
}
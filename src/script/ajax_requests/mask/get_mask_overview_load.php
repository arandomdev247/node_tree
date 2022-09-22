<?php

require_once '../../../mask/mask_overview_editor.php';

require_once '../../../library/load_ini.php';
require_once '../../../library/json_parser.php';

try
{
    define("DATA_PATH", get_data_path_from_forti_requests());
    if (!DATA_PATH) {
        throw new Exception("Data type path is not set correctly");
    }
    define("PATH_TO_DATA_FILE", get_most_recent_json(DATA_PATH));
    if (!PATH_TO_DATA_FILE) {
        throw new Exception('Data path is not set correctly');
    }

    $data = json_decode(file_get_contents(PATH_TO_DATA_FILE), true);
    echo load_mask_overview($data);
}
catch (Exception $e)
{
    echo $e->getMessage();
}

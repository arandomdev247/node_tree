<?php

/**
 * Fichier récupérant les données du fichier JSON
 */

require_once '../../../library/data_tree.php';
require_once '../../../library/json_parser.php';
require_once '../../../library/load_ini.php';

try {
    define("DATA_PATH", get_data_path_from_forti_requests());
    define("PATH_TO_DATA_FILE", get_most_recent_json(DATA_PATH));
    if(!PATH_TO_DATA_FILE) {
        throw new Exception('Data path is not set correctly');
    }
    echo show_all_data_tree_in_detail(PATH_TO_DATA_FILE);
} catch (Exception $e) {
    echo $e->getMessage();
}

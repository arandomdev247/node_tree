<?php

/**
 * Fichier récupérant le nombre de sites présent dans le fichier JSON
 */

require_once '../../../library/data_tree.php';

try {
    define("DATA_PATH", get_data_path_from_forti_requests());
    define("PATH_TO_DATA_FILE", get_most_recent_json(DATA_PATH));
    if (PATH_TO_DATA_FILE == "") {
        throw new Exception('Data path is not set correctly');
    }
    else {
        echo(count_children(PATH_TO_DATA_FILE));
    }
}
catch (Exception $e) {
    echo($e->getMessage());
}
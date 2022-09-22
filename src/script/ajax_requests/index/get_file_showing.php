<?php

/**
 * Fichier récupérant le nom du fichier JSON
 */

require_once '../../../library/data_tree.php';
require_once '../../../library/load_ini.php';

try {
    define("DATA_PATH", get_data_path_from_forti_requests());
    define("PATH_TO_DATA_FILE", get_data_path_from_forti_requests(DATA_PATH));
    if (PATH_TO_DATA_FILE == "") {
        throw new Exception('Data path is not set correctly');
    }
    else {
        echo(basename(get_most_recent_json(PATH_TO_DATA_FILE)));
    }
} catch (Exception $e) {
    echo('Erreur: ' . $e->getMessage());
}
<?php

function count_children(string $data = null): int
{
    if ($data === null) {
        return 0;
    }

    $data_string = file_get_contents($data);
    $data = json_decode($data_string, true);
    return count($data['children']);
}

function get_data_from_index(array $data, int $child_num)
{
    return $data['children'][$child_num];
}

function get_max_dept($data): int
{
    $max_depth = 0;
    if(isset($data['children']))
    {
        foreach ($data['children'] as $child)
        {
            $depth = get_max_dept($child);
            if ($depth > $max_depth)
            {
                $max_depth = $depth;
            }
        }
    }
    return $max_depth + 1;
}

/**
 * Retourne le fichier JSON le plus récent dans le dossier
 * @param string $path_to_dir
 * @return string
 * @throws Exception
 */
function get_most_recent_json(string $path_to_dir=""): string
{
    if ($path_to_dir === "") {
        $path_to_dir = '../../data/';
    }
    $files = scandir($path_to_dir, SCANDIR_SORT_DESCENDING);
    $files = array_diff($files, array('.', '..'));
    if ($files === []) {
        throw new Exception('Aucun fichier JSON trouvé dans le dossier : ' . $path_to_dir);
    }
    $most_recent = $files[0];
    if ($most_recent === null)
    {
        throw new Exception('Aucun fichier JSON trouvé dans le dossier : ' . $path_to_dir);
    }
    return realpath(DATA_PATH . '/' . $most_recent);
}
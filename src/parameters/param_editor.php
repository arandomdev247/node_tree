<?php

require_once __DIR__ . '/../library/json_parser.php';
require_once __DIR__ . '/Parameter.php';



function save_param(string $param_file, array $arr_param): bool
{
    $param_file = __DIR__ . '/data/' . basename($param_file, 'data.json') . 'config.json';
    $data = json_encode($arr_param, JSON_PRETTY_PRINT);
    return (bool)file_put_contents($param_file, $data);
}

function load_param(string $param_file): array
{
    return file_exists($param_file) ? json_decode(file_get_contents($param_file), true) : [];
}

function refresh_param(string $param_file): bool
{
    $arr_param = get_param_v2($param_file);
    try{
        if(!save_param($param_file, $arr_param)){
            throw new Exception('Erreur lors de l\'enregistrement des paramètres');
        }
    } catch (Exception $e) {
        echo $e->getMessage();
        return false;
    }
    return true;
}

/**
 *
 * @param string $param_file
 * @return void
 */
function show_param(string $param_file)
{
    $result = '<table id="table-parameters">';

    try {
        $arr_param = load_param(get_most_recent_json(dirname($param_file)));
    } catch (Exception $e) {
        echo $e->getMessage();
        return;
    }

    if (empty($arr_param)){

        try {
            echo get_most_recent_json(dirname($param_file));
        } catch (Exception $e) {
            echo $e->getMessage();
            return;
        }

        return;

        /*
        $arr_param = get_param_v2($param_file);
        if (!save_param($param_file, $arr_param)){
            echo 'Erreur lors de l\'enregistrement des paramètres';
            return;
        */
        }

    $result .= '<thead><tr>';
    $result .= '<th>Hôte</th>';
    $result .= '<th>Virtual Server Pool</th>';
    $result .= '<th>Virtual Server</th>';
    $result .= '<th>Real Server Pool</th>';
    $result .= '<th>Real Server (Nodes)</th>';
    $result .= '</tr></thead>';
    $result .= '<tbody><tr>';

    $depth = 'A';
    $id = 0;

    //TODO : Filtrer le tableau pour ne pas afficher les array inutiles
    //TODO : Faire un fichier de config qui conserve les paramètres

    foreach ($arr_param as $param) {
        if (count($param) > 1) {
            if ($param[0] != 'name') {
                $result .= "<td id=$depth>";
                foreach ($param as $item) {
                    $id++;

                    $result .= '<div class="form-check form-switch">';
                    $result .= "<input class=form-check-input type=checkbox id=chk_$depth$id data=$item>";
                    $result .= "<label class=form-check-label for=chk_$depth$id>$item";
                    $result .= '</label></div>';
                }
                $result .= '</td>';
                $depth++;
                $id = 0;
            }
        }
    }
    $result .= '</tr></tbody>';
    echo $result;

    /*
     * TODO : Améliorer la vitesse de chargement de la page
     */
}

/**
 * Function principale qui récupère et filtre les paramètres
 * @param string $param_file
 * @return array
 */
function get_param_v2(string $param_file): array
{
    $param_raw = json_decode(file_get_contents($param_file), true);
    $arr_param = data_raw_to_parameters_light($param_raw);

    return get_param_by_level_v3($arr_param);
}

function data_raw_to_parameters_light_v3(array $param_raw, int $level=0, array $original_array = array()): array
{

    $arr_param = array();
    $level++;
    foreach ($param_raw as $key => $value) {
        if (is_array($value)) {
            $arr_param = data_raw_to_parameters_light_v3($value, $level, $arr_param);

        } else {
            if (!isset($original_array[$level])
            ) {
                $arr_param[$level][] = new Parameter($key, $value, $level);
            }
            else if (!is_key_in_array_param($key, $original_array[$level], $level)) {
                $arr_param[$level][] = new Parameter($key, $value, $level);
            }
        }
    }
    if (!isset($arr_param[$level])) {
        $arr_param[$level] = [];
    }

    if (!isset($original_array[$level]) && count($arr_param[$level]) > 0) {
        $original_array[$level] = array_merge($original_array, $arr_param[$level]);
    }
    else if (isset($arr_param[$level]) && count($arr_param[$level]) > 0) {
        $original_array[$level] = custom_array_merge($original_array[$level], $arr_param[$level]);
    }

    return $original_array;
}

/**
 * Crée les paramètres des différents niveaux et les met dans une liste, prévient la création de doublons
 * @param array $param_raw
 * @param int $level
 * @param array $original_array
 * @return array
 */
function data_raw_to_parameters_light(array $param_raw, int $level=0, array $original_array = array()): array
{
    $arr_param = [];
    $level++;

    if (count($param_raw) < 2) {
        if(array_key_exists('name', $param_raw) && !is_key_in_array_param('name', $original_array, $level))
            $arr_param[] = new Parameter('name', $param_raw['name'], $level);
        elseif (!array_key_exists('name', $param_raw))
            $arr_param = data_raw_to_parameters_light($param_raw[0], --$level, $arr_param);

    }
    else {
        foreach ($param_raw as $key => $value) {
            if (is_array($value)) {
                $arr_param = data_raw_to_parameters_light($value, $level, $arr_param);

            } else {
                if (!is_key_in_array_param($key, $original_array, $level)) {
                    $arr_param[] = new Parameter($key, $value, $level);
                }
            }
        }
    }
    return custom_array_merge($original_array, $arr_param);
}

function custom_array_merge(array $arr_origin, array $arr_merging): array
{
    $arr_result = $arr_origin;
    foreach ($arr_merging as $item) {
        if (!is_key_in_array_param($item->getParameter(), $arr_origin, $item->getLevel())) {
            $arr_result[] = $item;
        }
    }
    return $arr_result;
}

function get_param_by_level_v3(array $arr_param): array
{
    $arr_result = array(array());
    foreach ($arr_param as $param) {
        $arr_result[$param->getLevel()][] = $param->getParameter();
    }
    return array_filter($arr_result);
}

function get_max_depth_param_list(array $param_list): int
{
    $max_depth = 0;
    foreach ($param_list as $param) {
        if ($param->getLevel() > $max_depth) $max_depth = $param->getLevel();
    }
    return $max_depth;
}

/**
 * Crée un tableau de paramètres à partir d'un tableau de données brutes
 * @param array $param_raw
 * @param int $level
 * @return array
 */
function data_raw_to_parameters(array $param_raw, int $level=0): array
{
    $arr_param = [];
    $level++;
    foreach ($param_raw as $key => $value) {
        if (is_array($value)) {
            $arr_param[] = data_raw_to_parameters($value, $level);
        } else {

            $arr_param[] = new Parameter($key, $value, $level);
        }
    }
    return $arr_param;
}

/**
 * Navigue dans les listes imbriquées et récupère les paramètres en function de leur niveau d'imbrication ($level)
 * S'il y a un doublon sur le même niveau, on ne prend pas en compte le doublon
 * @param array $arr_param
 * @param int $level
 * @return array
 */
function get_param_by_level(array $arr_param, int $level=0): array
{
    $arr_param_level = array();

    array_walk_recursive($arr_param, function($value) use (&$arr_param_level, &$level) {
        if ($value->getLevel() == $level && !is_key_in_array_param($value->getParameter(), $arr_param_level)) {
            $arr_param_level[] = $value;
        }
    });

    return $arr_param_level;
}

/**
 * Vérifie si une clé est déjà présente dans un tableau de paramètres
 * @param string $key
 * @param array $arr_param
 * @param int $level
 * @return bool
 */
function is_key_in_array_param(string $key, array $arr_param, int $level=-1) : bool
{
    if ($level >= -1) {
        foreach ($arr_param as $value) {
            if ($value->getParameter() == $key &&
                $value->getLevel() == $level) {
                return true;
            }
        }
    }
    else {
        foreach ($arr_param as $value) {
            if ($value->getParameter() == $key) {
                return true;
            }
        }
    }
    return false;
}

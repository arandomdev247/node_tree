<?php

require_once __DIR__ . '/../mask/mask_editor.php';

require_once __DIR__ . '/../library/data_tree.php';


function load_mask_overview(array $data): string
{
    $all_mask_arr = array();

    for ($i = 1; $i < 4; $i++){
        $all_mask_arr[$i] = load_mask_overview_data(strval($i));
    }
    return mask_overview_to_html($all_mask_arr, $data);
}


function mask_overview_to_html(array $all_mask_arr, $data): string
{
    $num_host = 1;
    $all_host = get_all_host_name($data);
    $all_mask_error = generate_error_finder_array($data, $all_mask_arr);

    $mask_summary_counter = [0, 0, 0];
    $mask_summary_data = [null, null, null];

    $mask_html = '<table id="table-overview">';
    $mask_html .= '<thead id="thead-overview">';
    $mask_html .= '<tr>';
    $mask_html .= '<th>N°</th>';
    $mask_html .= '<th>Masque N°1</th>';
    $mask_html .= '<th>Masque N°2</th>';
    $mask_html .= '<th>Masque N°3</th>';
    $mask_html .= '<th>Hôte</th>';
    $mask_html .= '</tr>';
    $mask_html .= '</thead>';

    $mask_html .= '<tbody id="tbody-overview">';
    foreach ($all_host as $host) {

        $mask_summary_data[0] = set_mask_result($all_mask_error[1], 1, $host);
        if ($mask_summary_data[0] != '')
            $mask_summary_counter[0]++;

        $mask_summary_data[1] = set_mask_result($all_mask_error[2], 2, $host);
        if ($mask_summary_data[1] != '')
            $mask_summary_counter[1]++;

        $mask_summary_data[2] = set_mask_result($all_mask_error[3], 3, $host);
        if ($mask_summary_data[2] != '')
            $mask_summary_counter[2]++;


        $mask_html .= '<tr>';
        $mask_html .= '<td>' . $num_host++ . '</td>';
        $mask_html .= '<td>' . $mask_summary_data[0] . '</td>';
        $mask_html .= '<td>' . $mask_summary_data[1] . '</td>';
        $mask_html .= '<td>' . $mask_summary_data[2] . '</td>';
        $mask_html .= '<td class="table-overview-host">' . $host . '</td>';
        $mask_html .= '</tr>';
    }
    $mask_html .= '</tbody>';
    $mask_html .= '</table>';

    return overview_error_counter($mask_summary_counter) . $mask_html;
}

function overview_error_counter(array $mask_summary_counter):string
{

    $html_summary_counter = '<div id="mask-overview-count">';
    $html_summary_counter .= '<p>';
    $html_summary_counter .= 'Nombre de masques avec erreur total : <b>' . ($mask_summary_counter[0] + $mask_summary_counter[1] + $mask_summary_counter[2]) . '</b><br>';
    $html_summary_counter .= 'Nombre d\'erreurs détaillé :';
    $html_summary_counter .= '<span class="summary-errors">' . get_circle_by_id(1) .  $mask_summary_counter[0] . '</span>';
    $html_summary_counter .= '<span class="summary-errors">' . get_circle_by_id(2) .  $mask_summary_counter[1] . '</span>';
    $html_summary_counter .= '<span class="summary-errors">' . get_circle_by_id(3) .  $mask_summary_counter[2] . '</span>';
    $html_summary_counter .= '</p>';

    $html_summary_counter .= '<hr>';
    return  $html_summary_counter;
}

function get_circle_by_id(int $id)
{
    $img_src = 'css/images/';
    $img_name = 'err.jpg';

    switch($id){
        case 1:
            $img_name = 'blue_circle.jpg';
            break;
        case 2:
            $img_name = 'red_circle.jpg';
            break;
        case 3:
            $img_name = 'green_circle.jpg';
            break;
    }

    $img_full = $img_src . $img_name;

    $img_src_from_php = realpath(__DIR__ . '/../../' . $img_src . $img_name);
    if (file_exists($img_src_from_php)){
        return '<img class="circles" src="' . $img_full . '" alt="cercle" title="' . pathinfo($img_full, PATHINFO_FILENAME) . '" /> ';
    }
    else
        return pathinfo($img_full, PATHINFO_FILENAME) . ' : ';

}

function set_mask_result(array $mask_error, int $id, string $host): string
{
    $img_src = 'css/images/';

    switch($id){
        case 1:
            $img_src .= 'blue_circle.jpg';
            break;
        case 2:
            $img_src .= 'red_circle.jpg';
            break;
        case 3:
            $img_src .= 'green_circle.jpg';
            break;
    }

    $img_src_from_php = realpath(__DIR__ . '/../../' . $img_src);

    if (file_exists($img_src_from_php)) {
        if (is_host_in_error($mask_error, $host))
            return '<img class="circles" src="' . $img_src . '" alt="cercle" title="' . $host . '" />';
        else
            return '';
    }
    else {
        if (is_host_in_error($mask_error, $host))
            return 'True';
        else
            return '';
    }

}

function is_host_in_error(array $mask_arr, string $host): bool
{
    if (in_array($host, $mask_arr))
        return true;
    return false;
}



function generate_error_finder_array(array $data, array $all_mask_arr):array
{
    $all_mask_error = array();

    foreach ($all_mask_arr as $key => $mask) {
        try {
            $all_mask_error[$key] = error_finder_for_summary($data, $mask);
        }
        catch (Exception $e) {
            $all_mask_error[$key] = array();
        }
    }
    return $all_mask_error;
}

/**
 * Renvois un array contenant un bool pour chaque hôte si une erreur est détectée avec le masque
 * @throws Exception
 */
function error_finder_for_summary(array $data, array $mask = array()): array
{
    $final_result = array();
    $host_id = 1;

    $ignore_blanks = $mask['ignore_blanks'] ?? false; // Si ignore_blanks est défini, on ignore les valeurs vides
    unset($mask['ignore_blanks']); // On supprime ignore_blanks du mask pour ne pas l'utiliser dans le filtre

    foreach ($mask as $key => $value)
        if ($value['checked']) {
            $level = str_replace('chkval_', '', $key)[0];
            if (strlen($level) != 1)
                throw new Exception("Erreur dans le masque");
            $level = ord($level) - ord('A');
            if ($level < 0)
                throw new Exception("Erreur dans le masque");

            $final_result = array_merge($final_result,
                error_finder_recurse_for_summary($data, $ignore_blanks, $value['value'], $value['name'], $host_id++, $level));
        }

    return $final_result;
}

/*
function error_finder_recurse_for_summary(array $data, bool $ignore_blanks = false, string $value_to_filter = '',
                                          string $key_to_filter = '', int $level=0, int $host_id=0, int $current_level=0,
                                          array $result = array()): array
{
    $new_result = $result;

    if (array_key_exists('name', $data) && count($data) <= 2) {
        foreach ($data as $key => $val)
            if (is_array($val))
                return (array_merge($new_result, error_finder_recurse_for_summary($val, $ignore_blanks, $value_to_filter, $key_to_filter, $level, $host_id, $current_level, $result)));
    }
    elseif(!array_key_exists('name', $data)) {
        foreach ($data as $key => $val)
            $new_result = array_merge($new_result, error_finder_recurse_for_summary($val, $ignore_blanks, $value_to_filter, $key_to_filter, $level, $host_id, $current_level, $result));
        return $new_result;
    }
    else
    {
        foreach ($data as $key => $val) {
            if ($current_level == 0) {
                $host_id++;
            }

            if (is_array($val) && $current_level < $level) {
                $new_result = array_merge($new_result, error_finder_recurse_for_summary($val, $ignore_blanks, $value_to_filter, $key_to_filter, $level, $host_id, ++$current_level, $result));
            } elseif ($current_level == $level) {
                if ($key === $key_to_filter &&
                    !(strval($val) === $value_to_filter) &&
                    !($ignore_blanks && is_blank($val))) {
                    echo $val . ' | ' . gettype($val);
                    $new_result[$val] = true;
                }
                //return $new_result;
            }
        }
        return $new_result;
    }
    //$new_result[$host_id] ? $new_result[$host_id] = true : $new_result[$host_id] = false;
    return $new_result;
}
*/

function error_finder_recurse_for_summary(array $data, bool $ignore_blanks = false, string $value_to_filter = '',
                                          string $key_to_filter = '', int $host_id=0, int $level=0, int $current_level=0,
                                          array $result = array(), string $hostname = 'err'): array
{
    $new_result = $result;


    if (array_key_exists('name', $data) && count($data) <= 2) {
        foreach ($data as $key => $val)
            if (is_array($val))
                return (array_merge($new_result, error_finder_recurse_for_summary($val, $ignore_blanks, $value_to_filter, $key_to_filter, $host_id, $level, $current_level, $result, $hostname)));
    }
    elseif(!array_key_exists('name', $data)) {
        foreach ($data as $key => $val)
            $new_result = array_merge($new_result, error_finder_recurse_for_summary($val, $ignore_blanks, $value_to_filter, $key_to_filter, $host_id, $level, $current_level, $result, $hostname));
        return $new_result;
    }
    else
    {
        foreach ($data as $key => $val) {

            if (!is_array($val) && array_key_exists('name', $data) && $current_level == 0) {
                $hostname = $data['name'];
            }


            if (is_array($val) && $current_level < $level) {
                $new_result = array_merge($new_result, error_finder_recurse_for_summary($val, $ignore_blanks, $value_to_filter, $key_to_filter, $host_id, $level, ++$current_level, $result, $hostname));
            } elseif ($current_level == $level) {
                if ($key === $key_to_filter &&
                    !(strval($val) === $value_to_filter) &&
                    !($ignore_blanks && is_blank($val))) {
                    $new_result[] = $hostname;
                    return $new_result;
                }
                //return $new_result;
            }
        }
        return $new_result;
    }

    return $new_result;
}


function load_mask_overview_data(string $name): array
{
    $mask_arr = array();

    $mask_str = load_json_mask($name);
    if ($mask_str != '' && $mask_str != null) {
        $mask_arr = json_decode($mask_str, true);
    }

    return($mask_arr);
}
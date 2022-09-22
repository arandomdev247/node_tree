<?php

require_once '../../../mask/mask_editor.php';

$data = json_decode(stripcslashes($_POST['data']), true);
$id = json_decode(stripcslashes($_POST['id']), true);
$ignore_blanks = json_decode(stripcslashes($_POST['ignore_blanks']), true);


if (empty($data) || empty($id) || !is_bool($ignore_blanks)) {
    echo 'false';
}
else
    echo generate_json($data, $id, $ignore_blanks);
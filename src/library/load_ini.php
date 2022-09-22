<?php

function get_data_path_from_forti_requests(): string
{
    $data_path = __DIR__;
    $data_path .= '/../script/forti_requests/data/json/';
    $data_path = preg_replace('#/+#','/',$data_path);
    return realpath($data_path);
}

function get_data_by_type_path(string $type): string
{
    $data_path = __DIR__;
    $data_path .= "/../" . $type . "/data/";
    $data_path = preg_replace('#/+#','/',$data_path);
    return realpath($data_path);
}
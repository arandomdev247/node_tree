<?php


$command = escapeshellcmd('python ../script/forti_requests/main.py -m');
$output = shell_exec($command);

echo $output;
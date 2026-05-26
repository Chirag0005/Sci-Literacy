<?php

$payload = [
    'action' => 'find',
    'collection' => 'questions',
    'filter' => [],
    'limit' => 2
];

$base64 = base64_encode(json_encode($payload));
$cmd = 'node database/mongo_cli.js ' . $base64;
$output = shell_exec($cmd);

echo "Command executed: $cmd\n";
echo "Output: \n$output\n";

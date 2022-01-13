<?php
set_time_limit(0);

$output1 = shell_exec('ls');
$output2 = exec('ls',$output3);
var_dump($output1);
var_dump($output2);
var_dump($output2);
exit;
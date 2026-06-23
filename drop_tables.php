<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$db = Illuminate\Support\Facades\DB::connection();
$tables = $db->select('SHOW TABLES');

$db->statement('SET FOREIGN_KEY_CHECKS=0;');
foreach($tables as $table) {
    $tableName = (array)$table;
    $tableName = array_values($tableName)[0];
    $db->statement("DROP TABLE IF EXISTS `$tableName`");
}
$db->statement('SET FOREIGN_KEY_CHECKS=1;');

echo "All tables dropped.\n";

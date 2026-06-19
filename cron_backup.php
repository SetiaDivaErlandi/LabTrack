<?php
$host     = "localhost";
$username = "root";
$password = ""; 
$dbname   = "labtrack";

$backup_dir = __DIR__ . '/backups/';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

$file_name   = 'labtrack_auto_scheduled_' . date('Y-m-d_H-i-s') . '.sql';
$backup_file = $backup_dir . $file_name;

$conn = mysqli_connect($host, $username, $password, $dbname);
if (!$conn) {
    file_put_contents(__DIR__ . '/task_scheduler_log.txt', "[" . date('Y-m-d H:i:s') . "] FAILED: Connection error.\n", FILE_APPEND);
    exit();
}

$tables = array();
$result = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

$sql_content = "-- LabTrack Database Automatic Scheduled Backup\n\n";
foreach ($tables as $table) {
    if (strpos($table, 'view_') === 0) continue;
    $result = mysqli_query($conn, "SELECT * FROM $table");
    $num_fields = mysqli_num_fields($result);
    
    $sql_content .= "DROP TABLE IF EXISTS `$table`;\n";
    $row2 = mysqli_fetch_row(mysqli_query($conn, "SHOW CREATE TABLE $table"));
    $sql_content .= $row2[1] . ";\n\n";
    
    for ($i = 0; $i < $num_fields; $i++) {
        while ($row = mysqli_fetch_row($result)) {
            $sql_content .= "INSERT INTO `$table` VALUES(";
            for ($j = 0; $j < $num_fields; $j++) {
                $row[$j] = addslashes($row[$j]);
                if (isset($row[$j])) { $sql_content .= '"' . $row[$j] . '"'; } else { $sql_content .= 'NULL'; }
                if ($j < ($num_fields - 1)) { $sql_content .= ','; }
            }
            $sql_content .= ");\n";
        }
    }
    $sql_content .= "\n\n";
}

file_put_contents($backup_file, $sql_content);
file_put_contents(__DIR__ . '/task_scheduler_log.txt', "[" . date('Y-m-d H:i:s') . "] SUCCESS: " . $file_name . "\n", FILE_APPEND);
echo "Success";
?>
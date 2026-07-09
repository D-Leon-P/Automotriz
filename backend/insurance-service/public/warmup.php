<?php
header('Content-Type: application/json');
ini_set('max_execution_time', 300);

$status = opcache_get_status(false);
if ($status && isset($status['opcache_statistics']['num_cached_scripts']) && $status['opcache_statistics']['num_cached_scripts'] > 1000) {
    echo json_encode(['status' => 'already_warmed', 'compiled_files' => $status['opcache_statistics']['num_cached_scripts']]);
    exit;
}

$dirs = [
    __DIR__ . '/../app',
    __DIR__ . '/../config',
    __DIR__ . '/../routes',
    __DIR__ . '/../vendor',
];

$count = 0;
foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            if (@opcache_compile_file($file->getRealPath())) {
                $count++;
            }
        }
    }
}
echo json_encode(['status' => 'warmed', 'compiled_files' => $count]);

<?php
$dir = new RecursiveDirectoryIterator('c:/Users/vc/Desktop/multipanelx');
$ite = new RecursiveIteratorIterator($dir);
foreach($ite as $file) {
    if(pathinfo($file, PATHINFO_EXTENSION) === 'php' && strpos($file->getPathname(), 'test_extract') === false) {
        $content = file_get_contents($file);
        $content = preg_replace('/error_reporting\s*\(\s*E_ALL\s*\)\s*;\s*ini_set\s*\(\s*\'display_errors\'\s*,\s*1\s*\)\s*;\s*/i', '', $content);
        file_put_contents($file, $content);
    }
}
echo "Done";

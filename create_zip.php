<?php
$zip = new ZipArchive();
$filename = "c:/Users/vc/Desktop/multipanelx_deploy3.zip";

if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
    exit("cannot open <$filename>\n");
}

$sourceDir = "c:/Users/vc/Desktop/multipanelx";
$exclude = ['.git', '.codegraph', 'test_extract', 'test_extract2', 'cpanel.txt', 'deploy.ps1', 'upload_ftp.php', 'multipanelx_deploy.zip', 'multipanelx_deploy2.zip', 'multipanelx_deploy3.zip'];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($sourceDir) + 1));
    
    // Check exclude
    $skip = false;
    foreach ($exclude as $ex) {
        if (strpos($relativePath, $ex) === 0) {
            $skip = true;
            break;
        }
    }
    if ($skip) continue;

    if ($file->isDir()) {
        $zip->addEmptyDir($relativePath);
    } else {
        $zip->addFile($file->getPathname(), $relativePath);
    }
}

echo "Numfiles: " . $zip->numFiles . "\n";
echo "Status:" . $zip->status . "\n";
$zip->close();
echo "Zip created.\n";

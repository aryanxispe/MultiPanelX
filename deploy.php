<?php
$ftpServer = "ftp://ftp.myvipsite.fun";
$ftpUser = "nztvysxb123";
$ftpPass = "*wBXmgR11*X6z3";

$sourceDir = "c:/Users/vc/Desktop/multipanelx";
$exclude = ['.git', '.codegraph', 'test_extract', 'test_extract2', 'cpanel.txt', 'deploy.ps1', 'upload_ftp.php', 'multipanelx_deploy.zip', 'multipanelx_deploy2.zip', 'multipanelx_deploy3.zip', 'multipanelx_deploy.tar.gz', 'create_zip.php', 'deploy.php'];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if ($file->isDir()) continue;

    $localPath = $file->getPathname();
    $relativePath = str_replace('\\', '/', substr($localPath, strlen($sourceDir) + 1));
    
    // Check exclude
    $skip = false;
    foreach ($exclude as $ex) {
        if (strpos($relativePath, $ex) === 0) {
            $skip = true;
            break;
        }
    }
    if ($skip) continue;

    echo "Uploading $relativePath ...\n";
    $remoteUrl = "$ftpServer/public_html/" . ltrim($relativePath, '/');
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $remoteUrl);
    curl_setopt($ch, CURLOPT_USERPWD, "$ftpUser:$ftpPass");
    curl_setopt($ch, CURLOPT_UPLOAD, 1);
    curl_setopt($ch, CURLOPT_INFILE, fopen($localPath, 'r'));
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localPath));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, true);
    
    $result = curl_exec($ch);
    if ($result === false) {
        echo "Error uploading $relativePath: " . curl_error($ch) . "\n";
    }
    curl_close($ch);
}
echo "FTP Deployment complete.\n";

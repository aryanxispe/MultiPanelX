$ErrorActionPreference = "Stop"
$ftpServer = "ftp://ftp.myvipsite.fun/public_html/"
$ftpUser = "nztvysxb123"
$ftpPass = "*wBXmgR11*X6z3"
$sourceDir = "c:\Users\vc\Desktop\multipanelx"

$exclude = @(".git", ".codegraph", "test_extract", "test_extract2", "cpanel.txt", "deploy.ps1", "upload_ftp.php", "multipanelx_deploy.zip", "multipanelx_deploy2.zip", "multipanelx_deploy3.zip", "multipanelx_deploy.tar.gz", "create_zip.php", "deploy.php", "upload_all.ps1")

$files = Get-ChildItem -Path $sourceDir -Recurse -File
foreach ($f in $files) {
    $skip = $false
    $relPath = $f.FullName.Substring($sourceDir.Length + 1).Replace('\', '/')
    foreach ($ex in $exclude) {
        if ($relPath -match "^$ex") {
            $skip = $true
            break
        }
    }
    if ($skip) { continue }

    $remoteUrl = "$ftpServer$relPath"
    Write-Host "Uploading $relPath ..."
    
    # Run curl.exe to upload the file
    $args = @("-T", $f.FullName, $remoteUrl, "--user", "${ftpUser}:${ftpPass}", "--insecure", "--ftp-create-dirs", "-s")
    & curl.exe @args
}
Write-Host "FTP Upload Complete!"

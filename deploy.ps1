$ErrorActionPreference = "Stop"

$cpanelHost = "dns.myperfectdns.com"
$cpanelPort = "2083"
$cpanelUser = "nztvysxb123"
$cpanelToken = "GFBGJ39JLDLIP4FLHXCB0PFVHBAAZVN8"
$ftpServer = "ftp://ftp.myvipsite.fun"
$ftpUser = "nztvysxb123"
$ftpPass = "*wBXmgR11*X6z3"

$headers = @{
    "Authorization" = "cpanel ${cpanelUser}:${cpanelToken}"
}

# 1. Zip the files
Write-Host "Zipping files..."
$sourceDir = "c:\Users\vc\Desktop\multipanelx"
$zipPath = "c:\Users\vc\Desktop\multipanelx_deploy.zip"
if (Test-Path $zipPath) { Remove-Item $zipPath }

# Exclude unnecessary files
$exclude = @(".git", ".codegraph", "multipanelx_deploy.zip", "multipanelx_deploy2.zip", "cpanel.txt", "deploy.ps1", "test_extract", "test_extract2")
$filesToZip = Get-ChildItem -Path $sourceDir -Recurse | Where-Object {
    $excludeMatch = $false
    foreach ($ex in $exclude) {
        if ($_.FullName -match [regex]::Escape($ex)) {
            $excludeMatch = $true
            break
        }
    }
    -not $excludeMatch
}
Compress-Archive -Path $filesToZip.FullName -DestinationPath $zipPath -Force
Write-Host "Zipping complete."

# 2. Upload via cURL FTP
Write-Host "Uploading ZIP via FTP..."
$curlCmd = "curl.exe -T `"$zipPath`" `"$ftpServer/public_html/multipanelx_deploy.zip`" --user `"${ftpUser}:${ftpPass}`" --insecure"
Invoke-Expression $curlCmd

# 3. Extract via cPanel API
Write-Host "Extracting ZIP via cPanel API..."
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$extractUrl = "https://${cpanelHost}:${cpanelPort}/execute/Fileman/extract"
$extractBody = @{
    sourcefiles = "public_html/multipanelx_deploy.zip"
    dir = "public_html/"
}
Invoke-RestMethod -Uri $extractUrl -Method Get -Headers $headers -Body $extractBody

Write-Host "Deployment stage complete!"

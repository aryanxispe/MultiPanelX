<?php
$ftp_server = "ftp.myvipsite.fun";
$ftp_user = "nztvysxb123";
$ftp_pass = "*wBXmgR11*X6z3";

$conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
if (@ftp_login($conn, $ftp_user, $ftp_pass)) {
    echo "Connected as $ftp_user@$ftp_server\n";
} else {
    die("Couldn't connect as $ftp_user\n");
}

ftp_pasv($conn, true);

// We need to upload to public_html/
if (!ftp_chdir($conn, "public_html")) {
    die("Couldn't change directory to public_html\n");
}

function uploadDir($conn, $local_dir, $remote_dir) {
    echo "Uploading directory $local_dir to $remote_dir\n";
    @ftp_mkdir($conn, $remote_dir);
    
    $files = scandir($local_dir);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            $local_file = "$local_dir/$file";
            $remote_file = "$remote_dir/$file";
            
            if (is_dir($local_file)) {
                if ($file === '.git' || $file === '.codegraph' || $file === 'test_extract' || $file === 'test_extract2') continue;
                uploadDir($conn, $local_file, $remote_file);
            } else {
                if (pathinfo($local_file, PATHINFO_EXTENSION) == 'php' || pathinfo($local_file, PATHINFO_EXTENSION) == 'css' || pathinfo($local_file, PATHINFO_EXTENSION) == 'js' || pathinfo($local_file, PATHINFO_EXTENSION) == 'json') {
                    if (ftp_put($conn, $remote_file, $local_file, FTP_BINARY)) {
                        echo "Successfully uploaded $local_file\n";
                    } else {
                        echo "There was a problem while uploading $local_file\n";
                    }
                }
            }
        }
    }
}

uploadDir($conn, "c:/Users/vc/Desktop/multipanelx", ".");

ftp_close($conn);
echo "FTP upload complete.\n";

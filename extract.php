<?php $zip = new ZipArchive; if ($zip->open("multipanelx_update.zip") === TRUE) { $zip->extractTo("./"); $zip->close(); echo "ok"; } else { echo "fail"; } unlink("extract.php"); ?>

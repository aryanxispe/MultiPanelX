<?php
$dir = new DirectoryIterator('c:/Users/vc/Desktop/multipanelx');
foreach($dir as $file) {
    if($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;

        // Add csrf_field() to forms
        $content = preg_replace('/(<form[^>
    <?php echo csrf_field(); ?>]*>)/i', '$1' . "\n" . '    <?php echo csrf_field(); ?>', $content);

        // Find POST handlers and inject verify_csrf();
        $content = preg_replace('/(if\s*\(\s*\$_POST\s*(?:&&[^)]+)?\)\s*\{)/i', '$1' . "\n" . '    verify_csrf();', $content);
        $content = preg_replace('/(if\s*\(\s*\$_SERVER\[\'REQUEST_METHOD\'\]\s*(?:==|===)\s*\'POST\'\s*\)\s*\{)/i', '$1' . "\n" . '    verify_csrf();', $content);

        if ($content !== $original) {
            file_put_contents($path, $content);
            echo "Updated " . $file->getFilename() . "\n";
        }
    }
}
echo "Done CSRF setup\n";

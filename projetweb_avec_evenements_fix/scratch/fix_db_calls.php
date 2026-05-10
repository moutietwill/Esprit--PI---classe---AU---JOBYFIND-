<?php
function replaceInDir($dir) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $newContent = str_replace('config::getConnexion()', 'Database::getInstance()->getConnection()', $content);
            if ($content !== $newContent) {
                file_put_contents($file->getPathname(), $newContent);
                echo "Updated (DB): " . $file->getPathname() . "\n";
            }
        }
    }
}

replaceInDir('views/frontoffice');
replaceInDir('views/backoffice');

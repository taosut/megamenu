<?php
$parentDir = "/var/www/tekshop/media/catalog/product/uploads/product/";
//$parentDir = "d:\\Projects\\app_teko.sale\\uploads\\product\\";

function listFolderFiles($dir)
{
    foreach (new DirectoryIterator($dir) as $fileInfo) {
        if (!$fileInfo->isDot()) {
            $path = $fileInfo->getPathname();
            if ($fileInfo->isDir()) {
                listFolderFiles($path);
            } else {
                $size = filesize($path);

                if(!$size){
                    echo $path . "\n";
                }
            }
        }
    }
}
listFolderFiles($parentDir);
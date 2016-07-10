<?php

$srcRoot = __DIR__.'/Core';
$buildRoot = __DIR__."/";
$buildFileName = "daemon.phar";
$buildFile  = $buildRoot . $buildFileName;
$basePointer = strpos($srcRoot,'Core');


if (file_exists( $buildFile.'.zip')) {
    Phar::unlinkArchive($buildFile.'.zip');
}

echo "Creando phar para PhpDaemon en  ".$buildRoot."\n";


$phar = new Phar($buildFile, 0, $buildFileName);



$phar->compressFiles(Phar::GZ);
$phar->setSignatureAlgorithm (Phar::SHA1);


$files = array();


$files["config.php"]    = __DIR__ . "/config.php";
$files["autoload.php"]  = __DIR__ . "/autoload.php";
$files["functions.php"] = __DIR__ . "/functions.php";
$files["bootstrap.php"] = __DIR__ . "/bootstrap.php";


 
$rd = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcRoot));
foreach($rd as $file) {
    if ($file->getFilename() != '..' && $file->getFilename() != '.') {
        $files[substr($file->getPath().DIRECTORY_SEPARATOR.$file->getFilename(),$basePointer)]=$file->getPath().DIRECTORY_SEPARATOR.$file->getFilename();
    }
}



$phar->startBuffering();
$phar->buildFromIterator(new ArrayIterator($files));
$phar->stopBuffering();

$phar->setStub($phar->createDefaultStub('bootstrap.php'));
$phar = null;

//$phar->buildFromDirectory($srcRoot.'/Core/','./\.php$/');

//copy($srcRoot . "/config.ini", $buildRoot . "/config.ini");
<?php

$srcRoot = __DIR__;
$buildRoot = __DIR__."/log";
 
$phar = new Phar($buildRoot . "/myapp.phar", 
                FilesystemIterator::CURRENT_AS_FILEINFO |  FilesystemIterator::KEY_AS_FILENAME, "app.phar");

$phar["example.php"] = file_get_contents($srcRoot . "/example.php");

$phar->setStub($phar->createDefaultStub("example.php"));

//copy($srcRoot . "/config.ini", $buildRoot . "/config.ini");
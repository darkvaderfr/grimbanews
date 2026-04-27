<?php

$source = dirname(__DIR__) . '/database/grimbanews.sqlite';
$target = '/tmp/grimbanews-testing.sqlite';
$views = '/tmp/grimbanews-testing-views';

if (is_file($source)) {
    copy($source, $target);
    chmod($target, 0666);
}

if (! is_dir($views)) {
    mkdir($views, 0777, true);
}

putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=' . $target);
putenv('CACHE_STORE=array');
putenv('LOG_CHANNEL=stderr');
putenv('CMS_MAX_EXECUTION_TIME=0');
putenv('VIEW_COMPILED_PATH=' . $views);

$_ENV['DB_CONNECTION'] = $_SERVER['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = $_SERVER['DB_DATABASE'] = $target;
$_ENV['CACHE_STORE'] = $_SERVER['CACHE_STORE'] = 'array';
$_ENV['LOG_CHANNEL'] = $_SERVER['LOG_CHANNEL'] = 'stderr';
$_ENV['CMS_MAX_EXECUTION_TIME'] = $_SERVER['CMS_MAX_EXECUTION_TIME'] = '0';
$_ENV['VIEW_COMPILED_PATH'] = $_SERVER['VIEW_COMPILED_PATH'] = $views;

require dirname(__DIR__) . '/vendor/autoload.php';

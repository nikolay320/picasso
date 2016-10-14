<?php
$min_enableBuilder = false;
$min_builderPassword = 'admin';
$min_errorLogger = true;
$min_allowDebugFlag = true;
$min_cachePath = '/var/www/html/picasso/wp-content/plugins/bwp-minify/cache';
$min_documentRoot = '/var/www/html/picasso';
$min_cacheFileLocking = true;
$min_serveOptions['bubbleCssImports'] = true;
$min_serveOptions['maxAge'] = 2160000;
$min_serveOptions['minApp']['groupsOnly'] = false;
$min_symlinks = array();
$min_uploaderHoursBehind = 0;
$min_libPath = dirname(__FILE__) . '/lib';
ini_set('zlib.output_compression', '0');
// auto-generated on 2016-05-09 10:16:44

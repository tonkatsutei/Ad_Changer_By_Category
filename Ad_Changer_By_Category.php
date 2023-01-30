<?php
/* 
Plugin Name: Ad Changer By Category
Plugin URI: https://manual.tonkatsutei.com/acbc/
Description: カテゴリーごとに設定した別々の広告を表示します。
Author: ton活亭
Version: 0.0.1
Author URI: https://twitter.com/tonkatsutei

▼ バージョン履歴

0.0.1
・開発開始
・formで管理画面を作っていたがカスタム投稿に変更するために中断

*/

declare(strict_types=1);

if (!defined('ABSPATH')) exit;
@define('WP_MEMORY_LIMIT', '256M');

//ini_set("display_errors", 'On');
//error_reporting(E_ALL ^ E_DEPRECATED);

// 自動更新
require_once('plugin-update-checker-5.0/plugin-update-checker.php');

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/tonkatsutei/Ad_Changer_By_Category/',
    __FILE__,
    'ACBC'
);
$myUpdateChecker->setBranch('master');

// 本体
require_once('include/base.php');

<?php
/* 
Plugin Name: Ad Changer By Category
Plugin URI: https://manual.tonkatsutei.com/acbc/
Description: カテゴリーごとに設定した別々の広告を表示します。
Author: ton活亭
Version: 1.0.3
Author URI: https://twitter.com/tonkatsutei

▼ バージョン履歴

1.0.3
・グループ追加の後に行追加した際に最後のグループ番号になってた
・記事中でショートコードのg=を存在しない番号を書くと更新受付してくれなかった

1.0.2
・クリック計測
・カテゴリー別ランダム表示

1.0.1
・エラー表示をオフに

1.0.0
・公開

*/

declare(strict_types=1);

if (!defined('ABSPATH')) exit;
@define('WP_MEMORY_LIMIT', '256M');

ini_set("display_errors", 'On');
error_reporting(E_ALL ^ E_DEPRECATED);

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

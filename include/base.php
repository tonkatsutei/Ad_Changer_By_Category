<?php

declare(strict_types=1);

namespace tonkatsutei\Ad_Changer_By_Category\base;

if (!defined('ABSPATH')) exit;

_base::autoload();
_base::plugin_on();

class _base
{
    public static function plugin_on(): void
    {
        // 管理者メニュー
        add_action('admin_menu', 'tonkatsutei\Ad_Changer_By_Category\control_panel\_control_panel::show_admin_menu');

        // ショートコードの実行
        add_action('init', 'tonkatsutei\Ad_Changer_By_Category\shortcode\_shortcode::use_shortcode');
    }

    public static function autoload(): void
    {
        $files = glob(_common::plugin()['path'] . 'include/*.php');
        foreach ($files as $file) {
            require_once($file);
        }
    }
}

class _common
{
    public static function plugin(): array
    {
        $name = self::between('tonkatsutei\\', '\\', __NAMESPACE__)[0];
        $path = WP_PLUGIN_DIR . '/' . $name . '/';
        $path = str_replace('//', '/', $path);
        $version = get_file_data($path . $name . '.php', array('version' => 'Version'))['version'];
        $re['name'] = $name;
        $re['path'] = $path;
        $re['version'] = $version;
        return $re;
    }

    public static function between(string $beg, string $end, string $str): array
    {
        // $begと$endが同じ時
        {
            if ($beg === $end) {
                // 最初の$begまでを削除
                $pos = strpos($str, $beg);
                $str = mb_substr($str, $pos); // 最初の$begは残す

                // $begで区切って返す
                return explode($beg, $str);
            }
        }

        // $begと$endが違う時
        {
            // 最初の$endが$begの前にあるときは$endまで削除
            $beg_pos = strpos($str, $beg);
            $end_pos = strpos($str, $end);
            if ($end_pos < $beg_pos) {
                $str = mb_substr($str, $end_pos + 1); // 最初の$endを残さない
            }

            // $begで区切る
            $array = explode($beg, $str);

            $re = [];
            foreach ($array as $item) {
                // $endの位置
                $pos = strpos($item, $end);

                // $endがない時は飛ばす
                if ($pos === false || $pos === 0) {
                    continue;
                }

                // $endまでの文字列
                $re[] = substr($item, 0, $pos);
            }
            return $re;
        }
    }
}

class _options
{
    public static function update(string $key, string $val): void
    {
        update_option(_common::plugin()['name'] . "_$key", $val);
    }

    public static function get(string $key): string
    {
        $val = get_option(_common::plugin()['name'] . "_$key");
        if (!$val) {
            return '';
        }
        return stripslashes($val);
    }
}

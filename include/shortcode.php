<?php

declare(strict_types=1);

namespace tonkatsutei\Ad_Changer_By_Category\shortcode;

if (!defined('ABSPATH')) exit;

use tonkatsutei\Ad_Changer_By_Category\base\_options;

class _shortcode
{
    public static function use_shortcode(): void
    {
        // ショートコードを使う箇所
        add_filter('the_content',  'do_shortcode', 21);
        add_filter('comment_text', 'do_shortcode', 21);
        add_filter('widget_text',  'do_shortcode', 21);

        // ショートコードの実行
        add_shortcode("ACBC", '\tonkatsutei\Ad_Changer_By_Category\shortcode\_shortcode::show_ad');
    }

    public static function show_ad(array $atts): ?string
    {
        extract(shortcode_atts([
            "g"    => 0,
        ], $atts));

        // カテゴリーのデータを取得
        // 記事に複数のカテゴリーが設定していても1番目を対象とする
        $cate_slug = get_the_category()[0]->slug;

        // 保存値を取得
        $data = _options::get('data');
        $data = json_decode($data, true);

        // 対象グループのデータ
        $ads = $data[$g];

        // 同カテゴリーを複数広告にセットしている場合にランダムに表示させる
        shuffle($ads); // ランダム順

        // アフィリエイトコードを取り出す
        $n = -1;
        foreach ($ads as $key => $val) {
            $cate = $val['cate'];
            if (in_array($cate_slug, $cate, true)) {
                $n = $key;
                break;
            }
        }

        if ($n >= 0) {
            // 表示のカウントアップ
            self::countup($n);

            // 広告タグを返す
            $adcode = $ads[$n]['adcode'];
            $adcode = <<<EOD
                <div class='ACBC' data-n='{$n}'>
                    {$adcode}
                </div>
            EOD;
            return $adcode;
        }

        // 該当カテゴリーが無い時は空白を返す
        return '';
    }

    private static function countup(int $n): void
    {
    }
}

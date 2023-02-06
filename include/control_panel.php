<?php

declare(strict_types=1);

namespace tonkatsutei\Ad_Changer_By_Category\control_panel;

if (!defined('ABSPATH')) exit;

use tonkatsutei\Ad_Changer_By_Category\base\_common;
use tonkatsutei\Ad_Changer_By_Category\base\_options;
use tonkatsutei\Ad_Changer_By_Category\js\_js;

class _control_panel
{
    public static function show_admin_menu(): void
    {
        add_menu_page(
            'ACBC', // page_title
            'ACBC', // menu_title
            'administrator', // capability
            'ACBC', // menu_slug
            'tonkatsutei\Ad_Changer_By_Category\control_panel\_control_panel::control_panel_html', // html
            'dashicons-excerpt-view', // icon_url   https://developer.wordpress.org/resource/dashicons
        );
    }

    public static function control_panel_html(): void
    {
        // 更新ボタンを押した場合
        if (isset($_POST['settei'])) {

            // POSTデータを取得
            $data = [];
            $group = [];
            foreach ($_POST as $key => $val) {
                if (false !== strpos($key, 'cate(')) {
                    $g = (int)_common::between('cate(', ')', $key)[0];
                    $n = (int)_common::between(')', 'e', $key . 'e')[0];
                    $needle = [' ', '　', '|', '、', "\r\n", "\r", "\n", "\t"];
                    $val = str_replace($needle, ',', $val);
                    $val = explode(',', $val);
                    $val = array_unique($val);
                    $val = array_filter($val);
                    $data[$g][$n]['cate'] = $val;
                }
                if (false !== strpos($key, 'adcode(')) {
                    $g = (int)_common::between('adcode(', ')', $key)[0];
                    $n = (int)_common::between(')', 'e', $key . 'e')[0];
                    $val = str_replace("\r", "\\r", $val);
                    $val = str_replace("\n", "\\n", $val);
                    $data[$g][$n]['adcode'] = $val;
                }
                if (false !== strpos($key, 'count(')) {
                    $g = (int)_common::between('count(', ')', $key)[0];
                    $n = (int)_common::between(')', 'e', $key . 'e')[0];
                    $data[$g][$n]['count'] = (int)$val;
                    $group[$g] = $g;
                }
            }
            ksort($data);

            // 設定済みの最後のグループ番号
            $v['idg'] = max($group);

            // 保存
            $data_str = json_encode($data, JSON_UNESCAPED_UNICODE);
            _options::update('data', $data_str);
            _options::update('idg', (string)$v['idg']);

            $v["settei_res"] = <<<EOD
                <div style="padding:1em;">更新しました。</div>
            EOD;
        } else {
            $v["settei_res"] = '';
        }

        // httpの場合
        if ($_SERVER['HTTPS'] != 'on') {
            $v['http_msg_id'] = 'http_msg_on';
        } else {
            $v['http_msg_id'] = 'http_msg_off';
        }

        // 設定済みの最後のグループ番号
        $v['idg'] = (int)_options::get(('idg'));
        if ($v['idg'] === 0) {
            $v['idg'] = 1;
        }

        // 保存値を取得
        // 更新の場合は更新済みの値がセットされている
        $data = _options::get('data');

        // 新規フラグ
        if (empty($data)) {
            $new_flug = true;
        } else {
            $new_flug = false;
        }

        if ($new_flug) {
            $data = [];
        } else {
            $data = json_decode($data, true);
            ksort($data);
        }

        // 重複を除いたグループ番号だけの配列
        if ($new_flug) {
            $group[1] = 1;
        } else {
            foreach ($data as $key => $val) {
                $group[$key] = $key;
            }
        }

        // 保存値からTABLEを生成
        $tables = '';
        if ($new_flug) {
            $row_src = self::row_new_src((string)$v['idg'], '', '', '0');
            $tables .= self::tbl_new_src('1', $row_src);
        } else {
            foreach ($data as $g_key => $g_val) {
                $row_src = '';
                foreach ($g_val as $val) {
                    if (isset($val['cate'])) {
                        $cate = implode(',', $val['cate']);
                    } else {
                        $cate = '';
                    }
                    if (isset($val['adcode'])) {
                        $adcode = $val['adcode'];
                    } else {
                        $adcode = '';
                    }
                    if (isset($val['count'])) {
                        $count = (string)$val['count'];
                    } else {
                        $count = '0';
                    }
                    $row_src .= self::row_new_src((string)$g_key, $cate, $adcode, $count);
                }
                $tables .= self::tbl_new_src((string)$g_key, $row_src);
            }
        }
        $v['tables'] = $tables;

        // バージョン
        $v['version'] = 'Ver.' . _common::plugin()['version'];

        $v['row_new_src'] = self::row_new_src('%idg', '%val_cate', '%val_adcode', '%val_count');
        $v['tbl_new_src'] = self::tbl_new_src('%idg', '%row_new_src');
        $v['panel_js'] = _js::panel_js($v);

        // HTML
        $code = self::html($v);
        $code .= self::acbc_style($v);
        $code .= self::main_style($v);
        $code .= self::dark_mode_style($v);
        print $code;
    }

    private static function html(array $v): string
    {
        $prefix = "_" . _common::plugin()['name'];
        return <<<EOD
            <form method="post" action="" enctype="multipart/form-data" id="acbc_form">
                <div class="{$prefix}_wrap">
                    <div class="settei_res">{$v["settei_res"]}</div>

                    <h2>
                        Ad Changer By Category
                    </h2>
                    <div class='version'>{$v['version']}</div>
                    <div id='{$v['http_msg_id']}'>
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                        HTTP（非SSL）では[COPY]ボタンは機能しません。
                    </div>

                    {$v['tables']}

                    <div id="hoge"></div>
                    <table id="tbl_foot">
                        <tr>
                            <th id="add_groupe">
                                <span class="dashicons dashicons-table-row-after"></span>
                                グループ追加
                            </th>
                        </tr>
                        <tr>
                            <td>
                                <button type="submit" name="settei" id="settei" value="on">更 新</button>
                            </td>
                        </tr>
                    </table>

                </div>
            </form>
            {$v['panel_js']}
        EOD;
    }

    private static function row_new_src(string $idg, string $cate, string $adcode, string $count): string
    {
        return <<<EOD
            <tr>
                <td>
                    <textarea class="cate"   name="{$idg}">{$cate}</textarea>
                </td>
                <td>
                    <textarea class="adcode" name="{$idg}">{$adcode}</textarea>
                </td>
                <td>
                    <input    class="count"  name="{$idg}" value="{$count}"></input><br>
                    <div class="reset">RESET</div>
                </td>
                <td class="row_remove">
                    <i class="del_row fa fa-minus-square" aria-hidden="true"></i>
                </td>
            </tr>
        EOD;
    }

    private static function tbl_new_src(string $idg, string $row_src): string
    {
        return <<<EOD
            <table class="tbl_groupe">
            <thead>
                <tr>
                    <th colspan=4>
                        <div class="horizontal">
                            <h3>グループ {$idg}</h3>
                            <div>
                                ショートコード：
                                <span class="code">[ACBC g={$idg}]</span>　
                                <span class="copycode" data-code="[ACBC g={$idg}]"><i class="fa fa-clipboard" aria-hidden="true"></i> COPY</span>
                            </div>
                            <div class="del_groupe">このグループを削除 <i class="fa fa-minus-square-o" aria-hidden="true"></i></div>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th>対象カテゴリー</th>
                    <th>HTMLタグ（広告）</th>
                    <th>
                        クリック数
                        <i class="fa fa-refresh" aria-hidden="true"></i>
                    </th>
                    <th>削除</th>
                </tr>
            </thead>
            <tbody>
                {$row_src}
            </tbody>
            <tfoot>
                <tr>
                    <td colspan=3 class="border_none">
                        <span class='add_row'>
                            <i class="fa fa-plus-square" aria-hidden="true"></i>
                            タグ行追加
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
        EOD;
    }

    private static function acbc_style(array $v): string
    {
        $prefix = "_" . _common::plugin()['name'];
        return <<<EOD
            <style>
            #wpbody{
                color:#fff;
            }
            .{$prefix}_wrap #http_msg_on{
                margin-top: 1em;
                padding: 10px;
            }
            .{$prefix}_wrap #http_msg_on i{
                font-size: 2em;
                color: #f39800;
            }
            .{$prefix}_wrap #http_msg_off{
                display: none;
            }
            .{$prefix}_wrap .horizontal {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .{$prefix}_wrap .code {
                /*font-weight: normal;*/
                color: #8ab4f8;
            }
            .{$prefix}_wrap .del_groupe{
                text-align:right;
            }
            .{$prefix}_wrap .row_remove{
                text-align:center;
                padding-top: 1em;
            }
            .{$prefix}_wrap .tbl_groupe td {
                vertical-align: top;
                padding-bottom: 5px !important;
            }
            .{$prefix}_wrap thead tr:first-child th,
            .{$prefix}_wrap tfoot td {
                border : none !important;
            }
            .{$prefix}_wrap #tbl_foot th {
                border : 1px solid #fff !important;
            }
            .{$prefix}_wrap #tbl_foot td {
                border : none !important;
            }
            .{$prefix}_wrap #settei,
            .{$prefix}_wrap .copycode,
            .{$prefix}_wrap .add_row,
            .{$prefix}_wrap .del_groupe,
            .{$prefix}_wrap i,
            .{$prefix}_wrap .reset,
            .{$prefix}_wrap #tbl_foot th {
                cursor: pointer;
            }
            .{$prefix}_wrap .tbl_groupe .adcode {
                width : 450px;
            }
            .{$prefix}_wrap .tbl_groupe .count {
                width : 100px;
            }
            .{$prefix}_wrap .copycode {
                border: 1px solid #fff;
                padding: 3px;
                border-radius: 5px;
                margin-top: 5px;
                display: inline-block;
            }
            .{$prefix}_wrap .count,
            .{$prefix}_wrap .reset{
                text-align:right;
                padding-right: 1em;
            }
            </style>
        EOD;
    }

    private static function main_style(array $v): string
    {
        $prefix = "_" . _common::plugin()['name'];
        return <<<EOD
            <style>
            .{$prefix}_wrap {
                width: 100%;
                max-width: 800px;
                margin-top: 1em;
                padding: 1em;
                border-radius: 10px;
                letter-spacing: 0.1em;
            }
            .{$prefix}_wrap .center{
                text-align:center;
            }
            .{$prefix}_wrap .settei_res {
                font-weight: bold;
                border-radius: 5px;
            }
            .{$prefix}_wrap h2 {
                font-size: 4em;
                /*font-weight: 100;*/
                padding-bottom: inherit;
                margin-bottom: 0;
            }
            .{$prefix}_wrap .version{
                font-weight: normal;
                margin-left: 1em;
                color: #999;
            }
            .{$prefix}_wrap h3 {
                /*margin-bottom: 0.1em;*/
                letter-spacing: 0.03em;
            }
            .{$prefix}_wrap .inline {
                display: inline-block;
            }
            .{$prefix}_wrap button {
                padding:1em 3em;
            }
            .{$prefix}_wrap .w80 {
                width: 80px;
            }
            .{$prefix}_wrap .w170 {
                width: 170px;
            }
            .{$prefix}_wrap hr {
                border: 0;
            }
            .{$prefix}_wrap table {
                margin: 1em;
            }
            .{$prefix}_wrap table input[type='text']{
                width: 50px;
            }
            .{$prefix}_wrap button{
                margin: 1em 0;
            }
            .{$prefix}_wrap .icon {
                color: #7cbaf1;
                font-size: 2em;
                margin: 0 0 10px;
                display: inline;
            }
            </style>
        EOD;
    }

    private static function dark_mode_style(array $v): string
    {
        $prefix = "_" . _common::plugin()['name'];
        return <<<EOD
            <style>
            #wpwrap {
                background-color: #2a4359;
            }
            .{$prefix}_wrap {
                background-color: #2c3338;
                color: #fff;
            }
            .{$prefix}_wrap textarea, .{$prefix}_wrap input{
                background-color: #49545c;
                color: #fff;
            }
            .{$prefix}_wrap .settei_res {
                background-color: #717171;
                color: #fff;
            }
            .{$prefix}_wrap h2 {
                color: #7cbaf1;
            }
            .{$prefix}_wrap h3 {
                color: #7cbaf1;
                /*background-color: #7cbaf1;*/
            }
            .comment {
                color: #999;
            }
            .color_f5374e {
                color: #f5374e;
            }
            .color_fff {
                color: #fff;
            }
            .{$prefix}_wrap hr {
                border-top: 1px dashed #7cbaf1;
            }
            .{$prefix}_wrap table {
                color: #fff;
            }
            .{$prefix}_wrap th{
                border-top: solid 1px #999;
                border-bottom: solid 1px #999;
                padding: 3px;
            }
            .{$prefix}_wrap td{
                border-bottom: solid 1px #999;
                padding: 3px;
            }

            </style>
        EOD;
    }
}

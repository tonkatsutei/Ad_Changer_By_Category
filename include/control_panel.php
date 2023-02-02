<?php

declare(strict_types=1);

namespace tonkatsutei\Ad_Changer_By_Category\control_panel;

if (!defined('ABSPATH')) exit;


use tonkatsutei\Ad_Changer_By_Category\base\_common;
use tonkatsutei\Ad_Changer_By_Category\image_size\_image_size;
use tonkatsutei\Ad_Changer_By_Category\base\_options;

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
                if (false !== strpos($key, 'cate')) {
                    $g = (int)_common::between('cate_', '_', $key)[0];
                    $n = (int)_common::between('_', 'e', $key . 'e')[0];
                    $val = str_replace([' ', '　', "\r\n", "\r", "\n", "\t"], ',', $val);
                    $data[$g][$n]['cate'] = explode(',', $val);
                }
                if (false !== strpos($key, 'adcode')) {
                    $g = (int)_common::between('cate_', '_', $key)[0];
                    $n = (int)_common::between('_', 'e', $key . 'e')[0];
                    $data[$g][$n]['adcode'] = $val;
                    $group[$g] = $g;
                }
            }

            // 設定済みの最後のグループ番号
            $v['idg'] = max($group);

            // 保存
            $data_str = serialize($data);
            _options::update('data', $data_str);
            _options::update('idg', $v['idg']);

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
        $data = unserialize($data);

        // 新規の時
        if (empty($data)) {
            $new_flug = true;
        } else {
            $new_flug = false;
        }

        // 重複を除いたグループ番号だけの配列
        if ($new_flug) {
            $group[1] = 1;
        } else {
            $gu = array_column($data, 'group');
            foreach ($gu as $i) {
                $group[$i] = $i;
            }
        }

        // 保存値からTABLEを生成
        $tables = '';
        if ($new_flug) {
            print "NEW<br>";
            $row_src = self::row_new_src((string)$v['idg'], '', '');
            $tables .= self::tbl_new_src('1', $row_src);
        } else {
            foreach ($group as $num => $vals) {
                $row_src = '';
                foreach ($vals as $key) {
                    $row_src .= self::row_new_src((string)$num, $key['cate'], $key['adcode']);
                }
                $tables .= self::tbl_new_src((string)$num, $row_src);
            }
        }
        $v['tables'] = $tables;

        // バージョン
        $v['version'] = 'Ver.' . _common::plugin()['version'];

        $v['row_new_src'] = self::row_new_src('%idg', '%val_cate', '%val_adcode');
        $v['tbl_new_src'] = self::tbl_new_src('%idg', '%row_new_src');

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

            <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
            <script>
                $(function(){
                    var idg = {$v['idg']};

                    function row_new(idg){
                        var row_new = `
                            {$v['row_new_src']}
                        `;
                        row_new = row_new.replace( /%idg/g, idg );
                        row_new = row_new.replace( /%val_cate/g, '' );
                        row_new = row_new.replace( /%val_adcode/g, '' );
                        return row_new;
                    }

                    function tbl_new(idg){
                        var row_new_src = row_new(idg);
                        var tbl_new_src = `
                            {$v['tbl_new_src']}
                        `;
                        tbl_new_src = tbl_new_src.replace( /%row_new_src/g, row_new_src );
                        tbl_new_src = tbl_new_src.replace( /%idg/g, idg );
                        return tbl_new_src;
                    }

                    // グループ追加
                    $(document).on("click", "#add_groupe", function(event){
                        ++idg;
                        $('#tbl_foot').before(tbl_new(idg));
                    });

                    // グループ削除
                    $(document).on("click", ".del_groupe", function(event){
                        $(this).parent().parent().parent().parent().parent().remove();
                    });

                    // 行追加
                    $(document).on("click", ".add_row", function(event){
                        $(this).parents('table').find('tbody').append(row_new(idg));
                    });

                    // 行削除
                    $(document).on("click", ".del_row", function(event){
                        $(this).parent().parent().remove();
                    });

                    // ショートコードをコピー
                    $(document).on("click", ".copycode", function(event){
                        const code = $(this).data('code');
                        navigator.clipboard.writeText(code);
                    });

                    // 送信前にtextareaのnameに連番を振る
                    $(document).on("click", "#settei", function(event){
                        $('.cate').each(function(i){
                            var g = $(this).attr('name');
                            $(this).attr('name', 'cate_' + g + '_' + (i+1));
                        });
                        $('.adcode').each(function(i){
                            var g = $(this).attr('name');
                            $(this).attr('name', 'adcode_' + g + '_' + (i+1));
                        });
                    });

                });
            </script>
        EOD;
    }

    private static function row_new_src(string $idg, string $cate, string $adcode): string
    {
        return <<<EOD
            <tr>
                <td>
                    <textarea class="cate" name="{$idg}">{$cate}</textarea>
                </td>
                <td>
                    <textarea class="adcode" name="{$idg}">{$adcode}</textarea>
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
                    <th colspan=3>
                        <div class="horizontal">
                            <h3>グループ {$idg}</h3>
                            <div>
                                ショートコード：
                                <span class="code">[ACBC G={$idg}]</span>　
                                <span class="copycode" data-code="[ACBC G={$idg}]"><i class="fa fa-clipboard" aria-hidden="true"></i> COPY</span>
                            </div>
                            <div class="del_groupe">このグループを削除 <i class="fa fa-minus-square-o" aria-hidden="true"></i></div>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th>対象カテゴリー</th>
                    <th>HTMLタグ（広告）</th>
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
            .{$prefix}_wrap #tbl_foot th {
                cursor: pointer;
            }
            .{$prefix}_wrap tbody tr td:nth-child(2) textarea {
                width : 400px;
            }
            .{$prefix}_wrap .copycode {
                border: 1px solid #fff;
                padding: 3px;
                border-radius: 5px;
                margin-top: 5px;
                display: inline-block;
            }
            .{$prefix}_wrap .fa{
                font-size: 1.6em;
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
                max-width: 700px;
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

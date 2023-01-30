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
            // POSTデータから保存値を更新
            _image_size::update_control_panel();
            // 更新値を反映
            _image_size::apply_setting();
            $v["settei_res"] = <<<EOD
                <div style="padding:1em;">
                    更新しました。<br>
                    反映しない場合は他のプラグインやテーマを確認してください。
                </div>
            EOD;
        } else {
            $v["settei_res"] = '';
        }

        // 保存値を取得
        // 更新の場合は更新済みの値がセットされている
        $image_sizes = self::get_saved_value();

        // 表示用TABLE
        $table_html = "<table name='usable_image_sizes'>";
        $table_html .= <<<EOF
                        <tr>
                            <th></th>
                            <th>サイズ</th>
                            <th>切替</th>
                            <th>width</th>
                            <th>height</th>
                            <th>crop</th>
                            <th>初期化</th>
                        </tr>
                    EOF;
        foreach ($image_sizes as $key => $val) {
            $type = $val['type'];
            $width = (int)$val['width'];
            $height = (int)$val['height'];
            $flug = (int)$val['flug'];
            if ($flug === 1) {
                $flug = true;
            } else {
                $flug = false;
            }
            $crop = (int)$val['crop'];
            if ($crop === 1) {
                $crop = true;
            } else {
                $crop = false;
            }
            $table_html .= self::usable_image_size_tr($type, $key, $flug, $width, $height, $crop);
        }
        $table_html .= '</table>';

        // フォームにセット
        $v['table'] = $table_html;
        //$v['name'] = str_replace('_', ' ', _common::plugin()['name']);
        $v['version'] = 'Ver.' . _common::plugin()['version'];

        // HTML
        $code = self::html($v);
        $code .= self::main_style($v);
        $code .= self::dark_mode_style($v);

        print $code;
    }

    // 保存値
    private static function get_saved_value(): array
    {
        // 標準アイキャッチ
        $array = _image_size::get_regular_image_sizes();
        foreach ($array as $key => $val) {
            $image_size[$key] = $val;
        }

        // 追加アイキャッチ
        $added = _image_size::get_added_image_sizes();
        foreach ($added as $key => $val) {
            $image_size[$key] = $val;
        }

        return $image_size;
    }

    private static function usable_image_size_tr(string $type, string $name, bool $flug, int $width, int $height, bool $crop): string
    {
        if ($type === 'regular') {
            //$type_src = '<span class="dashicons dashicons-wordpress-alt"></span>';
            $type_src = '<span class="dashicons dashicons-wordpress"></span>';
        } else {
            $type_src = '<span class="dashicons dashicons-layout"></span>';
        }

        if ($flug) {
            $flug_src = <<<EOD
                        　<label><input type='radio' name='data_{$name}_f' value='0'>OFF</label>
                        　<label><input type='radio' name='data_{$name}_f' value='1' checked = 'cheched' >ON</label>　
                    EOD;
        } else {
            $flug_src = <<<EOD
                        　<label><input type='radio' name='data_{$name}_f' value='0' checked = 'cheched'>OFF</label>
                        　<label><input type='radio' name='data_{$name}_f' value='1'>ON</label>　
                    EOD;
        }

        if ($crop) {
            $crop = 1;
        } else {
            $crop = 0;
        }

        if ($type === 'regular' && $name !== 'thumbnail') {
            $crop_src = "<input type='text' value='0' style='text-align:center;color:#000;' disabled><input type='hidden' name='data_{$name}_c' value='0'>";
        } else {
            $crop_src = "<input type='text' name='data_{$name}_c' value='{$crop}' style='text-align:center;'>";
        }

        $initialization_src = "<input type='checkbox' name='data_{$name}_i' value='1'>";

        return <<<EOD
            <tr>
                <td>{$type_src}</td>
                <td>{$name}</td>
                <td>{$flug_src}</td>
                <td><input type='text' name='data_{$name}_w' value='{$width}'  style='text-align:right;'></td>
                <td><input type='text' name='data_{$name}_h' value='{$height}' style='text-align:right;'></td>
                <td>{$crop_src}</td>
                <td style='text-align:center;'>{$initialization_src}</td>
            </tr>
        EOD;
    }

    private static function html(array $v): string
    {
        $prefix = "_" . _common::plugin()['name'];
        return <<<EOD
            <form method="post" action="" enctype="multipart/form-data">
                <div class="{$prefix}_wrap">
                    <div class="settei_res">{$v["settei_res"]}</div>

                    <h2>
                        Ad Changer By Category
                    </h2>
                    <div class='version'>{$v['version']}</div>

                    <!--{$v['table']}-->

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
                                <button type="submit" name="settei" value="on">更 新</button>
                            </td>
                        </tr>
                    </table>

                </div>
            </form>
            <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
            <script>
                $(function(){
                    var idx = 3;
                    var idg = 1;
                    function tbl_new(idg){
                        return `
                            <table class="tbl_groupe">
                                <thead>
                                    <tr>
                                        <th><h3>グループ \${idg}</h3></th>
                                        <th colspan=2 class="del_groupe">グループ削除 <span class="dashicons dashicons-table-row-delete"></span></th>
                                    </tr>
                                    <tr>
                                        <th>HTMLタグ</th>
                                        <th>カテゴリー</th>
                                        <th>削除</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <textarea></textarea>
                                        </td>
                                        <td>
                                            <textarea></textarea>
                                        </td>
                                        <td class="row_remove">
                                            <span class="dashicons dashicons-remove"></span>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan=3 class="border_none">
                                            <span class="dashicons dashicons-insert"></span>
                                            タグ行追加
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        `;
                    }
                    $('#hoge').after(tbl_new(idg));
                    $('#add_groupe').click(function(event){
                        $('#tbl_foot').before(tbl_new(++idg));
                    });
                    $('#tbl1').on("click", ".dashicons-table-row-delete", function(event){
                        $(this).parent().parent().remove();
                    });
                });
            </script>
            <style>
            h3 {
                text-align:left;
            }
            .del_groupe{
                text-align:right;
            }
            .row_remove{
                text-align:center;
            }
            thead tr:first-child th,
            tfoot td {
                border : none !important;
            }
            #tbl_foot th {
                border : 1px solid #fff !important;
            }
            #tbl_foot td {
                border : none !important;
            }
            .dashicons,
            #tbl_foot th {
                cursor: pointer;
            }
            tbody tr td:first-child textarea {
                width : 400px;
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

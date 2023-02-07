<?php

declare(strict_types=1);

namespace tonkatsutei\Ad_Changer_By_Category\js;

use tonkatsutei\Ad_Changer_By_Category\base\_options;

if (!defined('ABSPATH')) exit;

add_filter('the_content', __NAMESPACE__ . '\_js::count_click_js');

class _js
{
    public static function count_click_js(string $the_content): string
    {
        $license_id = _options::get('lisense_id');
        return <<< EOD
            {$the_content}
            <script id='count_click_js'>
                $(function(){
                    $(".ACBC a").on("click", function() {
                        // ジャンプを無効化
                        event.preventDefault();

                        // クリックしたadcode番号
                        var n = $(this).parents('.ACBC').data('n');

                        // カウントアップAPIにアクセス
                        fetch("/wp-content/plugins/Ad_Changer_By_Category/count_up.php?n=" + n + "&l={$license_id}")
                            //レスポンスの受け取り
                            .then(function (response) {
                                //受け取ったデータを返す
                                return response.text();
                            })

                        // ジャンプ
                        location.href= $(this).attr('href');
                    });
                });
            </script>
        EOD;
    }

    public static function panel_js(array $v): string
    {
        return <<< EOD
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
                        row_new = row_new.replace( /%val_count/g, '0' );
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
                        var g = $(this).parents('table').data('idg');
                        $(this).parents('table').find('tbody').append(row_new(g));
                    });

                    // 行削除
                    $(document).on("click", ".del_row", function(event){
                        $(this).parent().parent().remove();
                    });

                    // クリック数リセット（行）
                    $(document).on("click", ".reset", function(event){
                        $(this).parents('td').find('.count').val(0);
                    });

                    // クリック数リセット（グループ）
                    $(document).on("click", ".fa-refresh", function(event){
                        $(this).parents('table').find('.count').val(0);
                    });

                    // ショートコードをコピー
                    $(document).on("click", ".copycode", function(event){
                        const code = $(this).data('code');
                        navigator.clipboard.writeText(code);
                    });

                    // 送信前にtextareaのnameに連番を振る
                    $(document).on("click", "#settei", function(event){
                        $('._Ad_Changer_By_Category_wrap .cate').each(function(i){
                            var g = $(this).attr('name');
                            $(this).attr('name', 'cate(' + g + ')' + (i+1));
                        });
                        $('._Ad_Changer_By_Category_wrap .adcode').each(function(i){
                            var g = $(this).attr('name');
                            $(this).attr('name', 'adcode(' + g + ')' + (i+1));
                        });
                        $('._Ad_Changer_By_Category_wrap .count').each(function(i){
                            var g = $(this).attr('name');
                            $(this).attr('name', 'count(' + g + ')' + (i+1));
                        });
                    });

                });
            </script>
        EOD;
    }
}

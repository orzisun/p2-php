<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// p2 スレッドサブジェクト表示関数 携帯用
// for subject.php

/**
 * sb_print - スレッド一覧を表示する (<tr>〜</tr>)
 */
function sb_print_k(&$aThreadList)
{
    global $_conf, $_exconf, $browser, $sb_view, $p2_setting, $STYLE, $sb_view;

    //=================================================

    if (!$aThreadList->threads) {
        if ($aThreadList->spmode == 'fav' && $sb_view == 'shinchaku') {
            echo '<p>お気にｽﾚに新着なかったぽ</p>';
        } else {
            echo '<p>該当ｻﾌﾞｼﾞｪｸﾄはなかったぽ</p>';
        }
        return;
    }

    // 変数 ================================================

    // >>1
    if (ereg("news", $aThreadList->bbs) || $aThreadList->bbs == 'bizplus' || $aThreadList->spmode == 'news') {
        // 倉庫は除く
        if ($aThreadList->spmode != 'soko') {
            $only_one_bool = true;
        }
    }

    // 板名
    if ($aThreadList->spmode and $aThreadList->spmode != 'taborn' and $aThreadList->spmode != 'soko') {
        $ita_name_bool = true;
    }

    $norefresh_q = '&amp;norefresh=1';

    // ソート ==================================================

    // スペシャルモード時
    if ($aThreadList->spmode) {
        $sortq_spmode = "&amp;spmode={$aThreadList->spmode}";
        // あぼーんなら
        if ($aThreadList->spmode == 'taborn' or $aThreadList->spmode == 'soko') {
            $sortq_host = "&amp;host={$aThreadList->host}";
            $sortq_ita = "&amp;bbs={$aThreadList->bbs}";
        }
    } else {
        $sortq_host = "&amp;host={$aThreadList->host}";
        $sortq_ita = "&amp;bbs={$aThreadList->bbs}";
    }

    $midoku_sort_ht = "<a href=\"{$_conf['subject_php']}?sort=midoku{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}\">新着</a>";

    //=====================================================
    // ボディ
    //=====================================================

    // spmodeがあればクエリー追加
    if ($aThreadList->spmode) { $spmode_q = "&amp;spmode={$aThreadList->spmode}"; }

    $_newthre_color = ($_exconf['ubiq']['c_newthre']) ? $_exconf['ubiq']['c_newthre'] : '#ff6600';
    $_unum_color = ($_exconf['ubiq']['c_unum']) ? $_exconf['ubiq']['c_unum'] : '#ff6600';
    $i = 0;
    foreach ($aThreadList->threads as $aThread) {
        $i++;
        $midoku_ari = '';
        $anum_ht = ""; //#r1

        $bbs_q = "&amp;bbs=".$aThread->bbs;
        $key_q = "&amp;key=".$aThread->key;

        if ($aThreadList->spmode != 'taborn') {
            if (!$aThread->torder) { $aThread->torder = $i; }
        }

        // 新着レス数 =============================================
        $unum_ht = '';
        // 既得済み
        if ($aThread->isKitoku()) {
            $unum_ht = "{$aThread->unum}";

            $anum = $aThread->rescount - $aThread->unum +1 - $_conf['respointer'];
            if ($anum > $aThread->rescount) { $anum = $aThread->rescount; }
            $anum_ht = "#r{$anum}";

            // 新着あり
            if ($aThread->unum > 0) {
                $midoku_ari = true;
                $unum_ht = "<font color=\"{$_unum_color}\">{$aThread->unum}</font>";
            }

            // subject.txtにない時
            if (!$aThread->isonline) {
                // 誤動作防止のためログ削除操作をロック
                $unum_ht = '-';
            }

            $unum_ht = '['.$unum_ht.']';
        }

        // 新規スレ
        if ($aThread->new) {
            $unum_ht = "[<font color=\"{$_newthre_color}\">新</font>]";
        }

        //総レス数 =============================================
        $rescount_ht = "{$aThread->rescount}";

        // 板名 ============================================
        if ($ita_name_bool) {
            $ita_name = $aThread->itaj ? $aThread->itaj : $aThread->bbs;
            $ita_name_hd = htmlspecialchars($ita_name);
            // $htm['ita'] = "(<a href=\"{$_conf['subject_php']}?host={$aThread->host}{$bbs_q}\">{$ita_name_hd}</a>)";
            $htm['ita'] = '('.$ita_name_hd.')';
        }

        // torder(info) =================================================
        /*
        if ($aThread->fav) { //お気にスレ
            $torder_st = "<b>{$aThread->torder}</b>";
        } else {
            $torder_st = $aThread->torder;
        }
        $torder_ht = "<a id=\"to{$i}\" class=\"info\" href=\"info.php?host={$aThread->host}{$bbs_q}{$key_q}\">{$torder_st}</a>";
        */
        $torder_ht = $aThread->torder;

        // title =================================================
        $rescount_q = "&amp;rc=".$aThread->rescount;

        // dat倉庫 or 殿堂なら
        if ($aThreadList->spmode == 'soko' || $aThreadList->spmode == 'palace') {
            $rescount_q = '';
            $offline_q = '&amp;offline=true';
            $anum_ht = '';
        }

        // タイトル未取得なら
        if (!$aThread->ttitle_ht) {
            // 見かけ上のタイトルなので携帯対応URLである必要はない
            //if (P2Util::isHost2chs($aThread->host)) {
            //  $aThread->ttitle_ht = "http://c.2ch.net/z/-/{$aThread->bbs}/{$aThread->key}/";
            //} else {
                $aThread->ttitle_ht = "http://{$aThread->host}/test/read.cgi/{$aThread->bbs}/{$aThread->key}/";
            //}
        }

        // 全角英数スペースカナを半角に
        //$aThread->ttitle_ht = mb_convert_kana($aThread->ttitle_ht, 'rnsk');

        $aThread->ttitle_ht .= ' ('.$rescount_ht.')';

        // 新規スレ
        if ($aThread->new) {
            $classtitle_q = ' class="thre_title_new"';
        } else {
            $classtitle_q = ' class="thre_title"';
        }

        $thre_url = "{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}{$rescount_q}{$offline_q}{$anum_ht}";

        // オンリー>>1 =============================================
        if ($only_one_bool) {
            $one_ht = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;one=true\">&gt;&gt;1</a>";
        }

        //アクセスキー=====
        /*
        $access_ht = '';
        if ($aThread->torder >= 1 && $aThread->torder <= 9) {
            $access_ht = " {$_conf['accesskey']}=\"{$aThread->torder}\"";
        }
        */

        //====================================================================================
        // スレッド一覧 table ボディ HTMLプリント <tr></tr>
        //====================================================================================

        //ボディ
        echo <<<EOP
<div>{$unum_ht}{$aThread->torder}.<a href="{$thre_url}">{$aThread->ttitle_ht}</a>{$htm['ita']}</div>
EOP;
    }

}

?>

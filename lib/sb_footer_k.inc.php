<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    p2 -  サブジェクト - 携帯フッタ表示
    for subject.php
*/

//=================================================
//フッタプリント
//=================================================
$mae_ht = '';

$host_bbs_q = 'host=' . $aThreadList->host . '&amp;bbs=' . $aThreadList->bbs;

if ($word) {
    $word_at = '&amp;word='.$word;
} else {
    $word_at = '';
}

if ($aThreadList->spmode == 'fav' && $sb_view == 'shinchaku') {
    $allfav_ht = "<p><a href=\"{$_conf['subject_php']}?spmode=fav{$norefresh_q}\">全てのお気にｽﾚを表示</a></p>";
}

// ページタイトル部分HTML設定 ====================================
if ($aThreadList->spmode == 'taborn') {
    $ptitle_ht = "<a href=\"{$ptitle_url}\" {$_conf['accesskey']}=\"{$_conf['k_accesskey']['above']}\">{$_conf['k_accesskey']['above']}.<b>{$aThreadList->itaj_hd}</b></a>（ｱﾎﾞﾝ中）";
} elseif ($aThreadList->spmode == 'soko') {
    $ptitle_ht = "<a href=\"{$ptitle_url}\" {$_conf['accesskey']}=\"{$_conf['k_accesskey']['above']}\">{$_conf['k_accesskey']['above']}.<b>{$aThreadList->itaj_hd}</b></a>（dat倉庫）";
} elseif ($ptitle_url) {
    $ptitle_ht = "<a href=\"{$ptitle_url}\"><b>{$ptitle_hd}</b></a>";
} else {
    $ptitle_ht = "<b>{$ptitle_hd}</b>";
}

// ナビ ===============================
if ($disp_navi['from'] > 1) {
    $mae_ht = "<a href=\"{$_conf['subject_php']}?{$host_bbs_q}&amp;spmode={$aThreadList->spmode}{$norefresh_q}&amp;from={$disp_navi['mae_from']}{$word_at}\" {$_conf['accesskey']}=\"{$_conf['k_accesskey']['prev']}\">{$_conf['k_accesskey']['prev']}.前</a>";
}

if ($disp_navi['tugi_from'] < $sb_disp_all_num) {
    $tugi_ht = "<a href=\"{$_conf['subject_php']}?{$host_bbs_q}&amp;spmode={$aThreadList->spmode}{$norefresh_q}&amp;from={$disp_navi['tugi_from']}{$word_at}\" {$_conf['accesskey']}=\"{$_conf['k_accesskey']['next']}\">{$_conf['k_accesskey']['next']}.次</a>";
}

if ($disp_navi['from'] == $disp_navi['end']) {
    $sb_range_on = $disp_navi['from'];
} else {
    $sb_range_on = $disp_navi['from'].'-'.$disp_navi['end'];
}
$sb_range_st = $sb_range_on.'/'.$sb_disp_all_num;

if (!$disp_navi['all_once']) {
    $k_sb_navi_ht = "<p>{$sb_range_st} {$mae_ht} {$tugi_ht}</p>";
}

// {{{ dat倉庫
// スペシャルモードでなければ、またはあぼーんリストなら
if (!$aThreadList->spmode or $aThreadList->spmode == 'taborn') {
    $dat_soko_ht = "<a href=\"{$_conf['subject_php']}?{$host_bbs_q}{$norefresh_q}&amp;spmode=soko\">dat倉庫</a>\n";
}
// }}}

// {{{ あぼーん中のスレッド
if ($ta_num) {
    $taborn_link_ht = "<a href=\"{$_conf['subject_php']}?{$host_bbs_q}{$norefresh_q}&amp;spmode=taborn\">ｱﾎﾞﾝ中({$ta_num})</a>\n";
}
// }}}

// {{{ 新規スレッド作成
if (!$aThreadList->spmode) {
    $buildnewthread_ht = "<a href=\"post_form.php?{$host_bbs_q}&amp;newthread=1\">ｽﾚ立て</a>\n";
}
// }}}

// {{{ お気にスレセット切替
if ($aThreadList->spmode == 'fav' && $_exconf['etc']['multi_favs']) {
    $switchfavlist_ht = '<div>' . FavSetManager::makeFavSetSwitchForm('m_favlist_set', 'お気にスレ', NULL, NULL, FALSE, array('spmode' => 'fav')) . '</div>';
}

// }}}
// {{{ ソート変更 （新着 レス No. タイトル 板 すばやさ 勢い Birthday ☆）

$sorts = array('midoku' => '新着', 'res' => 'ﾚｽ', 'no' => 'No.', 'title' => 'ﾀｲﾄﾙ');
if ($aThreadList->spmode and $aThreadList->spmode != 'taborn' and $aThreadList->spmode != 'soko') { $sorts['ita'] = '板'; }
if ($_conf['sb_show_spd']) { $sorts['spd'] = 'すばやさ'; }
if ($_conf['sb_show_ikioi']) { $sorts['ikioi'] = '勢い'; }
$sorts['bd'] = 'Birthday';
if ($_conf['sb_show_fav'] and $aThreadList->spmode != 'taborn') { $sorts['fav'] = '☆'; }

$htm['change_sort'] = "<form method=\"get\" action=\"{$_conf['subject_php']}\">";
$htm['change_sort'] .= '<input type="hidden" name="norefresh" value="1">';
// spmode時
if ($aThreadList->spmode) {
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"spmode\" value=\"{$aThreadList->spmode}\">";
}
// spmodeでない、または、spmodeがあぼーん or dat倉庫なら
if (!$aThreadList->spmode || $aThreadList->spmode == "taborn" || $aThreadList->spmode == "soko") {
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"host\" value=\"{$aThreadList->host}\">";
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"bbs\" value=\"{$aThreadList->bbs}\">";
}
$htm['change_sort'] .= 'ｿｰﾄ:<select name="sort">';
foreach ($sorts as $k => $v) {
    if ($now_sort == $k) {
        $htm['change_sort'] .= "<option value=\"{$k}\" selected>{$v}</option>";
    } else {
        $htm['change_sort'] .= "<option value=\"{$k}\">{$v}</option>";
    }
}
$htm['change_sort'] .= '</select>';
$htm['change_sort'] .= '<input type="submit" value="変更"></form>';

// }}}

// HTMLプリント ==============================================
echo '<hr>';
echo $k_sb_navi_ht;
include (P2_LIBRARY_DIR . '/sb_toolbar_k.inc.php');
echo $allfav_ht;
echo $switchfavlist_ht;
echo '<p>';
echo $dat_soko_ht;
echo $taborn_link_ht;
echo $buildnewthread_ht;
echo '</p>';
echo $htm['change_sort'];
echo '<hr>';
echo '<p>';
echo $_conf['k_to_index_ht'];
echo '</p>';
echo '</body></html>';

?>

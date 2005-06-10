<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
	p2 -  お気に板の同期
*/

require_once (P2_LIBRARY_DIR . '/brdctl.class.php');

//================================================
// 読み込み
//================================================
//favita_pathファイルがなければ終了
if (!file_exists($_conf['favita_path'])) {
	return;
}

//favita_path読み込み;
$lines = @file($_conf['favita_path']);

//board読み込み
$_current = BrdCtl::read_brds();

//================================================
// 処理
//================================================

//板リストを単純配列に変換
$current = array();
foreach ($_current as $brdmenu) {
	foreach ($brdmenu->categories as $category) {
		foreach ($category->menuitas as $ita) {
			$current[] = "\t{$ita->host}\t{$ita->bbs}\t{$ita->itaj}";
		}
	}
}

// ■データの同期
// 2ch/bbspinkの場合、板リストと現データをbbs（板名）で照合して、板リストデータで現データを上書きする。
$neolines = array();
$lines = array_map('rtrim', $lines);
foreach ($lines as $line) {
	$data = explode("\t", rtrim($line));
	if (preg_match('/^\w+\.(2ch\.net|bbspink\.com)$/', $data[1], $matches)) {
		$grep_pattern = '/^\t\w+\.' . preg_quote($matches[1], '/') . '\t' . preg_quote($data[2], '/') . '\t/';
	} else {
		if (preg_match('/jbbs\.(shitaraba\.com|livedoor\.(com|jp))/', $data[1])) {
			$data[1] = preg_replace('/jbbs\.(shitaraba|livedoor)\.com/', 'jbbs.livedoor.jp', $data[1]);
			$neolines[] = implode("\t", $data);
		} else {
			$neolines[] = $line;
		}
		continue;
	}
	if ($findline = preg_grep($grep_pattern, $current)) {
		// itajは現データを優先。$findlineは最初に見つかったものを利用。
		if ($data[3]) {
			$newdata = explode("\t", rtrim(array_shift($findline)));
			$neolines[] = "\t{$newdata[1]}\t{$newdata[2]}\t{$data[3]}";
		} else {
			$neolines[] = $findline[0];
		}
	} else {
		$neolines[] = $line;
	}
}

//================================================
// 更新があれば、書き込む
//================================================
if (serialize($lines) != serialize($neolines)) {
	$fp = @fopen($_conf['favita_path'], 'wb') or die("Error: {$_conf['favita_path']} を更新できませんでした");
	@flock($fp, LOCK_EX);
	foreach ($neolines as $l) {
		fputs($fp, $l);
		fputs($fp, "\n");
	}
	@flock($fp, LOCK_UN);
	fclose($fp);
	$sync_ok = true;
} else {
	$sync_ok = false;
}

?>

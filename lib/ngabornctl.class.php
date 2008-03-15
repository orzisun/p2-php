<?php
/*
    p2 - NGあぼーんを操作するクラス
*/
class NgAbornCtl{

    /**
     * あぼーん&NGワード設定を保存する
     */
    function saveNgAborns()
    {
        global $ngaborns, $ngaborns_hits;
        global $_conf;

        // HITした時のみ更新する
        if ($GLOBALS['ngaborns_hits']) {
            foreach ($ngaborns_hits as $code => $v) {

                if ($ngaborns[$code]['data']) {

                    // 更新時間でソートする
                    usort($ngaborns[$code]['data'], array('NgAbornCtl', 'cmpLastTime'));

                    $cont = "";
                    foreach ($ngaborns[$code]['data'] as $a_ngaborn) {

                        // 必要ならここで古いデータはスキップ（削除）する
                        if (!empty($a_ngaborn['lasttime']) && $_conf['ngaborn_daylimit']) {
                            if (strtotime($a_ngaborn['lasttime']) < time() - 60 * 60 * 24 * $_conf['ngaborn_daylimit']) {
                                continue;
                            }
                        }

                        if (empty($a_ngaborn['lasttime'])) {
                            $a_ngaborn['lasttime'] = date('Y/m/d G:i');
                        }

                        $cont .= $a_ngaborn['cond'] . "\t" . $a_ngaborn['lasttime'] . "\t" . $a_ngaborn['hits'] . "\n";
                    } // foreach

                    /*
                    echo "<pre>";
                    echo $cont;
                    echo "</pre>";
                    */

                    // 書き込む

                    $fp = @fopen($ngaborns[$code]['file'], 'wb'); // or die("Error: cannot write. ( $ngaborns[$code]['file'] )");
                    if ($fp) {
                        @flock($fp, LOCK_EX);
                        fputs($fp, $cont);
                        @flock($fp, LOCK_UN);
                        fclose($fp);
                    }


                } // if

            } // foreach
        }
        return true;
    }

    /**
     * NGあぼーんHIT記録を更新時間でソートする
     *
     * @private
     */
    function cmpLastTime($a, $b)
    {
        if (empty($a['lasttime']) || empty($b['lasttime'])) {
            return strcmp($a['lasttime'], $b['lasttime']);
        }
        return (strtotime($a['lasttime']) < strtotime($b['lasttime'])) ? 1 : -1;
    }

    /**
     * あぼーん&NGワード設定を読み込む
     */
    function loadNgAborns()
    {
        $ngaborns = array();

        $ngaborns['aborn_res'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_res.txt'); // これだけ少し性格が異なる
        $ngaborns['aborn_name'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_name.txt');
        $ngaborns['aborn_mail'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_mail.txt');
        $ngaborns['aborn_msg'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_msg.txt');
        $ngaborns['aborn_id'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_id.txt');
        $ngaborns['ng_name'] = NgAbornCtl::readNgAbornFromFile('p2_ng_name.txt');
        $ngaborns['ng_mail'] = NgAbornCtl::readNgAbornFromFile('p2_ng_mail.txt');
        $ngaborns['ng_msg'] = NgAbornCtl::readNgAbornFromFile('p2_ng_msg.txt');
        $ngaborns['ng_id'] = NgAbornCtl::readNgAbornFromFile('p2_ng_id.txt');

        return $ngaborns;
    }

    /**
     * readNgAbornFromFile
     *
     * @private
     */
    function readNgAbornFromFile($filename)
    {
        global $_conf;

        $lines = array();
        $array['file'] = $_conf['pref_dir'].'/'.$filename;
        if ($lines = @file($array['file'])) {
            $lines = array_map('trim', $lines);

            if ($lines) {
                foreach ($lines as $l) {
                    $lar = explode("\t", $l);
                    if (count($lar) < 3 || strlen($lar[0]) == 0) {
                        continue;
                    }
                    $ar = array(
                        'cond' => $lar[0], // 検索条件
                        'word' => $lar[0], // 対象文字列
                        'lasttime' => $lar[1], // 最後にHITした時間
                        'hits' => intval($lar[2]), // HIT回数
                    );
                    if ($filename == 'p2_aborn_res.txt') {
                        continue;
                    }

                    // 板縛り
                    if (preg_match('!<bbs>(.+?)</bbs>!', $ar['word'], $matches)) {
                        $ar['bbs'] = explode(',', $matches[1]);
                    }
                    $ar['word'] = preg_replace('!<bbs>(.*)</bbs>!', '', $ar['word']);

                    // タイトル縛り
                    if (preg_match('!<title>(.+?)</title>!', $ar['word'], $matches)) {
                        $ar['title'] = $matches[1];
                    }
                    $ar['word'] = preg_replace('!<title>(.*)</title>!', '', $ar['word']);

                    // 正規表現
                    if (preg_match('/^<(mb_ereg|preg_match|regex)(:[imsxeADSUXu]+)?>(.+)$/', $ar['word'], $matches)) {
                        // マッチング関数とパターンを設定
                        if ($matches[1] == 'regex') {
                            if (P2_MBREGEX_AVAILABLE) {
                                $ar['regex'] = 'mb_ereg';
                                $ar['word'] = $matches[3];
                            } else {
                                $ar['regex'] = 'preg_match';
                                $ar['word'] = '/' . str_replace('/', '\\/', $matches[3]) . '/';
                            }
                        } else {
                            $ar['regex'] = $matches[1];
                            $ar['word'] = $matches[3];
                        }
                        // 大文字小文字を無視
                        if ($matches[2] && strstr($matches[2], 'i')) {
                            if ($ar['regex'] == 'mb_ereg') {
                                $ar['regex'] = 'mb_eregi';
                            } else {
                                $ar['word'] .= 'i';
                            }
                        }
                    // 大文字小文字を無視
                    } elseif (preg_match('/^<i>(.+)$/', $ar['word'], $matches)) {
                        $ar['word'] = $matches[1];
                        $ar['ignorecase'] = true;
                    }

                    $array['data'][] = $ar;
                }
            }

        }
        return $array;

    }

}
?>

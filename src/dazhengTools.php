<?php
/**
 * Created by PhpStorm.
 * User: mrren
 * Date: 2019/5/20
 * Time: 2:00 PM
 */

namespace epii\dazheng\tools;

use epii\server\Args;
use epii\server\Console;

class dazhengTools
{

    public static function success($cns)
    {
        $num = 0;
        if (is_array($cns)) {
            $num = count($cns);
            $cns = implode(",", $cns);
        } else if (is_string($cns)) {
            $num = count(explode(",", $cns));
        }
        $data = ["num" => $num, "cns" => $cns, "code" => 0, "msg" => "成功"];
        Console::exit(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public static function isQy()
    {
        return in_array(Args::val("com_type"), [2, 4, 6]);
    }


    public static function isSheLi()
    {
        return in_array(Args::val("com_type"), [1, 2]);
    }

    public static function isBianGeng()
    {
        return in_array(Args::val("com_type"), [3, 4]);
    }

    public static function isFaRen()
    {
        return Args::val("user_type") == 1;
    }

    public static function error($msg, $code)
    {
        Console::error($msg, null, $code);
    }

    public static function getTimeMicrotime()
    {
        return microtime(true) * 1000;
    }

    public static function parse_tpl($tpl_file, $data)
    {
        if (!is_file($tpl_file)) {

            return false;
        }
        $find = [];
        $replace = [];
        foreach ($data as $k => $v) {
            $find[] = $k;
            $replace[] = $v;
        }

        if (is_string($find)) {
            $find = ["/\{\{" . $find . "\}\}/is"];
            $replace = [$replace];
        } else {
            foreach ($find as $key => $value) {
                $find[$key] = "/\{\{" . $value . "\}\}/is";
            }
        }
        return preg_replace($find, $replace, file_get_contents($tpl_file));

    }

    public static function toChineseNumber($money)
    {
        $money = round($money, 2);
        $money = $money . "";
        $cnynums = array("零", "壹", "贰", "叁", "肆", "伍", "陆", "柒", "捌", "玖");
        $cnyunits = array("元", "角", "分");
        $cnygrees = array("拾", "佰", "仟", "万", "拾", "佰", "仟", "亿");
        $tmp = explode(".", $money, 2);
        $int = $tmp[0] . "";

        $dec = [];
        if (isset($tmp[1])) {
            $dec[] = $tmp[1];
        } else {
            $dec[] = "";
        }
        if (isset($tmp[2])) {
            $dec[] = $tmp[2];
        } else {
            $dec[] = "";
        }

        $dec = array_filter(array($dec[1], $dec[0]));


        $ret = array_merge($dec, array(implode("", self::cnyMapUnit(str_split($int), $cnygrees)), ""));

        $ret = implode("", array_reverse(self::cnyMapUnit($ret, $cnyunits)));
        return str_replace(array_keys($cnynums), $cnynums, $ret) . (count($dec) === 0 ? "整" : "");
    }

    public static function cnyMapUnit($list, $units)
    {
        $ul = count($units);
        $xs = array();
        foreach (array_reverse($list) as $x) {
            $l = count($xs);
            if ($x != "0" || !($l % 4))
                $n = ($x == '0' ? '' : $x) . ($l > 0 ? ($units[($l - 1) % $ul]) : "");
            else $n = isset($xs[0][0]) && is_numeric($xs[0][0]) ? $x : '';
            array_unshift($xs, $n);
        }
        return $xs;
    }

    public static function unescape($str)
    {
        $str = rawurldecode($str);
        preg_match_all("/(?:%u.{4})|&#x.{4};|&#\d+;|.+/U", $str, $r);
        $ar = $r[0];
        //print_r($ar);
        foreach ($ar as $k => $v) {
            if (substr($v, 0, 2) == "%u") {
                $ar[$k] = iconv("UCS-2BE", "UTF-8", pack("H4", substr($v, -4)));
            } elseif (substr($v, 0, 3) == "&#x") {
                $ar[$k] = iconv("UCS-2BE", "UTF-8", pack("H4", substr($v, 3, -1)));
            } elseif (substr($v, 0, 2) == "&#") {

                $ar[$k] = iconv("UCS-2BE", "UTF-8", pack("n", substr($v, 2, -1)));
            }
        }
        return join("", $ar);
    }

    public static function getTimeFormString($str)
    {
        $d = str_replace(["年", "月", "日"], ["-", "-", ""], $str . " 00:00:00");

        return strtotime($d);
    }


    public static function getTrFromTds($tds, $line_num, callable $td_handler = null)
    {
        $out = [];
        foreach ($tds as $key => $value) {
            $list_index = (int)($key / $line_num);
            $index = isset($out[$list_index]) ? count($out[$list_index]) : 0;
            $out[$list_index][$index] = $td_handler ? $td_handler($index, $value) : $value;
        }
        return $out;
    }


    public static function getTdFromTable($html, $start_trim = 0, $end_trim = 0, $strip_tags = false, $reg = "/\<td([^td]*?)\>(.*?)<\/td\>/is")
    {
        // preg_match_all("/\<td([^\<]*?)\>(.*?)<\/td\>/is", $html, $match);
        preg_match_all($reg, $html, $match);
        $out = [];
        if ($match[2]) {
            $out = array_slice($match[2], $start_trim, count($match[2]) - $start_trim - $end_trim);

        }
        if ($strip_tags) {
            foreach ($out as $key => $value) {
                $out[$key] = trim(strip_tags($value));
            }
        }
        return $out;
    }
}
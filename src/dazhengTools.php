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

    public static function isQy($com_type = null)
    {
        return in_array($com_type !== null ? $com_type : Args::val("com_type"), [2, 4, 6]);
    }


    public static function isSheLi($com_type = null)
    {
        return in_array($com_type !== null ? $com_type : Args::val("com_type"), [1, 2]);
    }

    public static function isBianGeng($com_type = null)
    {
        return in_array($com_type !== null ? $com_type : Args::val("com_type"), [3, 4]);
    }

    public static function isFaRen($user_type = null)
    {
        return ($user_type !== null ? $user_type : Args::val("user_type")) == 1;
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

    public static function getByIndex($string, $index, $fenge = "fengehao")
    {
        if (!$string) return $string;
        $tmp = explode($fenge, $string);
        if (isset($tmp[$index])) {
            return $tmp[$index];
        }
        return $string;
    }

    public static function changeUserInfo($com_type, &$username, &$password, &$login_userId, &$login_deptId)
    {
        $index = !self::isQy($com_type) ? 0 : 1;
        $username = self::getByIndex($username, $index);
        $password = self::getByIndex($password, $index);
        $login_userId = self::getByIndex($login_userId, $index);
        $login_deptId = self::getByIndex($login_deptId, $index);
        self::str_replace_user_info($password, $login_userId);

    }

    private static function str_replace_user_info(&$password, &$login_userId)
    {
        $login_userId = str_replace("sangejing", "###", $login_userId);
        $password = str_replace(["jiahao"], ["+"], $password);
    }


    public static function getArgsForClient()
    {
        $args = array_merge(Args::configVal(), Args::optVal(), Args::argVal());
        if ($dataurl = Args::optVal("data-url")) {
            $args = array_merge($args, json_decode(file_get_contents($dataurl), true));
            if (!$args) {
                self::error("数据源格式错误", -22);
            }
        }


        self::str_replace_user_info($args["password"], $args["login_userId"]);

        if (isset($args["phone"])) {

            if (count($arr_tmp = explode("-", $args["phone"])) == 2) {
                $args["phone"] = $arr_tmp[0];
                $args["company_name"] = trim($arr_tmp[1]);
            } else {
                $args["company_name"] = "";
            }
            if (isset($args["id_card_name"])) {
                if (count($arr_tmp = explode("-", $args["id_card_name"])) == 2) {
                    $args["id_card_name"] = $arr_tmp[0];
                    $args["faren"] = trim($arr_tmp[1]);
                } else if (count($arr_tmp = explode("-", $args["id_card_name"])) >= 3) {
                    $args["id_card_name"] = $arr_tmp[0];
                    for ($i = 1; $i < count($arr_tmp); $i = $i + 2) {
                        $args[$arr_tmp[$i]] = $arr_tmp[$i + 1];
                    }
                } else {
                    $args["faren"] = $args["id_card_name"];
                }
            }
        }

        if (isset($args["code"]))
        {
            if (count($arr_tmp = explode("--", $args["code"])) >= 3) {
                $args["code"] = $arr_tmp[0];
                for ($i = 1; $i < count($arr_tmp); $i = $i + 2) {
                    $args[$arr_tmp[$i]] = $arr_tmp[$i + 1];
                }
            }
        }
        if (isset($args["login_userId"]))
            foreach (["username", "password", "login_userId", "login_deptId"] as $item) {
                $args[$item] = self::getByIndex($args[$item], (!self::isQy()) ? 0 : 1);
                if (isset($args['user_index'])) {
                    $args[$item] = self::getByIndex($args[$item], $args['user_index'], "MULT");
                }
                Args::setValue($item, $args[$item]);

            }
        return $args;

    }


    public static function md5PostData(&$post_data_array, $just_md5 = false)
    {
        foreach (["faren_name", "faren_idcard", "faren_phone", "weituoren_name", "weituoren_idcard", "weituoren_phone"] as $item) {

            $post_data_array[$item . "_md5"] = (isset($post_data_array[$item]) && $post_data_array[$item]) ? md5($post_data_array[$item] . "#wenshi") : "";
            if ($just_md5)
                $post_data_array[$item] = "";
        }
    }


}
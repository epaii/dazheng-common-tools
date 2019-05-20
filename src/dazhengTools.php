<?php
/**
 * Created by PhpStorm.
 * User: mrren
 * Date: 2019/5/20
 * Time: 2:00 PM
 */

namespace epii\dazheng\tools;

use epii\server\Args;

class dazhengTools
{
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
}
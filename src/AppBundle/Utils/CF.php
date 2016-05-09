<?php
namespace AppBundle\Utils;
/**
 * Created by PhpStorm.
 * User: Jon
 * Date: 4/23/2016
 * Time: 6:44 AM
 */

class CF
{    
    public static function getType($type)
    {
        switch ($type) {
            case "1":
                $type_name = "title.field";
                break;
            case "2":
                $type_name = "title.project";
                break;
            case "3":
                $type_name = "title.news";
                break;
            case "4":
                $type_name = "title.recruit";
                break;
        }
        return array('code'=>$type,'name' => $type_name );
    }
}
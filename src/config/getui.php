<?php
/**
 * Created by PhpStorm.
 * User: AltriaPendragon
 * Date: 2018/12/15
 * Time: 9:51
 */
return [
    /**
     * 个推配置
     */
    'getui'=>[
         'appKey'=> env('GT_APPKEY',''),
         'appId'=>env('GT_APPID',''),
         'masterSecret'=>env('GT_MASTERSECRET',''),
         'appSecret'=>env('GT_APPSECRET',''),
         'host'=>env('GT_HOST',''),
    ]

];
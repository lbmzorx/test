<?php
/**
 * Created by PhpStorm.
 * User: aa
 * Date: 2017/10/18
 * Time: 11:05
 */
//跨域session域名配置,获取当前主机名
$host_array = explode('.', $_SERVER["HTTP_HOST"]);
//针对com域名，获取顶级域名
if (count($host_array) == 3) {
define('DOMAIN', $host_array[1] . '.' . $host_array[2]);
}
//针对com.cn域名
elseif (count($host_array) == 4) {
define('DOMAIN', $host_array[1] . '.' . $host_array[2]. '.' . $host_array[3]);
}
else{
//echo "本系统不支持本地访问，请配置域名";exit;
}
define('DOMAIN_HOME', 'www.' . DOMAIN);
define('DOMAIN_YUN', 'explore.' . DOMAIN);
define('DOMAIN_API', 'fronttest.' . DOMAIN);
define('DOMAIN_EMAIL', 'mail.' . DOMAIN);
define('DOMAIN_IMG', 'img.' . DOMAIN);

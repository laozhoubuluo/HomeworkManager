<?php
/*

作业上传系统独立精简版 v1
原始版本作者：CopyRight(C) 程式设计 Coding axer@tc.edu.tw 20120216-0314
修改版本作者：CopyRight(C) 老周部落
版权宣告：本程式遵从GNUv3规范 http://www.gnu.org/licenses/gpl.html

init 程式的初始档，包括建立smarty物件、资料库等。

*/
// 程式的初始档，包括建立smarty物件、资料库等。
// 安全性检查
if (__FILE__ == ''){ die('Fatal error code: 0'); }

// ----==== 程式初始档开始 ====-----
define('DB_NAME', "homework"); //资料库名

define('DB_ADDR', "localhost");  //资料库ip

define('DB_USR', "homework");  // 资料库使用者

define('DB_PWD', "12345678");  //资料库密码

define('SUPER_PASSWD', "12345678");  //最高权限user 密码，后台添加用户时会用到

define('SendAllEmail', 0);  //Set 1 to enable email

define('SITE_CNAME', "作业管理系统欢迎您！");  //网站标头

define('SITE_DN', $_SERVER['HTTP_HOST']); //网域名或IP，请勿加上'/'

define('SITE_URL', "http://". SITE_DN ."/homework/");  // 修改网站位址，请保留字串最后的 '/'

define('UPDIR', "upload/");

define('UPLOAD_DIR', "/var/www/html/homework/".UPDIR ); //修改上传位址

define('TEMP_PATH', "temp/");

define('UPLOAD_TEMP_DIR', UPLOAD_DIR . TEMP_PATH);  

define('UPURL', SITE_URL. UPDIR);  

define('RANK_DIFFERENCE', 1); // 排序差值大小

define('PAGE_ENCRYPT_KEY',"QQHW589632"); //全页加密码，勿更动

define('MAX_IMG_SIZE', 16777210); //16MB 最大上传档案大小

define('MAX_IMG_WIDTH', 2400);  // 上传图片最大宽

define('MAX_IMG_HEIGHT', 2400);

define('ROWS_PER_PAGE', 30);    //页面一页笔数

define('ROWS_EXHIBITION', 40);    //展示页面笔数

define('SEMESTER_NUM',2); // 学期制 2 or 3

define('SEMESTER1', 8); // 学期1起始月份

define('SEMESTER2', 2); // 学期2起始月份

define('HWPREFIX', 'hw'); // 作业目录字首xx，产生的目录会是 xx1, xx2, xx3...

define('RESET_PWD', '12345678');   // 修改学生的密码时之预设新密码

// ========== 程式设定结束 ===============
// 以下内容请勿任意修改，否则会造成程式损害

define('ROOT_PATH', str_replace('includes/init.php', '', str_replace('\\', '/', __FILE__)));
// ex. ROOT_PATH=/home/plurkgo/public_html/ss/

@ini_set('memory_limit',          '64M');
@ini_set('session.cache_expire',  60);
@ini_set('session.use_trans_sid', 0);
@ini_set('session.use_cookies',   1);
@ini_set('session.auto_start',    0);
@ini_set('display_errors',        1);
@ini_set('include_path', '.:' . ROOT_PATH); //引入路径

include "libs/Smarty.class.php";      //读入SMARTY函式库(2.6.26 June 18th, 2009)
include "adodb5/adodb.inc.php";    //读入ADODB的类别函式库 V5.09a (2009.6.26)
include "base.class.php";        //读入基础的function

//Database
$DB = NewADOConnection('mysqli');//Using MySQLi
$DB->Connect(DB_ADDR, DB_USR, DB_PWD, DB_NAME);
$DB->Execute("set names utf8");

//Smarty
$view = new Smarty();
define('SMARTY_DIRECTOR', 'libs');
$view->template_dir = SMARTY_DIRECTOR . "/templates/";
$view->compile_dir = SMARTY_DIRECTOR . "/templates_c/";
$view->config_dir = SMARTY_DIRECTOR . "/configs/";
$view->cache_dir = SMARTY_DIRECTOR . "/cache/";
$view->left_delimiter = '{{';
$view->right_delimiter = '}}';

//启动 session
session_start();
$f= (empty($_POST["f"]))? (empty($_GET["f"])? "":$_GET["f"]):$_POST["f"];
$f=trim($f);    //hacking proof

//错误字串
$PRIV_ERR ="你的操作不被允许，权限不足？";
$CLOSE_WINDOW = "，按此<a href='javascript:window.close();'>关闭视窗</a>";

?>

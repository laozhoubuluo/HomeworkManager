<?php
// 认证码产生程式 ---Program coded by 
// http://jax-work-archive.blogspot.com/2007/11/php.html?showComment=1232821260000#c4000714540630615955
// Revised By uhoo.tw@gmail.com
// 自传认证码 990401，若无传入 v 值，则自行产生乱数，并以Session作认证；否则由传入v值产生认证碥，并以v值作认证
// v 值为数字串藉由 LongEncode() 转成为密文显示

// 认证图片宽
$imageWidth = 120;
$imageHeight = 32;
define("DEFAULT_WD_NUM", 3);  // 影响动态非传值位数

header("Content-type:image/png");
header("Content-Disposition:filename=image_code.png");
//定义 header 的文件格式为 png，第二个定义其实没什麽用


$wd=DEFAULT_WD_NUM;  //字数
// 由取得值产生 captacha
if(! empty($_GET['v'])){
//  include 'init.php';
  include 'base.class.php';
  $obj = new Bila_base_class();
  $verification__session= substr(strtoupper( $obj->LongEncode($_GET['v'])),0,$wd);
  //$wd= strlen($verification__session);
  $imageWidth= $wd *32+30;
  $imageHeight = 36;
}
else{
  // 开启 session
  session_start();

  // 设定乱数种子
  mt_srand((double)microtime()*1000000);

  // 验证码变数
  $verification__session = '';

  // 定义显示在图片上的文字，可以再加上大写字母
  $str = 'ACDEFGHJKLMNPQRSTUVWXYZ23456789';

  $l = strlen($str); //取得字串长度

  //随机取出 $wd 个字
  for($i=0; $i<$wd; $i++){
     $num=rand(0,$l-1);
     $verification__session.= $str[$num];
  }

  // 将验证码记录在 session 中
  $_SESSION["simg_code"] = $verification__session;
}


// 建立图片物件
$im = @imagecreatetruecolor($imageWidth, $imageHeight)
or die("无法建立图片！");


//主要色彩设定
// 图片底色
//$bgColor = imagecolorallocate($im, rand(200,255),rand(200,255),rand(200,255));
$bgColor = imagecolorallocate($im, 252,237 ,240);

//设定图片底色
imagefill($im,0,0,$bgColor);

//底色干扰线条
for($i=0; $i<10; $i++){

	$gray1 = imagecolorallocate($im, rand(100,255),rand(100,255),rand(100,255));
   imageline($im,rand(0,$imageWidth),rand(0,$imageHeight),
   rand($imageHeight,$imageWidth),rand(0,$imageHeight),$gray1);
}

//利用true type字型来产生图片
for($ii=0; $ii< $wd; $ii++){
	$Color = imagecolorallocate($im, rand(0,150), rand(0,150) , rand(0,150));
	$this_wd = substr ($verification__session, $ii, 1);
	imagettftext($im, 15+rand(0,10), -15+rand(0,30), 20+ 32*$ii, 25, $Color, "ariblk.ttf", $this_wd);
}
/*
imagettftext (int im, int size, int angle,
int x, int y, int col,
string fontfile, string text)
im 图片物件
size 文字大小
angle 0度将会由左到右读取文字，而更高的值表示逆时钟旋转
x y 文字起始座标
col 颜色物件
fontfile 字形路径，为主机实体目录的绝对路径，
可自行设定想要的字型
text 写入的文字字串
*/

// 干扰像素
for($i=0;$i<30;$i++){
	$gray2 = imagecolorallocate($im, rand(60,255),rand(60,255),rand(60,255));
   	imagesetpixel($im, rand()%$imageWidth ,
   	rand()%$imageHeight , $gray2);
}

imagepng($im);
imagedestroy($im);
?>


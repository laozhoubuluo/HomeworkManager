<?php
/*
作业上传系统独立精简版 v1
原始版本作者：CopyRight(C) 程式设计 Coding axer@tc.edu.tw 20120216-0314
修改版本作者：CopyRight(C) 老周部落
版权宣告：本程式遵从GNUv3规范 http://www.gnu.org/licenses/gpl.html 

认证管理程式

*/

include "includes/init.php";
include "includes/auth.class.php";

$obj = new Auth_class();
$obj->DB= $DB;
$obj->f = $f;
$obj->SetSession($_SESSION);
// $obj->InitAllCatArr();

//For index only
$view->caching = 0;
$view->compile_check = true;
$view->cache_lifetime = 10800; //3 hours

if( !isset($_SESSION['priv'])) $_SESSION['priv'] = $obj->GetNoLoginPrivArr();
$view->assign('obj', $obj);
$view->assign('f', "HW");

switch($f){
  case "LoginChk":
    $wt=5000;
    if( empty( $_POST["validate"])){ $msg =  "认证码未填！5 秒后导至首页"; }
    else{
      $validcode = $_POST["validate"];
      if($validcode !== "好" ) $msg =  "您输入的认证码{$validcode}错误，无法送出！";
      else{
        $IsOk =$obj->Member_Auth( $_POST["email"], md5($_POST["passwd"]));
        if( $IsOk>0 ){ $msg ="登入成功"; $wt=1000; }
        else{
          if($IsOk==-3)$msg = "登入失败： 帐号密码错误";
          else $msg = "登入失败 Err". $IsOk;
        }
      }
    }
    if( !empty($_POST['r']))$url=$_POST['r'];
    else $url= SITE_URL . "manage.php?f=Homework";
    $msg .= $obj->JS_CntDn( $url, $wt);
    $view->assign('msg', $msg);
    $view->display('Message.mtpl');
    break;

  case "Logout":
    $_SESSION=array();
    session_destroy();
    $obj->SetSession($_SESSION);    //登出后再传 _SESSION 传递给物件
    $msg ="您已经顺利登出，欢迎下次再来。";
    $msg .= $obj->JS_CntDn(  SITE_URL, 1000);
    $view->assign('msg', $msg);
    $view->display('Message.mtpl');
    break;

  default:
    $view->display('Index.mtpl');
    break;
}

?>



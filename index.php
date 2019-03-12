<?php
/*
作业上传系统独立精简版 v1
原始版本作者：CopyRight(C) 程式设计 Coding axer@tc.edu.tw 20120216-0314
修改版本作者：CopyRight(C) 老周部落
版权宣告：本程式遵从GNUv3规范 http://www.gnu.org/licenses/gpl.html 

首页管理程式

*/

include "includes/init.php";
include "includes/index.class.php";

// Enable Class
$obj = new index_class();
$obj->DB= $DB;
$obj->f = $f;
$obj->SetSession($_SESSION);

//For index only
$view->caching = 0;
$view->compile_check = true;
$view->cache_lifetime = 10800; //3 hours

if( !isset($_SESSION['priv'])) $_SESSION['priv'] = $obj->GetNoLoginPrivArr();
$view->assign('obj', $obj);
$view->assign('f', $f);
switch($f){
  case "About":
    $currDT =date("Y-m-d"); 
    //if( $currDT <= '2012-02-29')print "YES";
    $view->display('About.mtpl');
    break;
  case "Exhibition":
    $view->display('Exhibition.mtpl');
    break;
  case "HwDetail":
    $view->assign('f', "List");
    $sn=0;
    $_SESSION['currURL']= $_SERVER['REQUEST_URI'];
    if( !empty($_GET["c"])){
      $sn= $obj->LongDecode($_GET["c"]);
      $view->assign('c', $_GET["c"]);
    }
    if($sn<=0){
      $msg="错误的作业编号";
      $msg .= $obj->JS_CntDn( SITE_URL, 2000);
      $view->assign('msg', $msg);
      $view->display('Message.mtpl');
      break;
    }
    $url = urlencode(SITE_URL . $_SERVER['REQUEST_URI']);
    $view->assign('url', $url);
    $view->assign('hID', $sn);
    $view->assign('maxsize',ini_get("upload_max_filesize"));
    $view->display('HwPage.mtpl');
    break;
  case "Login":
    $view->display('LoginPage.mtpl');
    break;
  default:
    $view->assign('f', "List");
    $view->caching = 0;
    if( empty($_POST['c']) &&  empty($_GET['c'])){
      $tbl=array(
        "pg"=>1,
        "hkw"=> empty($_POST["hkw"])?"": trim(addslashes($_POST["hkw"])),
        "s" => empty( $_POST["hs"])?"": $_POST["hs"],
      );
    }else{
      $c =empty($_POST['c'])? $_GET['c']:$_POST['c'];
      $tbl=  $obj->Decrypt_c2Arr( $c );
      if(! isset($tbl['pg']))$tbl['pg']=1;
    }
    if(! empty($tbl['s'])){ //_kw=1,_date=1,_state=1,state=0
      $s= $tbl['s'];
      $srr = explode(",",$s);
      foreach($srr as $it){
        preg_match('/(\w+)=(\w+)/', $it, $match);
        $k= $match[1];
        $v= $match[2];
        $tbl[$k]=$v;
      }
    }
    if(isset($tbl['stp'])) $stp= (int)$tbl['stp'];
    else $stp= empty($_POST['stp'])?(empty($_GET['stp'])?0:(int)$_GET['stp']):(int)$_POST['stp'];
    $tbl['odr']= empty($_GET['odr'])?(empty($tbl['odr'])?0:$tbl['odr']):(int)$_GET['odr'];  //default no orderb
    unset($tbl['s']);
    $obj->SetCurrParam($tbl);
    unset($tbl['stp']);  //clear
    $c=  $obj->Encrypt_Arr2c($tbl);
    $view->assign('c', $c);
    $view->display('Index.mtpl');
    break;
}

?>



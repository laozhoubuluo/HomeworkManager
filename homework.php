<?php
/*
作业上传系统独立精简版 v1
原始版本作者：CopyRight(C) 程式设计 Coding axer@tc.edu.tw 20120216-0314
修改版本作者：CopyRight(C) 老周部落
版权宣告：本程式遵从GNUv3规范 http://www.gnu.org/licenses/gpl.html 

使用者作业管理程式

*/

include "includes/init.php";
include "includes/homework.class.php";

$obj = new Homework_class();
$obj->DB= $DB;
$obj->f = $f;
$obj->SetSession($_SESSION);
// $obj->InitAllCatArr();

//For index only
$view->caching = 0;
//$view->compile_check = true;
//$view->cache_lifetime = 10800; //3 hours
$view->assign('obj', $obj);
$view->assign('f', "HW");

switch($f){
  case "ChkCanUpload":  //AJAX
    $hID =  (int)$_POST['sn'];
    $upPasswd= isset( $_POST['p'])?$_POST['p']:"";
    print $IsOk= $obj->CheckCanUpload($hID, $upPasswd);
    break;
  case "DlHwIframe":
    $sn =  (int)$obj->LongDecode($_GET['c']);
    $obj->SendFile2Browser($sn);
    break;
  case "DoMyHw":
    $wt=5000;
    $sn =  (int)$obj->LongDecode($_POST['c']);
    $crypt = md5($_POST['passwd']);
    $IsOk= $obj->CheckHwPasswd($sn, $crypt);
    if($IsOk <0){
      $msg="密码错误，无法操作 Err{$IsOk}";
      $msg .= $obj->JS_CntDn( "{$_SESSION['currURL']}" , $wt);
      $view->assign('msg', $msg);
      $view->display('Message.mtpl');
      break;
    }
    switch($_POST['o']){
      case 'd':
        $IsOk =$obj->ProcDelOneUploadHw( $sn );
        if( $IsOk>0 ) { $msg ="档案删除成功 <br />"; $wt=2000; }
        else $msg = "档案删除失败 Err{$IsOk}";
        break;
      case 'dl':
        $view->assign('sn', $sn);
        $view->assign('c', $_POST['c']);
        $view->display('HwDownloadPage.mtpl');
        exit; //中止
        break;
      case 'm':
        $view->assign('sn', $sn);
	    $view->display('HwModPage.mtpl');
        exit; //中止 
        break;
      default:
        $msg="不明的操作错误Err-11";
        break;
    }   
    $msg .= $obj->JS_CntDn( "{$_SESSION['currURL']}" , $wt);
    $view->assign('msg', $msg);
    $view->display('Message.mtpl');
    break;
  case "ModMyHw":
    $sn = (int)$obj->LongDecode($_POST['snc']);
    $arr= array();
    $row = $obj-> GetOneUploadHw($sn);
    //判断作业编号是否正确
    if(!isset($row['hID']) || $row['hID']<= 0){
      $msg="错误的作业编号Err-12";
      $msg .= $obj->JS_CntDn( "{$_SESSION['currURL']}" , 5000);
      $view->assign('msg', $msg);
      $view->display('Message.mtpl');
      break;
    }
    //判断作业是否在可上传状态
    $ret = $obj-> GetOneHw($row['hID']);
    if ($ret['canUpload'] == 0){
      $msg="档案修改失败，非上传时间Err-3";
      $msg .= $obj->JS_CntDn( "{$_SESSION['currURL']}" , 5000);
      $view->assign('msg', $msg);
      $view->display('Message.mtpl');
      break;
    }
    //从POST请求中获取学号、姓名
    $cid = mysqli_real_escape_string($obj->DB->_connectionID, trim( $_POST['cid'] ));//学号
    $cname = mysqli_real_escape_string($obj->DB->_connectionID, trim( $_POST['cname'] ));//姓名
    if(!$obj-> CheckCidByRegex($cid, $row['hID'])){
      $msg="您输入的学号不符合管理员在后台设置的正则表达式，请重新输入！";
      $msg .= $obj->JS_CntDn( "{$_SESSION['currURL']}" , 5000);
      $view->assign('msg', $msg);
      $view->display('Message.mtpl');
      break;
    }
    if($_FILES['MyFile']['size']>0){ // 有上传新档
      //通过数据库获取作业标题，并将其传送给ProcUpFiles, 作业标准化命名需求
      $title = $obj-> GetHwTitle($row['hID']);
      $imgDir = HWPREFIX ."{$row['hID']}/";   //ex: 2008DecMedia/
      $IsOk= $obj->ProcUpFiles($_FILES['MyFile'], $imgDir, $rrr, $title, $cid, $cname);
      if( $IsOk >0){ $arr= $rrr; }
      else $msg="档案上传失败 Err{$IsOk}";
    } else {
      $hwcheck = $obj-> GetOneUploadHw($sn);
      if ($hwcheck['cid'] != $cid || $hwcheck['cname'] != $cname){
        $msg="如需修改学号及姓名，请重新上传文档！";
        $msg .= $obj->JS_CntDn( "{$_SESSION['currURL']}" , 5000);
        $view->assign('msg', $msg);
        $view->display('Message.mtpl');
        break;
	  } else {
        $IsOk = 1;//无上传文件直接标记为成功
      }
    }
    $arr['sn']= $sn;
    $arr['modPasswd']= $_POST['passwd'];
    $arr['remark']= mysqli_real_escape_string($obj->DB->_connectionID, trim( $_POST['remark'] ));
    $arr['cid']= mysqli_real_escape_string($obj->DB->_connectionID, trim( $_POST['cid'] ));//学号
    $arr['cname']= mysqli_real_escape_string($obj->DB->_connectionID, trim( $_POST['cname'] ));//姓名
    $arr['uDT']=time();
    if( !$row)$msg = "修改失败，参数错误 Err-13";
    else {
      if( $IsOk>0 ){ $IsOk =$obj->ProcModMyHw ( $arr ); }//文件上传成功再修改数据库
      if( $IsOk>0 ){ $msg ="档案修改成功 <br />"; }
      else $msg = "档案修改失败 Err{$IsOk}";
    }
    $msg .= $obj->JS_CntDn( SITE_URL ."?f=HwDetail&c={$obj->LongEncode($row['hID'])}" , 5000);
    $view->assign('msg', $msg);
    $view->display('Message.mtpl');
    break;
  case "UploadHw":
    $hID = (int)$_POST['hID'];
    $upPasswd= isset( $_POST['upPasswd'])?$_POST['upPasswd']:"";
    $IsOk= $obj->CheckCanUpload($hID, $upPasswd);
    if( $IsOk <=0){
      if($IsOk ==-3) $msg ="档案上传失败，非上传时间 Err{$IsOk}";
      elseif($IsOk ==-4) $msg ="档案上传失败，上传密码错误 Err{$IsOk}";
      else $msg= "档案上传失败 Err{$IsOk}";
      $msg .= $obj->JS_CntDn( SITE_URL . "?f=HwDetail&amp;c={$obj->LongEncode($hID)}" , 5000);
      $view->assign('msg', $msg);
      $view->display('Message.mtpl');
      break;
    }
    //从POST请求中获取学号、姓名，通过数据库获取作业标题，并将其传送给ProcUpFiles, 作业标准化命名需求
    $cid = mysqli_real_escape_string($obj->DB->_connectionID, trim( $_POST['cid'] ));//学号
    $cname = mysqli_real_escape_string($obj->DB->_connectionID, trim( $_POST['cname'] ));//姓名
    $title = $obj-> GetHwTitle($hID);
    if(!$obj-> CheckCidByRegex($cid, $hID)){
      $msg="您输入的学号不符合管理员在后台设置的正则表达式，请重新输入！";
      $msg .= $obj->JS_CntDn( "{$_SESSION['currURL']}" , 5000);
      $view->assign('msg', $msg);
      $view->display('Message.mtpl');
      break;
    }
    if($obj-> CheckUploadStatusByCid($cid, $hID)){
      $msg="本学号已经上传过作业，请使用编辑功能或删除后重新上传！";
      $msg .= $obj->JS_CntDn( "{$_SESSION['currURL']}" , 5000);
      $view->assign('msg', $msg);
      $view->display('Message.mtpl');
      break;
    }
    $imgDir = HWPREFIX .$hID. "/";   //ex: xx00/
    $IsOk= $obj->ProcUpFiles($_FILES['MyFile'], $imgDir, $rrr, $title, $cid, $cname);
    $msg="";
    if( $IsOk >0){
      $arr=$rrr;
      $arr['hID']=$hID;
      $arr['modPasswd']= $_POST['passwd'];
      $arr['remark']= mysqli_real_escape_string($obj->DB->_connectionID, trim( $_POST['remark'] ));
      $arr['cid']= mysqli_real_escape_string($obj->DB->_connectionID, trim( $_POST['cid'] ));
      $arr['cname']= mysqli_real_escape_string($obj->DB->_connectionID, trim( $_POST['cname'] ));
      $arr['cDT']=time();
      $arr['uDT']=$arr['cDT'];  //第一次上传时，更新时间与新增时间相同
      $IsOk =$obj->ProcAddHwUpload( $arr );
      if( $IsOk>0 ) $msg .="档案上传储存成功 <br />";
      else $msg .= "档案上传储存失败 Err{$IsOk}";
    }else{
      if($IsOk ==-1) $msg ="档案传输错误 Err{$IsOk}";
      elseif($IsOk ==-4) $msg ="档案类型不被允许 Err{$IsOk}";
      else $msg= "目录建立失败，请检查目录权限是否可供写入 Err{$IsOk}";
    }
    $msg .= $obj->JS_CntDn(  SITE_URL . "?f=HwDetail&c={$obj->LongEncode($hID)}" , 3000);
    $view->assign('msg', $msg);
    $view->display('Message.mtpl');
    break;
  case "View":
    $sn = (int)$obj->LongDecode($_GET['c']);
    $IsOk= $obj->SendFile2Browser($sn);   
    break;
  default:
    $msg = "连结错误操作，一秒后导至首页{$f}".  $obj->JS_CntDn(  SITE_URL ,10000);
    $view->assign('msg', $msg);
    $view->display('Message.mtpl');
    break;
}

?>



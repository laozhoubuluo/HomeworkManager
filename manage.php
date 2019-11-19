<?php
/*
作业上传系统独立精简版 v1
原始版本作者：CopyRight(C) 程式设计 Coding axer@tc.edu.tw 20120216-0314
修改版本作者：CopyRight(C) 老周部落
版权宣告：本程式遵从GNUv3规范 http://www.gnu.org/licenses/gpl.html 

作业管理程式

*/

include "includes/init.php";
include "includes/manage.class.php";

$obj = new Manage_class();
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
if( $f=="AccManage")$view->assign('f', "AccManage");
else $view->assign('f', "HW");

if( empty($_SESSION['email'])){ $view->display('LoginPage.mtpl'); exit; }
switch($f){
  case "AccManage":
    $view->display('AccManage.mtpl');  
    break;
  case "AddAccount":
    $sup = $_POST['superpasswd'];
    if( $sup !== SUPER_PASSWD ){ $IsOk =-12; $msg="超级密码错误！ Err-12"; }
    else{
      $arr= array(
        'email' => $_POST['email'],
        'shadow' => md5($_POST['email']),
        'passwd' => md5($_POST['passwd']),
        'usID' => mysqli_real_escape_string($obj->DB->_connectionID, $_POST["usID"]),
        'cname' => mysqli_real_escape_string($obj->DB->_connectionID, $_POST["cname"]),
        "regDT" => date("Y-n-d H:i:s")
      );
      $IsOk =$obj->ProcAdmAddMember( $arr );
      $msg="伺服器状态异常，新增失败！ Err{$IsOk}";
    }
    if( $IsOk >0 ) $msg="会员新增成功";
    elseif( $IsOk ==-4 ) $msg="帐号已经存在";
    $msg .= $obj->JS_CntDn( SITE_URL. "manage.php?f=AccManage" , 4000);
    $view->assign('msg', $msg);
    $view->display('Message.mtpl');
    break;
  case "ChangeHomework":
    if( !isset($_POST['stp'])){ $view->display('AddHwPage.mtpl'); break; }
    $stp= (int)$_POST['stp'];
    $c= empty($_POST['c'])?"":$_POST['c'];
    $rank=(int)$_POST["rank"];
    if($rank>99)$rank=99;elseif($rank<0)$rank=0;
    $arr=array(
      "hwTitle"=> mysqli_real_escape_string($obj->DB->_connectionID, trim($_POST["hwTitle"])),
      "hwO"=> mysqli_real_escape_string($obj->DB->_connectionID, trim($_POST["hwO"])),
      "email"=> $_SESSION['email'],
      "passwd"=> mysqli_real_escape_string($obj->DB->_connectionID, $_POST["passwd"]),
      "classID"=> mysqli_real_escape_string($obj->DB->_connectionID, $_POST["classID"]),
      "fromDT" => empty( $_POST["from"])?"": $_POST["from"],
      "dueDT" => empty($_POST["to"])?"": $_POST["to"],
      "remark" => addslashes($_POST["remark"]),
      "closed" => (int)$_POST["closed"],
      "display" => (int)$_POST["display"],
      "upPasswd" => (int)$_POST["upPasswd"],
      "rank" =>$rank,
      "cDT" => date("Y-n-d H:i:s"),
      "cidRegex"=> mysqli_real_escape_string($obj->DB->_connectionID, trim($_POST["cidRegex"])),
      "FileNameFormat"=> mysqli_real_escape_string($obj->DB->_connectionID, trim($_POST["fileNameFormat"])),
      "FolderNameFormat"=> mysqli_real_escape_string($obj->DB->_connectionID, trim($_POST["folderNameFormat"]))
    );
    if($stp==3){ $IsOk =$obj->ProcAddHw( $arr );
      if( $IsOk <=0 )$msg="伺服器状态异常，写入终止！ Err{$IsOk}";
      else $msg="新增作业成功";
    }elseif($stp==5){
      $arr['hID']= (int)$_POST["hID"];
      $IsOk =$obj->ProcModHw( $arr );
      if( $IsOk <=0 )$msg="伺服器状态异常，写入终止！ Err{$IsOk}";
      else $msg="修改作业成功";
    }
    $msg .= $obj->JS_CntDn( SITE_URL . "manage.php?f=Homework&amp;c={$c}" , 4000);
    $view->assign('msg', $msg);
    $view->display('Message.mtpl');
    break;
  case "ChangeHwFilePWD": //变更学生上传档案之存取密码
	$wt=2000;

    if(isset($_POST['passwd']) and trim($_POST['passwd'])<>""){
		$sn = (int)$obj->LongDecode($_POST['c']);
		$IsOk =$obj->ProcChangeUpHwAttr($sn, "modPasswd", trim($_POST['passwd']));
		if( $IsOk <=0 )$msg="伺服器状态异常，写入终止！ Err{$IsOk}";
		else $msg="重设密码成功";
	} else {
		$wt=5000;
		$msg = "未输入新密码";
	}
	$msg .= $obj->JS_CntDn( $_SESSION['currURL'] , $wt);
    $view->assign('msg', $msg);
    $view->display('Message.mtpl');
    break;
  case "DelHw":
    $wt=5000;
    if( isset($_GET['c'])){
      $sn =  (int)$obj->LongDecode($_GET['c']);
     include "includes/homework.class.php";
      $obj2 = new Homework_class();
      $obj2->DB= $DB;
      $obj2->SetSession($_SESSION);
      $IsOk =$obj2->ProcDelOneUploadHw( $sn );
      unset( $obj2);
      if( $IsOk>0 ) { $msg ="档案删除成功 <br />"; $wt=2000; }
      else $msg = "档案删除失败 Err{$IsOk}";
    }
    else $msg = "错误的操作 Err-11";
    $msg .= $obj->JS_CntDn( $_SESSION['currURL'] , $wt);
    $view->assign('msg', $msg);
    $view->display('Message.mtpl');
    break;
  case "DelAccount":
    $wt=1000;
    $sup = $_POST['superpasswd'];
    if( $sup !== SUPER_PASSWD ){ $IsOk =-12; $msg="超级密码错误！ Err-12"; }
    else{
      $IsOk =$obj->ProcDelMember( $_POST['email'] );
      if( $IsOk <=0 ){$msg="伺服器状态异常，写入终止！ Err{$IsOk}"; $wt=3000;}
      else $msg="会员删除成功";
    }
    $msg .= $obj->JS_CntDn( SITE_URL. "manage.php?f=AccManage", $wt, false);
    $view->assign('msg', $msg);
    $view->display('Message.mtpl');
    break;
  case "FindUpHwPasswd": //查询学生作业档案密码
	$thePasswd="";
	if(isset($_POST['c']) and trim($_POST['c'])<>""){
		$sn = (int)$obj->LongDecode($_POST['c']);
		$sql="select `modPasswd` from `hwUpload` where `sn`='".$sn."'";
		$thePasswd=$obj->DB->GetOne($sql);
	}
	echo $thePasswd;
	break;
  case "Homework":
    if( empty($_POST['c']) &&  empty($_GET['c'])){
      $tbl=array(
        "pg"=>1,
        "kw"=> empty($_POST["kw"])?"": trim(addslashes($_POST["kw"])),
        "s" => empty( $_POST["s"])?"": $_POST["s"],
        "from" => empty( $_POST["from"])?"": $_POST["from"],
        "to" => empty($_POST["to"])?"": $_POST["to"],
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
    $tbl['odr']= empty($_GET['odr'])?(empty($tbl['odr'])?0:$tbl['odr']):(int)$_GET['odr'];  //default no orderby
    $obj->SetCurrParam($tbl);
    unset($tbl['stp']);  //clear
    $c=  $obj->Encrypt_Arr2c($tbl);
    $view->assign('c', $c);
    switch($stp){
      case 2: //Modi
        $sn= $tbl['sn'];
        $view->assign('hID', $sn);
        $view->display('ModHwPage.mtpl');
        break;
      case 3: //Save Modi
        break;
      case 4: //close
        $sn= $tbl['sn'];
        $IsOk = $obj->ProcChangeHwAttr($sn, "closed");
        if( $IsOk <=0 )$msg="伺服器状态异常，写入终止！ Err{$IsOk}";
        else $msg="修改作业属性[有效]成功";
        $msg .= $obj->JS_CntDn( SITE_URL ."manage.php?f=Homework&amp;c={$c}" , 2000);
        $view->assign('msg', $msg);
        $view->display('Message.mtpl');
        break;
      case 5: //display
        $sn= $tbl['sn'];
        $IsOk = $obj->ProcChangeHwAttr($sn, "display");
        if( $IsOk <=0 )$msg="伺服器状态异常，写入终止！ Err{$IsOk}";
        else $msg="修改作业属性[展示]成功";
        $msg .= $obj->JS_CntDn(  SITE_URL ."manage.php?f=Homework&amp;c={$c}" , 2000);
        $view->assign('msg', $msg);
        $view->display('Message.mtpl');
        break;
      case 6: //delete
        $sn= $tbl['sn'];
        $IsOk = $obj->ProcDelHw($sn);
        if( $IsOk <=0 )$msg="伺服器状态异常，写入终止！ Err{$IsOk}";
        else $msg="删除作业成功，删除目录：". HWPREFIX. $sn;
        $msg .= $obj->JS_CntDn(  SITE_URL ."manage.php?f=Homework&amp;c={$c}", 4000);
        $view->assign('msg', $msg);
        $view->display('Message.mtpl');
        break;
      case 7: //hw Manage
        $_SESSION['currURL']= $_SERVER['REQUEST_URI'];
        $sn= $tbl['sn'];
        $view->assign('hID', $sn);
        $view->display('ManageUpHw.mtpl');
        break;
      case 9: //Dl hw
        $sn= $tbl['sn'];
        $wt=4000;
        $targetDir = HWPREFIX. $sn;
        $IsOk =$obj-> CreateDirectory(UPLOAD_TEMP_DIR);
        if( $IsOk == -1 )$msg="存在但不是目录，新增目录失败！ Err{$IsOk}". $obj->JS_CntDn( $_SESSION['currURL'] , $wt);
        elseif( $IsOk == -2 ) $msg="新增目录失败！ Err{$IsOk}". $obj->JS_CntDn( $_SESSION['currURL'] , $wt);
        elseif ( !system('command -v zip')) {
           $msg= "系统无zip命令，请安装zip套件 Err-3。<br/>安装范例: yum install zip";
        }
        else{
//          $tmpPath =  UPLOAD_TEMP_DIR. "$targetDir.zip";
          $currPath= UPLOAD_DIR. $targetDir;
          $cmd = "cd ". UPLOAD_TEMP_DIR. "; rm -f {$targetDir}.zip; cd ". $currPath."; zip -rq ".UPLOAD_TEMP_DIR."{$targetDir}.zip *";
          system( $cmd );
          sleep(1);
          //$msg="<h3>档名{$targetDir}.zip，请 [<a href='". SITE_URL. UPDIR . TEMP_PATH . $targetDir .".zip'>点选此处下载</a>] </h3>档案资讯：<div style='text-align:left;'>";
          $msg="<h3>档名{$targetDir}.zip，请 [<a href='". SITE_URL. 'manage.php?f=DownloadHwZip&sn=' . $sn ."'>点选此处下载</a>] </h3>档案资讯：<div style='text-align:left;'>";
          $arr= $obj-> GetUploadHwList($sn);
          foreach($arr as $it){
             $msg .= "{$it['fileName']} ,{$it['size']}bytes ,学号{$it['cid']} ,拥有人{$it['cname']} ,原档名{$it['oFileName']} ,备注{$it['remark']}<br/>";
          } 
          $msg .="</div>";
        }
        $view->assign('msg', $msg);
        $view->display('Message.mtpl');
        break;
      default:
        $_SESSION['currURL']= $_SERVER['REQUEST_URI'];
        $view->display('HwManage.mtpl');
        break;
    }
    break;
  case "MngUpHw": //AJAX
    $opr= isset($_POST['opr'])?$_POST['opr']:"";
    $snc = isset($_POST['sn'])?$_POST['sn']:"";
    if( empty($opr) || empty($snc)){ print "参数错误Err-10"; break; }
    $row = $obj->Decrypt_c2Arr( $snc );
    if($row['sn'] <= 0){ print "参数错误Err-11"; break; }
    $sn =$row['sn'];
    switch($opr){
      case "ex":  //exhibition
        $IsOk = $obj->ProcChangeUpHwAttr($sn, "display");
        if( $IsOk <=0 )$msg="伺服器状态异常，写入终止！ Err{$IsOk}";
        else $msg="1";
        print $msg;
        break;
      case "p": //passed
        $IsOk = $obj->ProcChangeUpHwAttr($sn, "passed");
        if( $IsOk <=0 )$msg="伺服器状态异常，写入终止！ Err{$IsOk}";
        else{
          $obj->UpdatePassedCnt( $sn, $row['p'] );
          $msg="2";
        }
        print $msg;
        break;
      default:
        print "参数错误Err-12";
        break;
    }
    break;
  case "SaveUpHwScore":
    $wt=5000;
    $z= $_POST['z'];
    $h= $_POST['h'];
    $IsOk = $obj->UpdateUpHwScore($z,$h);
    if( $IsOk == -1 )$msg="没有资料，动作无效！ Err{$IsOk}";
    if( $IsOk <=0 )$msg="伺服器状态异常，写入终止！ Err{$IsOk}";
    else {$msg="储存作业成绩成功"; $wt=2000;}
    $msg .= $obj->JS_CntDn( $_SESSION['currURL'] , $wt);
    $view->assign('msg', $msg);
    $view->display('Message.mtpl');
    break;
  case "SaveOneHwScore":
    $IsOk = $obj->UpdateOneHwScore($_POST);
    if( $IsOk == -1 )$msg="没有资料，动作无效！ Err{$IsOk}";
    if( $IsOk <=0 )$msg="伺服器状态异常，写入终止！ Err{$IsOk}";
    else {$msg="储存作业成绩成功"; $wt=2000;}
    print $msg;
    break;
  case "DownloadHwZip":
    $sn = (int)$_GET['sn'];
    $title = $obj->GetOneHw( $sn );
    if (isset($title['hwTitle'])) {
        $title = $title['hwTitle'];
    } else {
      $msg="作业项目不存在，无法执行发送操作！";
      $msg .= $obj->JS_CntDn( "{$_SESSION['currURL']}" , 5000);
      $view->assign('msg', $msg);
      $view->display('Message.mtpl');
      break;
    }
    if ($obj->SendFile2Browser($sn, $title) != 1) {
      $msg="档案文件不存在，无法执行发送操作！";
      $msg .= $obj->JS_CntDn( "{$_SESSION['currURL']}" , 5000);
      $view->assign('msg', $msg);
      $view->display('Message.mtpl');
      break;
    }
    break;
  default:
    $msg = "". $obj->JS_CntDn( SITE_URL, 0);
    $view->assign('msg', $msg);
    $view->display('Message.mtpl');
    break;
}

?>



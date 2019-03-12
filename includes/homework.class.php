<?php
/*

作业上传系统独立精简版 v1
原始版本作者：CopyRight(C) 程式设计 Coding axer@tc.edu.tw 20120216-0314
修改版本作者：CopyRight(C) 老周部落
版权宣告：本程式遵从GNUv3规范 http://www.gnu.org/licenses/gpl.html

Homework_class 类别程式

*/

class Homework_class extends Bila_base_class {

  function __construct() {
    parent::__construct(); // 父类别初始化
  }

  public function CreateHwMngListPgStr()
  {
    $arr=$this->GetCurrParam();
    $c= $this->Encrypt_Arr2c($arr);
    $pg=$arr['pg'];
    return $this->CreateEncodePgLnkHtmlByc( SITE_URL. "/manage/Homework", $c, PAGE_ENCRYPT_KEY, $pg);
  }
  public function GetHwTitle($hID)
  {//新增获取作业标题，标准化命名需求。
    $sql = "select hwTitle from `hwList` where `hID`={$hID} limit 1";
    $row= $this->DB->GetRow( $sql );
    if( !$row)return -1; 
    return $row['hwTitle'];
  }
  public function CheckHwPasswd($sn, $cipher)
  {
    $sql = "select * from `hwUpload` where `sn`={$sn} and md5(`modPasswd`)='{$cipher}' limit 1";
    $row= $this->DB->GetRow( $sql );
    if( !$row)return -1; 
    return 1;
  }
  public function CheckCanUpload($hID, $upPasswd=""){
    if(empty($hID) || $hID<0)return -1;
    $row= $this->GetOneHw( $hID );
    if( gettype($row)=== "integer" )return -2; //encounter Errs, no this hw;
    if( $row['canUpload']== 0) return -3;  //Not Upload duration
    if( $row['upPasswd'] ==1 && $row['passwd'] !== $upPasswd )return -4; //Passwd wrong
    return 1;
  }
  public function GetOneHw($hID){
    if( empty($hID))return -1;
    $sql = "select * from `hwList` where `hID`={$hID} limit 1";
    $row= $this->DB->GetRow( $sql );
    $currD =date("Y-m-d H:i:s");//时间精确到秒，精准限制提交时间需求
    if( $currD <= $row['dueDT'] &&  $currD >= $row['fromDT'] && $row['closed']==0)$row['canUpload']= 1;
    else $row['canUpload']= 0;
    return $row;
  }
  public function GetOneUploadHw($sn){
    if( empty($sn))return -1;
    $sql = "select * from `hwUpload` where `sn`={$sn} limit 1";
    $row= $this->DB->GetRow( $sql );
    return $row;
  }
  public function ProcAddHwUpload( $arr ){
    if( empty($arr) || !is_array($arr)) return -1;
    $IsOk = $this->DB->AutoExecute( "hwUpload", $arr, 'INSERT');
    if( !$IsOk) return -2;
    $one = $this->DB->GetOne("SELECT LAST_INSERT_ID()");
    $this->LogUser( "stp=add,sn={$one},hID={$arr['hID']}");
    $sql= "update hwList set `uploadCnt`=`uploadCnt`+1, lastModDT=now() where hID={$arr['hID']} limit 1";
    $IsOk= $this->DB->Execute( $sql );
    return $one;
  }

  public function ProcDelOneUploadHw($sn){
    if( $sn <=0 )return -1;
    $row = $this->GetOneUploadHw($sn);
    if( !$this->LogUser( "sn={$sn},stp=del,hID={$row['hID']}")) return -2;
    if(! $row)return -3;
    $sql= "delete from `hwUpload` where `sn`={$sn} limit 1";
    $IsOk= $this->DB->Execute( $sql );
    if( !$IsOk) return -4;
    $sql= "update hwList set `uploadCnt`=`uploadCnt`-1, lastModDT=now() where hID={$row['hID']} limit 1";
    $IsOk= $this->DB->Execute( $sql );
    @unlink(UPLOAD_DIR. $row['fileName'] );
    return 1;
  }
  public function ProcModMyHw($arr){
    if( empty($arr) || !is_array($arr)) return -1;
    $IsOk = $this->DB->AutoExecute( "hwUpload", $arr, 'UPDATE', "`sn`={$arr['sn']}");
    if( !$IsOk) return -2;
    $this->LogUser( "stp=modhw,sn={$arr['sn']}");
    return 1;
  }

  public function ProcUpFiles( $file, $imgDir, &$returnrr ){
    $currDir = UPLOAD_DIR .$imgDir;
    $fn = strtolower($file['name']);    // ex. thread_jh.gif
    $fs = $file['size']; // ex. 340 (in bytes)
    $tempfs = $file['tmp_name'];  // ex. /var/tmp/phpF390NL
    $err = $file['error'];  // error:0
    if ($err>0 || $fs <=0){ $msg= "传输错误，错误次". $err; return -1; }
    $IsOk =$this->ChkImgFileExt($fn, $this->NotAllowedFileExtArr, $ext);  //1: not allowed file
    if($IsOk > 0){ $msg= "传档类型 ". implode(',',$this->NotAllowedFileExtArr) . "不被允许"; return -2;}
    if( $this->CreateDirectory($currDir)<= 0){ $msg= "建立目录[$currDir]失败";return -3;}
    //$_date = date("YmdHis"). rand(0, 100) ;    //ex: 20080911123456
    //$newfn= "{$_date}.{$ext}";
    //命名格式: 作业标题-学号-姓名.扩展名, 作业标准化命名需求
    $newfn=$title . '-' . $cid . '-' . $cname . ".{$ext}";
    $newpath =  $currDir. $newfn;
    @copy( $tempfs,  $newpath);
    @unlink($tempfs);
    $returnrr= array("fileName"=> $imgDir. $newfn, "oFileName"=> $fn,  "size"=>$fs, "ext"=>$ext);
    return 1; //Succeed
  }
  public function ProcDelHw($sn){
    if( $sn <=0 )return -1;
    if( !$this->LogManage( "hID={$sn},stp=del")) return -2;
    $sql= "delete from `hwList` where `hID`={$sn} limit 1";
    $IsOk= $this->DB->Execute( $sql );
    if( !$IsOk) return -3;
    return 1;
  }
  public function ProcModHw( $arr ){
    if( empty($arr) || !is_array($arr)) return -1;
    $IsOk = $this->DB->AutoExecute( "hwList", $arr, 'UPDATE', "`hID`={$arr['hID']} and `email`='{$_SESSION['email']}'");
    if( !$IsOk) return -2;
    $this->LogManage( "stp=modhID={$arr['hID']}");
    return 1;
  }

  public function SendFile2Browser($sn=0){
    if(!$sn) return -1;
    $row= $this-> GetOneUploadHw($sn);
    if($row===-1 || !$row) return -2;
    switch ( $row['ext']) {
      case "avi": $ctype="video/avi";break;
      case "doc": $ctype="application/msword"; break;
      case "exe": $ctype="application/octet-stream"; break;
//      case "gif": $ctype="image/gif"; break;
      case "jpeg":
      case "jpg": $ctype="image/jpg"; break;
      case "odp": $ctype="application/vnd.oasis.opendocument.presentation";break;
      case "odt": $ctype="application/vnd.oasis.opendocument.text";break;
      case "ods": $ctype="application/vnd.oasis.opendocument.spreadsheet";break;
      case "pdf": $ctype="application/pdf"; break;
      case "zip": $ctype="application/zip"; break;
      case "xls": $ctype="application/vnd.ms-excel"; break;
      case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
      case "png": $ctype="image/png"; break;
      case "mp3": $ctype="audio/mpeg3";break;
      case "wav": $ctype="audio/wav";break;
      case "mpeg":
      case "mpg": $ctype="video/mpeg";break;
      default: $ctype="application/force-download";
    }

   $fullFileName= UPLOAD_DIR . $row['fileName'];


$ctype= mime_content_type( $fullFileName);
    $fileNameURL=  UPURL. $row['fileName'];
    if (!file_exists( $fullFileName )) return -3;
    $encodedFileName = urlencode($row['oFileName']);
    $encodedFileName = str_replace("+", "%20", $encodedFileName);
    $ua = isset($_SERVER["HTTP_USER_AGENT"])? $_SERVER["HTTP_USER_AGENT"]:"";
    if (preg_match("/MSIE/i", $ua))
      $header = "attachment; filename=\"{$encodedFileName}\";";
    elseif (preg_match("/Firefox/", $ua)) 
      $header = "attachment; filename*=\"utf8''{$row['oFileName']}\";";
    else 
      $header = "attachment; filename=\"{$row['oFileName']}\";";
//print $header;
//exit;
//    header("Content-type: application/force-download");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // cache stop
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // cache stop
header("Cache-Control: must-revalidate"); // cache stop

 
    header("Pragma: public");
    header("Expires: 0");
//    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//    header("Cache-Control: private",false);
header("Cache-Control: private");
    header("Content-Disposition: {$header}");
    header("Content-Type: {$ctype}");
//header("Content-Type: application/octet-stream");
//header("Content-Type: application/download");

   header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".@filesize($fullFileName));
    set_time_limit(0);
// download
 readfile($fullFileName);
exit;
/*
$file = @fopen($fullFileName,"rb");
if ($file) {
  while(!feof($file)) {
    print(fread($file, 1024*8));
    flush();
    if (connection_status()!=0) {
      @fclose($file);
      return -4;
    }
  }
  @fclose($file);
}*/

// log downloads
    return 1;
  }
}

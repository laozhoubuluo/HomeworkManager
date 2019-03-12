<?php
/*

作业上传系统独立精简版 v1
原始版本作者：CopyRight(C) 程式设计 Coding axer@tc.edu.tw 20120216-0314
修改版本作者：CopyRight(C) 老周部落
版权宣告：本程式遵从GNUv3规范 http://www.gnu.org/licenses/gpl.html

Manage_class 类别程式

*/

class Manage_class extends Bila_base_class {
  private $InnObj;

  function __construct() {
    parent::__construct(); // 父类别初始化
  }

  public function CreateHwMngListPgStr()
  {
    $arr=$this->GetCurrParam();
    $c= $this->Encrypt_Arr2c($arr);
    $pg=$arr['pg'];
    return $this->CreateEncodePgLnkHtmlByc( SITE_URL. "manage.php?f=Homework", $c, PAGE_ENCRYPT_KEY, $pg);
  }

  public function GetOneHw($hID){
    if( empty($hID))return -1;
    $sql = "select * from `hwList` where `hID`={$hID} limit 1";
    $row= $this->DB->GetRow( $sql );
    return $row;
  }

  public function GetHwList(){
    $tbl = $this-> GetCurrParam();
    $where="where `email`='{$_SESSION['email']}' ";
    $odr= empty($tbl['odr'])?-7:$tbl['odr'];
    switch( abs($odr)){
      case 1:
        $orderby = " order by `hID` ";
        break;
      case 2:
        $orderby = " order by `hwO` ";
        break;
      case 3:
        $orderby = " order by `hwTitle` ";
        break;
      case 4:
        $orderby = " order by `uploadCnt` ";
        break;
      case 5:
        $orderby = " order by `passedCnt` ";
        break;
      case 6:
        $orderby = " order by `clicked` ";
        break;
      case 7:
        $orderby = " order by `cDT` ";
        break;
      case 8:
        $orderby = " order by `lastModDT` ";
        break;
      default:
        $orderby = ""; //预设不排序
        break;
    }
    if($odr<0)$orderby .= "desc ";
    if( abs($odr)==2)$orderby .= ", `rank` desc "; //double rank
//    if( isset($tbl['_kw']) && $tbl['_kw']== 1 && !empty($tbl['kw']))
    if( !empty($tbl['kw']))
      $where .= "and (`hwTitle` like '%{$tbl['kw']}%' or `remark` like '%{$tbl['kw']}%') ";
//    if( isset($tbl['_date']) && $tbl['_date'] == 1){
    if( $tbl['from'] |$tbl['to']){
      if( $tbl['from'] =="")$where .="and `cDT` <= DATE_ADD('{$tbl['to']}', INTERVAL 1 DAY) ";
      elseif( $tbl['to'] == "")$where .="and `cDT` >= '{$tbl['from']}' ";
      else $where .="and (`cDT` between '{$tbl['from']}' and DATE_ADD('{$tbl['to']}', INTERVAL 1 DAY)) ";
    }
    $index= ( $tbl['pg']-1) * $this->RowsPerPage;
    if($index<0)$index=0;
    $sql ="select SQL_CALC_FOUND_ROWS * from `hwList` ";
    $sql .= "{$where} {$orderby} LIMIT {$index} ,". ROWS_PER_PAGE;
    //if( $this->GlobalRole ) $sql .= "{$where} {$orderby} LIMIT {$index} ,{$this->RowsPerPage}";
    //else $sql .= "where `innbasic`.`o`='{$this->GetSession('email')}' {$where} {$orderby} LIMIT {$index} ,{$this->RowsPerPage}";
    $arr= $this->DB->GetAll( $sql );
    $this->rowsCount = $this->DB->GetOne("select FOUND_ROWS()");
    $currD =date("Y-m-d");
    for($ii=0; $ii<sizeof($arr); $ii++){
      $sn=$arr[$ii]['hID'];
      $tbl['sn']=$sn;
      $tbl['stp']=2;  //modi
      $arr[$ii]['mc'] = $this->Encrypt_Arr2c($tbl);
      $tbl['stp']=4;  //close
      $arr[$ii]['cc'] = $this->Encrypt_Arr2c($tbl);
      $tbl['stp']=5;  //display
      $arr[$ii]['dspc'] = $this->Encrypt_Arr2c($tbl);
      $tbl['stp']=6;  //delete
      $arr[$ii]['delc'] = $this->Encrypt_Arr2c($tbl);
      $tbl['stp']=7;  //hw mng c
      $arr[$ii]['hwc'] = $this->Encrypt_Arr2c($tbl);
      $tbl['stp']=9;  //download c
      $arr[$ii]['dlc'] = $this->Encrypt_Arr2c($tbl);
      if( $currD <= $arr[$ii]['dueDT'] &&  $currD >= $arr[$ii]['fromDT'] && $arr[$ii]['closed']==0)$arr[$ii]['canUpload']= 1;
      else $arr[$ii]['canUpload']= 0;

    }
    return $arr;
  }
  public function GetMemberList(){
    $sql = "SELECT `email` FROM `member` order by `email`";
    $col= $this->DB->GetCol( $sql );  //GetRow只会取一行 ，要用GetCol
    return $col;
  }
  //此函数设定表单的状态
  public function GetTableArr(){
    $arr = $this->GetCurrParam();
    //produce catID option
    $arr['currentDateTime'] = date("Y-m-d H:i:s");
    return $arr;
  }
  public function GetUploadHwList( $hID){
    $sql ="select SQL_CALC_FOUND_ROWS * from `hwUpload` where `hID`={$hID} order by `cname`, `cDT` desc";
    $arr =$this->DB->GetAll( $sql );
    $this->rowsCount = $this->DB->GetOne("select FOUND_ROWS()");
    $tbl=array();
    for($ii=0; $ii<sizeof($arr); $ii++){
      $sn=$arr[$ii]['sn'];
      $arr[$ii]['ext'] = strtoupper($arr[$ii]['ext'] );
      $tbl['sn']=$sn;
      $tbl['stp']=4;  //passed
      $tbl['p']=$arr[$ii]['passed'];
      $arr[$ii]['pc'] = $this->Encrypt_Arr2c($tbl);
      $tbl['stp']=6;  //exhibition
      $arr[$ii]['exc'] = $this->Encrypt_Arr2c($tbl);
      $arr[$ii]['vc'] = $this->LongEncode($sn);
    }
    return $arr;
  }
  public function ProcAddHw( $arr ){
    if( empty($arr) || !is_array($arr)) return -1;
    $IsOk = $this->DB->AutoExecute( "hwList", $arr, 'INSERT');
    if( !$IsOk) return -2;
    $one = $this->DB->GetOne("SELECT LAST_INSERT_ID()");
    $this->LogManage( "stp=add,hID={$one}");
    return $one;
  } 
  public function ProcAdmAddMember( $arr){
    if( empty($arr) || !is_array($arr)) return -1;
    if( empty($arr['email']) )return -3;
    $sql ="select `email` from `member` where `shadow`='{$arr['shadow']}'";
    $one =$this->DB->GetOne( $sql );
    if(!empty($one))return -4;
    $IsOk = $this->DB->AutoExecute( "member", $arr, 'INSERT');
    if( ! $IsOk){
      if( !$this->LogManage( "[Failed]stp=addmbr,e={$arr['email']}")) return -2;
      return -5;
    }
    $this->LogManage( "stp=Addmbr,e={$arr['email']}");
    return 1;
  }
  public function ProcChangeHwAttr($sn, $attrName, $attrValue=""){
    if( empty($sn) || empty($attrName)) return -1;
    if( $sn <=0 )return -2;
    if( !$this->LogManage( "hID={$sn},stp=chattr,attrN={$attrName},v={$attrValue}")) return -3;
    if(empty($attrValue)) $sql= "update `hwList` set `{$attrName}`=not `{$attrName}` where `hID`={$sn} limit 1";
    else  $sql= "update `hwList` set `{$attrName}`='{$attrValue}' where `hID`={$sn} limit 1";
    $IsOk= $this->DB->Execute( $sql );
    if( !$IsOk) return -4;
    return 1;
  }
  public function ProcChangeUpHwAttr($sn, $attrName, $attrValue=""){
    if( empty($sn) || empty($attrName)) return -1;
    if( $sn <=0 )return -2;
    if( !$this->LogManage( "sn={$sn},stp=chattr,attrN={$attrName},v={$attrValue}")) return -3;
    if(empty($attrValue)) $sql= "update `hwUpload` set `{$attrName}`=not `{$attrName}` where `sn`={$sn} limit 1";
    else  $sql= "update `hwUpload` set `{$attrName}`='{$attrValue}' where `sn`={$sn} limit 1";
    $IsOk= $this->DB->Execute( $sql );
    if( !$IsOk) return -4;
    return 1;
  }
  public function ProcDelHw($sn){
    if( $sn <=0 )return -1;
    if( !$this->LogManage( "hID={$sn},stp=del")) return -2;
    $this->delTree( UPLOAD_DIR. HWPREFIX. $sn); 
    $sql= "delete from `hwList` where `hID`={$sn} limit 1";
    $IsOk= $this->DB->Execute( $sql );
    if( !$IsOk) return -3;
    return 1;
  }
  public function ProcDelMember( $e){
    if( empty($e))return -1;
    if( !$this->LogManage( "stp=delmbr,e={$e}")) return -2;
    $shadow= md5($e);
    $sql= "delete from `member` where `shadow`='{$shadow}' limit 1";
    $IsOk = $this->DB->Execute( $sql );
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
  public function UpdatePassedCnt($sn, $opassed=0){
    if( $sn <=0 )return -1;
    $diff = 2* (( $opassed+1)%2 )-1;  //0->+1 1->-1
    $sql = "update `hwList` set `passedCnt`=`passedCnt`+ ($diff) where `hID` in (select `hID` from `hwUpload` where `sn`={$sn})";
    $IsOk= $this->DB->Execute( $sql );
    return 1;
  }
  public function UpdateOneHwScore($post){
    $sn= $this->LongDecode($post['snc']);
    if( $sn <=0)return -1;
    $sql = "update `hwUpload` set `score`='{$post['v']}' where `sn`={$sn} and `hID`={$post['hID']} limit 1";
    $IsOk= $this->DB->Execute( $sql );
    if( !$IsOk) return -4;
    return 1; 
  }
  public function UpdateUpHwScore($z, $hID){
    if( empty($z)) return -1;
    if( empty($hID)) return -2;
    $zrr= explode(";", $z); 
    $sns="";
    foreach($zrr as $it){
      $row = explode(",", $it);
      if(sizeof($row)!=2) return -3;
      $sn= $this->LongDecode($row[0]);  
      $score = $row[1];
      $sql = "select `score` from `hwUpload` where `sn`={$sn}";
      $oscore = $this->DB->GetOne( $sql );
      if( $oscore !==  $score){
        $sql = "update `hwUpload` set `score`='{$score}' where `sn`={$sn} limit 1";
        $IsOk= $this->DB->Execute( $sql );
        if( !$IsOk) return -4;
        $sns .= $sn . " ";
      }
    }
    $this->LogManage( "stp=svscore,sn={$sns}");

    return 1;   
  }
}

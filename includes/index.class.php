<?php
/*

作业上传系统独立精简版 v1
原始版本作者：CopyRight(C) 程式设计 Coding axer@tc.edu.tw 20120216-0314
修改版本作者：CopyRight(C) 老周部落
版权宣告：本程式遵从GNUv3规范 http://www.gnu.org/licenses/gpl.html

Index_class 类别程式

*/

class Index_class extends Bila_base_class {
  private $PromotePgRowsPg=20;
  private $CurrWeeksn;
  private $CurrTimeStamp;
  private $currPage;
  public $TempInnNumer="";  //供样版临时取样，计算个数
  private $WeekName;

  public function __construct() {
    parent::__construct(); // 父类别初始化
    $this->CurrTimeStamp=time();  //1282759384
    $this->CurrWeeksn= (int)Date('W');
    $this->WeekName = array('日', '一', '二', '三', '四', '五', '六');
  }

  public function CreateHwListPgStr()
  {
    $arr=$this->GetCurrParam();
    $c= $this->Encrypt_Arr2c($arr);
    $pg=$arr['pg'];
    return $this->CreateEncodePgLnkHtmlByc( SITE_URL ."?f=List", $c, PAGE_ENCRYPT_KEY, $pg);
  }
  public function GetDisplayUpHws(){
    $sql ="select SQL_CALC_FOUND_ROWS u.*, h.hwTitle from `hwUpload` as u "; 
    $sql .= "left join `hwList` as h using(hID) where u.`display`=1 order by u.`cDT` desc limit 0,". ROWS_EXHIBITION;
    $arr =$this->DB->GetAll( $sql );
    $this->rowsCount = $this->DB->GetOne("select FOUND_ROWS()");
    $tbl=array();
    for($ii=0; $ii<sizeof($arr); $ii++){
      $sn=$arr[$ii]['sn'];
      $arr[$ii]['ext'] = strtoupper($arr[$ii]['ext'] );
      $tbl['sn']=$sn;
      $arr[$ii]['vc'] = $this->LongEncode($sn);
    }
    return $arr;
  }
  public function GetHwGroupDetails($classID){
    $tbl = $this-> GetCurrParam();
    $where="where `closed`=0 ";
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
        $orderby = " order by `classID` ";
        break;
      case 9:
        $orderby = " order by `lastModDT` ";
        break;
      default:
        $orderby = ""; //预设不排序
        break;
    }
    if($odr<0)$orderby .= "desc ";
    $sql ="select * from `hwList` ";
    $sql .= "where `closed`=0 and `classID`='{$classID}' {$orderby}";
    $arr= $this->DB->GetAll( $sql );
    for($ii=0; $ii<sizeof($arr); $ii++){
      $currD =date("Y-m-d");
      if( $currD <= $arr[$ii]['dueDT'] &&  $currD >= $arr[$ii]['fromDT'] && $arr[$ii]['closed']==0)$arr[$ii]['canUpload']= 1;
      else $arr[$ii]['canUpload']= 0;
      $sn=$arr[$ii]['hID'];
      $tbl['sn']=$sn;
      $tbl['stp']=3;  //upload
      $arr[$ii]['mc'] = $this->Encrypt_Arr2c($tbl);
      $tbl['stp']=8;  //view
      $arr[$ii]['vc'] = $this->LongEncode($sn);
    }
    return $arr;
  }
  
  public function GetHwGroupList(){
    $tbl = $this-> GetCurrParam();
    $where="where `closed`=0 ";
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
        $orderby = " order by `classID` ";
        break;
      case 9:
        $orderby = " order by `lastModDT` ";
        break;
      default:
        $orderby = ""; //预设不排序
        break;
    }
    if($odr<0)$orderby .= "desc ";
    if( !empty($tbl['hkw']) && $tbl['hkw'] !==" ")
      $where .= "and (`hwTitle` like '%{$tbl['hkw']}%' or `remark` like '%{$tbl['hkw']}%' or `hwO`='{$tbl['hkw']}%') ";
        if( isset( $tbl['_display']) && $tbl['_display']==1) $where .= "and `display`=1 ";
    if( isset( $tbl['_thissem']) && $tbl['_thissem']==1){
    }
    $index= ( $tbl['pg']-1) * ROWS_PER_PAGE;
    if($index<0)$index=0;
    $sql ="select SQL_CALC_FOUND_ROWS `classID`, count(`hID`) as cnt, sum(`uploadCnt`) as sumup, sum(`passedCnt`) as sumpass, sum(`clicked`) as sumcli, left(md5(`classID`),8) as hash from `hwList` ";
    $sql .= "{$where} group by `classID` {$orderby} LIMIT {$index} ,". ROWS_PER_PAGE;
    //if( $this->GlobalRole ) $sql .= "{$where} {$orderby} LIMIT {$index} ,{$this->RowsPerPage}";
    //else $sql .= "where `innbasic`.`o`='{$this->GetSession('email')}' {$where} {$orderby} LIMIT {$index} ,{$this->RowsPerPage}";
    $arr= $this->DB->GetAll( $sql );
    $this->rowsCount = $this->DB->GetOne("select FOUND_ROWS()");
    return $arr;
  }

  
  public function GetHwList(){
    $tbl = $this-> GetCurrParam();
    $where="where `closed`=0 ";
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
      default:
        $orderby = ""; //预设不排序
        break;
    }
    if($odr<0)$orderby .= "desc ";
    if( !empty($tbl['hkw']) && $tbl['hkw'] !==" ")
      $where .= "and (`hwTitle` like '%{$tbl['hkw']}%' or `remark` like '%{$tbl['hkw']}%' or `hwO`='{$tbl['hkw']}%') ";
	if( isset( $tbl['_display']) && $tbl['_display']==1) $where .= "and `display`=1 ";
    if( isset( $tbl['_thissem']) && $tbl['_thissem']==1){
    }
    $index= ( $tbl['pg']-1) * ROWS_PER_PAGE;
    if($index<0)$index=0;
    $sql ="select SQL_CALC_FOUND_ROWS * from `hwList` ";
    $sql .= "{$where} {$orderby} LIMIT {$index} ,". ROWS_PER_PAGE;
    //if( $this->GlobalRole ) $sql .= "{$where} {$orderby} LIMIT {$index} ,{$this->RowsPerPage}";
    //else $sql .= "where `innbasic`.`o`='{$this->GetSession('email')}' {$where} {$orderby} LIMIT {$index} ,{$this->RowsPerPage}";
    $arr= $this->DB->GetAll( $sql );
    $this->rowsCount = $this->DB->GetOne("select FOUND_ROWS()");
    for($ii=0; $ii<sizeof($arr); $ii++){
      $sn=$arr[$ii]['hID'];
      $tbl['sn']=$sn;
      $tbl['stp']=3;  //upload
      $arr[$ii]['mc'] = $this->Encrypt_Arr2c($tbl);
      $tbl['stp']=8;  //upload
      $arr[$ii]['vc'] = $this->LongEncode($sn);
    }
    return $arr;
  }

  public function GetOneHw($sn){
    if( $sn<=0)return false;
    $sql = "select * from `hwList` where `hID`={$sn} limit 1";
    $row =$this->DB->GetRow( $sql );
    $currD =date("Y-m-d H:i:s");//时间精确到秒，精准限制提交时间需求
    if( $currD <= $row['dueDT'] &&  $currD >= $row['fromDT'] && $row['closed']==0)$row['canUpload']= 1;
    else $row['canUpload']= 0;
    $row['upc']=  $this->Encrypt_Arr2c( array('sn'=>$row['hID'], 'stp'=>2, 'pg'=>1));
    return $row;
  }

  //此函数设定表单的状态
  public function GetTableArr(){
    $arr = $this->GetCurrParam();
    //produce catID option
    $arr['currentDateTime'] = date("Y-m-d H:i:s");
    return $arr;
  }

  public function GetUploadHwList( $hID){
    $sql ="select SQL_CALC_FOUND_ROWS * from `hwUpload` where `hID`={$hID} order by `cid`, `cDT` desc";//作业列表按新增学号字段排序
    $arr =$this->DB->GetAll( $sql );
    $this->rowsCount = $this->DB->GetOne("select FOUND_ROWS()");
    $tbl=array();
    for($ii=0; $ii<sizeof($arr); $ii++){
      $sn=$arr[$ii]['sn'];
      $arr[$ii]['ext'] = strtoupper($arr[$ii]['ext'] );
      $tbl['sn']=$sn;
      $tbl['stp']=3;  //mod
      $arr[$ii]['mc'] = $this->Encrypt_Arr2c($tbl);
      $tbl['stp']=6;  //delete
      $arr[$ii]['dc'] = $this->Encrypt_Arr2c($tbl);
      $arr[$ii]['vc'] = $this->LongEncode($sn);
    }
    return $arr;
  }
  public function Log2Counter($pgID=0){
    $sql ="select `clicked` from `counter` where `date`=curdate()";
    $one =$this->DB->GetOne( $sql );
    if(empty($one))$sql="insert into `counter`(`pgID`,`date`) values ({$pgID},curdate())";
    else $sql="update `counter` set `pgID`={$pgID}, `clicked`=`clicked`+1 where `date`=curdate()";
    $this->DB->Execute( $sql);
  }

  public function RecHwPgCnt($hID){
    $sql ="update `hwList` set `clicked`=`clicked`+1 where `hID`={$hID} limit 1";
    $IsOk= $this->DB->Execute( $sql );    
    return;
  }
}


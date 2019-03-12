<?php
/*

作业上传系统独立精简版 v1
原始版本作者：CopyRight(C) 程式设计 Coding axer@tc.edu.tw 20120216-0314
修改版本作者：CopyRight(C) 老周部落
版权宣告：本程式遵从GNUv3规范 http://www.gnu.org/licenses/gpl.html

Auth_class 认证类别程式

*/


class Auth_class extends Bila_base_class {

  public function __construct() {
    parent::__construct(); // 父类别初始化
  }

  //检查资料库是否已有注册过此 Email，Email不得重覆
  public function CheckIsRegistered( $email=""){
    $sql = "select `email` from `member` where `email`='{$email}'";
    $sn= $this->DB->GetOne( $sql );
    if($sn) return true;    //重覆性检查失败，目标已重覆
    return false;
  }

  public function GenAccessCode( $raw ){
    $date=date("yMd");    //09Sep07
	return substr( sha1($raw. $date),1,20);
  }

  public function GetBasProfile(){
    return $this->DB->GetRow("select * from `member` where `sn`={$this->GetSession('sn')}");
  }

  public function GetMemberByRoleID( $roleID ){
    $sql = "SELECT `email` FROM `member` WHERE `role` REGEXP '[[:<:]]{$roleID}[[:>:]]' limit 0,21";
    $arr= $this->DB->GetCol( $sql );    //GetRow只会取一行 ，要用GetCol
    $n =sizeof($arr);
    $res="";
    for( $ii=0; $ii< $n; $ii++){
      $res .= $arr[$ii]. ", ";
    }
	if( $n>20 )return $res. " 仅列出20笔，其馀请用查询";
	return $res;
  }

  public function GetMemberRoleByEmail( $email){
    $email = addslashes($email);
    if( ! $email) return -1;
    $sql ="select `role` from `member` where `email`='{$email}'";
    $one =$this->DB->GetOne( $sql );
    if( !$one) return -2;
    $arr = explode(",", str_replace("\"","", $one));
    $res="";
    for( $ii=0; $ii< sizeof($arr); $ii++){
      $roleID = (int)$arr[$ii];
      $sql ="select `rolename` from `role` where `roleID`={$roleID}";
      $one =$this->DB->GetOne( $sql );
      if( $one) $res .= $one. " ";
    }
    return $res;
  }

  public function GetRoleParams(){
    $roleID = $this-> GetCurrParam();
    $sql ="select *  from `role` where `roleID`={$roleID}";
    $arr =$this->DB->GetRow( $sql );
    return $arr;
  }

  public function GetRolePoolList(){
    $arr= $this->DB->GetAll("select `roleID`, `rolename`  from `role` order by `roleID`");
    $n =sizeof($arr);
    $res="";
    for( $ii=0; $ii< $n; $ii++){
      if( $this->CheckPriv('ModRoleProc') == 0 && $arr[$ii]['roleID']==6)continue;
      $res .= "<div style='width:133px; float:left; margin-right: 8px;' id='roleID{$arr[$ii]['roleID']}'><a href='/auth/RoleMng&r=" . $arr[$ii]['roleID'] . "'>".  $arr[$ii]['rolename'] ."</a></div>";
	  if( $ii %2 ==1)$res .="<br>";
    }
    return $res;
  }

  public function GetPrivPoolList(){
    $roleID = $this-> GetCurrParam();
    $sql ="select `priv` from `role` where `roleID`={$roleID}";
    $one =$this->DB->GetOne( $sql );
    if( $one===false )return -1;    //角色ID错误
    $privarr = explode(",", $one);
    $arr= $this->DB->GetAll("select * from `priv` order by `privID`");
    $n =sizeof($arr);
    $res="";
    for( $ii=0; $ii< $n; $ii++){
      $pv= trim($arr[$ii]['privname']);
      if( in_array( $pv, $privarr))$chk="checked";
      else $chk="";
      $res .= "<div class='rolemngdiv'><input $chk type=checkbox name='{$pv}' id='{$pv}' value='{$pv}'><sup>{$arr[$ii]['privID']}</sup>{$arr[$ii]['privcname']}</div>";
    }
    return $res;
  }

  public function GiveMemberRole( $arr ){
    $email = trim(addslashes($arr['email']));
    $roleID = (int)$arr['roleID'];
    $op =$arr['op'];    // 1: 绝对角色 2:多重角色
    if( ! $email) return -1;
    $sql ="select `role` from `member` where `email`='{$email}'";
    $one =$this->DB->GetOne( $sql );
    if( $one === false) return -2;  //查询失败，无此使用者
    if($one && $op==2){
      $roarr = explode(",", $one);    //带有" 的角色id
      if( !in_array("{$roleID}", $roarr)) //无此角色+入
        $one .= ",{$roleID}";
    }
    else $one="{$roleID}";
    $sql ="update `member` set `role`='{$one}' where `email`='{$email}' limit 1";
    $res =$this->DB->Execute( $sql );
    if($res)return 1;
    else return -3;
  }

  /**
   *  Member_Auth() - 认证使用者帐密，新版本 99.03.28
   *  @param $option 
   *  @return 认证结果，已认证完毕会写入 session 值
   *  提高安全性的写法 991117 shadow email
   */
  public function Member_Auth($id="", $pw="", $option=""){
    $shadow=md5($id);
    if( !$id || !$pw)return -1;
    $sql = "select `usID`, `email`, `cname` from `member` where `shadow`='{$shadow}' and `passwd`='{$pw}'";
    $row= $this->DB->GetRow( $sql );
    if( sizeof ($row) <= 0 ){  //找不到会员资料or帐密错误
      if( !$this->LogLogin( "failed {$id}/{$pw}")) return -2;
      return -3;
    }
    //unset($_SESSION);
    $_SESSION['usID']=$row['usID'];
    $_SESSION['cname']=$row['cname'];
    $_SESSION['email']=$row['email'];
    $this->LogLogin("id={$row['email']},st=success");
    $this->SetSession($_SESSION);
    return 1;
    //Session 取出结束
  }

  public function Tc_Auth($arr){
    $_SESSION['usID']=$arr['email'];
    $_SESSION['cname']=$arr['fullname'];
    $_SESSION['email']=$arr['email'];
    $this->LogLogin("id={$arr['email']},auth=tc,st=success");
    $this->SetSession($_SESSION);
    return 1;
  }

    public function ProcAddNewRole( $arr ){
        if( empty($arr) || !is_array($arr)) return -1;
        if( !$this->LogManage( "{$arr['rolename']}")) return -2;
        $sql= "insert into role( `rolename`, `ModDT`) values ";
        $sql .= "( '{$arr['rolename']}', NOW())";
        if( $this->DB->Execute( $sql ))return 1;
        else return -3;
    }

    public function ProcDelRole( $roleID ){
        $roleID = (int)$roleID;
        if( $roleID <=0)return -1; //gsnERROR
        if( !$this->LogManage( "roleID={$roleID}" )) return -2;
        $sql= "delete from `role` where `roleID`={$roleID} limit 1";
        $IsOk= $this->DB->Execute( $sql );
        if( !$IsOk) return -3; //Del goods Error
        return 1;
    }

    public function ProcModAdvProfile( $arr){
        if( empty($arr) || !is_array($arr)) return -1;
        if( !$this->LogUser( "cname={$arr['cname']}")) return -2;
        $IsOk = $this->DB->AutoExecute( "member", $arr, 'UPDATE', "`sn`={$this->GetSession('sn')}");
        if( ! $IsOk) return -3;
        return 1;
    }

    public function ProcModBasicProfile( $arr){
        if( empty($arr) || !is_array($arr)) return -1;
        if( !$this->LogUser( "usID={$arr['usID']}")) return -2;
        $IsOk = $this->DB->AutoExecute( "member", $arr, 'UPDATE', "`sn`={$this->GetSession('sn')}");
        if( $IsOk )return 1;
        return -3;
    }

  public function ProcSaveRegisterStep2( $arr){
    if( empty($arr) || !is_array($arr)) return -1;
    if( !$this->LogNobody( "e={$arr['email']}")) return -2;
    $shadow= md5( $arr['email'] );
    $sql= "insert into member( `email`, `shadow`, `passwd`, `usID`, `regDT`, `role`, `exp`) values ";
    $sql .= "('{$arr['email']}', '{$shadow}', '{$arr['passwd']}', '{$arr['usID']}', NOW(), 2, 10)";
    $IsOK =$this->DB->Execute( $sql );
	if($IsOK )return 1;
	else return -3;
  }

}

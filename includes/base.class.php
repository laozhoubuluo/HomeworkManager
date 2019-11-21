<?php
/*

作业上传系统独立精简版 v1
原始版本作者：CopyRight(C) 程式设计 Coding axer@tc.edu.tw 20120216-0314
修改版本作者：CopyRight(C) 老周部落
版权宣告：本程式遵从GNUv3规范 http://www.gnu.org/licenses/gpl.html

Base_class 类别程式

*/

class Bila_base_class {
  var $Week = array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
  var $Session = null;	//全站Session
  var $Session_id= null;	// Session_id，供表格认证用
  var $DB;    		//资料库物件
  var $AllCatArr; 		//一次取得所有选单，只需要在一开始或更动时执行一次即可。请叫用 InitAllCatArr();
  var $CurrParam=array();  		//用作页面参数
  var $rowsCount=0;		//资料库中取出总行数
  var $RowsPerPage;  //每页行数
  var $f;					//操作功能
  var $GlobalRole = false;	//全域角色
  var $AllowedImgExtNameArr = array('jpg', 'png', 'gif', 'jpeg');
  var $NotAllowedFileExtArr = array('exe', 'scr', 'com');
  var $DEFAULT_CODE; 
  var $CODE_SHIFT;

  public function __construct() {
    $this->DEFAULT_CODE = "OlPkQjRiShTgUfAz6By5Cx8Dw7vE9Fu0Gt1sH2Ir3Jq4KLpoMnNmVeWdXcYbZa";
    $this->CODE_SHIFT=6;
    $this->RowsPerPage= defined('ROWS_PER_PAGE')?ROWS_PER_PAGE:30;
  }

  // 属性部分函式
  // ---------------------------------------------------
  // 设定Session至物件变数中，因样版会取出需用Session的值
  public function SetSession($s){ 
    $this->Session = $s;
    if( $this->CheckPriv("GlobalRole")) $this->GlobalRole=true;
  }
  public function GetThisSession(){ return $this->Session; }
  public function GetSession($item){ 
	if(empty( $this->Session[$item])){
      if( isset( $this->Session[$item]))return -2;  //有，但是空值
	  else return -1;
	}
	else return $this->Session[$item];
  }
  public function GetSessionArray(){return $this->Session; }

  public function SetCurrParam($s){ $this->CurrParam = $s; }
  public function GetCurrParam(){return $this->CurrParam; }

  //检查图档附档名是否合规定
  public function ChkImgFileExt($filename, $AllowedImgExtNameArr, &$ext)
  {
    $ext_fname = explode(".", $filename ); //取最后的副档名
    $ext_fname = end($ext_fname); //Only variables should be passed by reference
    $ext_fname = strtolower($ext_fname); //Only variables should be passed by reference
    $ext= $ext_fname;
    if (@in_array( $ext_fname ,$AllowedImgExtNameArr )) return 1;
    return -1;
  }

  public function CheckPriv( $priv ){
    if( $this->GetSession('priv')===-1)return 0;
	if ( in_array($priv, $this->GetSession('priv') ))return 1;
	return 0;
  }

  //    20100408161128  => 2010/04/08
  public function ConvertInsDate($str){
    if( strlen($str)==0)return "";
    return substr($str,0,4)."/".substr($str,4,2)."/".substr($str,6,2);
  }

  //    20100408161128  => 2010/04/08 16:11:28
  public function ConvertInsDateTime($str){
    if( strlen($str)==0)return "";
    return substr($str,0,4)."/".substr($str,4,2)."/".substr($str,6,2) . " ".substr($str,8,2) .":".substr($str,-4,2).":".substr($str,-2,2);
  }

  /**
   * 函数名称：string CreateRndClrStr($startv, $darkflag)
   * 功能：随机产生一个颜色字串
   * @param $startv: start value, an integer 0-255, 0:darkest(black) to 255:white
   * @param $darkflag: true:from $startv to darker value; false:from $startv to lighter value。(true向前演色、false向后
浅色)
   * @return a color string. 回传：字串(ex.#123456, #ABCDEF)
   * ex: CreateRndClrStr(200,false) create an light color string which lightest color value is 200 产生一个浅色的字串
   * ex: CreateRndClrStr(70,true) create an dark color string 产生一个深色的字串，最浅的颜色是70
   */
  public function CreateRndClrStr($startv,$darkflag=false)
  {
    mt_srand((double)microtime()*1000000);
    $cv=0;
    for($ii=0;$ii<3;$ii++){
      if($darkflag) $v =mt_rand(0,$startv);
      else  $v =mt_rand($startv,255);
      $cv+= $v<< $ii*8;
    }
    $hex= str_pad(dechex($cv), 6, "0", STR_PAD_LEFT);
    return "#".$hex;
  }

  public function CreateDirectory($curr_dir){
    if( file_exists( $curr_dir) ){
      if( !is_dir( $curr_dir) )return -1;  //存在但不是目录，新增目录失败
      else return 1;
    }else {
      $IsOk = mkdir( $curr_dir, 0775, true);
      if( $IsOk ) return 2;
      else return -2; // 新增目录失败
    }
  }

  protected function CreatePgLnkHtml( $link_str, $curr_page=1, $extend_page=5, $target="_top"){
    $rowsCount = $this->rowsCount;
    $rows_per_page =$this->RowsPerPage;
    if($rowsCount==0)return "无资料";
    $totalpage= (int)(($rowsCount-1) / $rows_per_page)+1;
    if($curr_page > $totalpage)$curr_page=$totalpage;
    $left_page = $curr_page-$extend_page;  //
    $right_page = $curr_page+$extend_page; //
    if($left_page <1){
        $right_page+= (-$left_page);    //左侧触底加入右侧
        $left_page=1;
    }
    if($right_page>$totalpage){
        $right_page=$totalpage;
        $left_page -= ($extend_page-$totalpage+$right_page);
        if($left_page <1)$left_page=1;
    }
    $target="target='{$target}'";
    $url="<a $target href='{$link_str}";
    $res= $url ."&pg=1'>&lt;&lt;</a>&nbsp;";  // 左<<
    if($left_page > 11){
        $lmid =(int)( ($left_page-1)/2);
        $res .= $url. "&pg={$lmid}'>{$lmid}..</a>&nbsp;";
    }
    for($ii=$left_page; $ii<=$right_page; $ii++){
        if( $curr_page==$ii) $res .= "&nbsp;[<strong>{$ii}</strong>]&nbsp;";
        else $res .= $url. "&pg={$ii}'>{$ii}</a>&nbsp;";
    }
    if($totalpage-$right_page > 10){
        $rmid =(int)( ($totalpage+$right_page)/2);
        $res .= "{$url}&pg={$rmid}'>..{$rmid}</a>&nbsp;";
    }
    $res .= "&nbsp;".  $url. "&pg={$totalpage}'>&gt;&gt;</a>";    //右 >>
//    $res .= " <small>总笔数{$rowsCount} 总页数{$totalpage}</small>";
//    $res .= " <small>总笔数{$rowsCount}";
    return $res;
  }


   /**
   * 由资料笔数产生选单字串 修改980924 此函数需配合 SetCurrParam 使用
    *----- @param $rowsCount 总笔数-->取自 全变 $rowsCount
    * @param $link_str 连结用字串，例如 a.php?fun=list
    * @param $str_arr 在页面前后不变的字串，例如 00012531,1,1231564 则阵列为 array("00012531," , ",1231564");
    * @param $ky 编码用的 key值
    *----- @param $rows_per_page 每页几行 --> 取自 全变 $RowsPerPage
    * @param $curr_page 目前页面
    * @param $extend_page 左右两侧展开的页面数，总显示页面连结数为 $extend_page*2 +1
    * @param $target -->url 的目的，例 _top _blank 等 
    * @return 选单字串， 例 << [1] 2 3 4 >> 总笔数
		* 
   */
	// 990104 由 c 值产生阵列
  protected function CreateEncodePgLnkHtmlByc( $link_str, $c, $ky, $curr_page=1, $extend_page=5, $target="_top"){
    $rowsCount = $this->rowsCount;
    $rows_per_page =$this->RowsPerPage;
    if($rowsCount==0)return "无资料";
    $totalpage= (int)(($rowsCount-1) / $rows_per_page)+1;
    if($curr_page > $totalpage)$curr_page=$totalpage;
    $left_page = $curr_page-$extend_page; //
    $right_page = $curr_page+$extend_page; //
    if($left_page <1){
      $right_page+= (-$left_page);  //左侧触底加入右侧
      $left_page=1;
    }
    if($right_page>$totalpage){
      $right_page=$totalpage;
      $left_page -= ($extend_page-$totalpage+$right_page);
      if($left_page <1)$left_page=1;
    }
	$arr= $this->Decrypt_c2Arr( $c, $ky);
	$arr['pg']=1;
    $c = $this->Encrypt_Arr2c($arr, $ky);	//Page 1
    $target="target='{$target}'";
    $url="<a $target href=". $link_str;
    $res= $url."&c={$c}>&lt;&lt;</a>&nbsp;"; // 左<<

    if($left_page > 11){
      $lmid =(int)( ($left_page-1)/2);
	    $arr['pg']=$lmid;
	    $c = $this->Encrypt_Arr2c($arr, $ky);  //Page 1
      $res .= $url. "&c={$c}>{$lmid}</a>&nbsp;";
    }

    for($ii=$left_page; $ii<=$right_page; $ii++){
      if( $curr_page==$ii)
        $res .= "&nbsp;<b>{$ii}</b>&nbsp;";
      else{
		    $arr['pg']=$ii;
		    $c = $this->Encrypt_Arr2c($arr, $ky);  //Page 1
         $res .= $url. "&c={$c}>{$ii}</a>&nbsp;";
      }
    }   

    if($totalpage-$right_page > 10){
      $rmid =(int)( ($totalpage+$right_page)/2);
	    $arr['pg']=$rmid;
  	  $c = $this->Encrypt_Arr2c($arr, $ky);  //Page 1
      $res .= $url. "&c={$c}>{$rmid}</a>&nbsp;";
    }
    $arr['pg']=$totalpage;
    $c = $this->Encrypt_Arr2c($arr, $ky);  //Page 1
    $res .= "&nbsp;". $url. "&c={$c}>&gt;&gt;</a>";  //右 >>
    $res .= " <small>总笔数{$rowsCount}</small>";
    return $res;
  }
  // 产生由类别和编号的字串
  public function Decrypt_c2Arr($c, $key=''){
    if(empty($key))$key = PAGE_ENCRYPT_KEY;
    $str = $this->URIAuthcode($c, "DECODE", $key);
    $arr=json_decode($str, true);
    return $arr;
  }
  // REmove Dir
  // src  http://andy.diimii.com/2009/10/php%E5%88%AA%E9%99%A4%E7%9B%AE%E9%8C%84%E8%B3%87%E6%96%99%E5%A4%BE-rmdirunlink/
  function delTree($dir) {  
    $files = glob( $dir . '/*', GLOB_MARK );  
    foreach( $files as $file ){  
      if( substr( $file, -1 ) == '/' )  
        $this->delTree( $file );  
      else  
        unlink( $file );  
    }   
    if (is_dir($dir)) rmdir( $dir );   
  }  

  public function Encrypt_Arr2c($arr, $key='', $expiry = 0){
    $str= json_encode($arr);
    if(empty($key))$key = PAGE_ENCRYPT_KEY;
    return $this->URIAuthcode($str, "ENCODE", $key, $expiry);
  }

  //给予会员点数
  public function GiveMemberExp( $e, $pt=0){
    $sql = "update `member` set `exp`= `exp` + {$pt} where `email`='{$e}' limit 1";
    $IsOk =$this->DB->Execute( $sql );
    if( !$IsOk )return -1;
    return 1;
  }

  // 取得两阵列的交集阵列
  protected function GetIntersectArray($arr,$arr2){
    $col=array();
    $intersect = array();
    foreach($arr as $it)$col[$it]= 1;
    foreach($arr2 as $it)$col[$it] ++;
    foreach($col as $k=>$v){
      if($v>1) $intersect[]=$k;
    }
    return $intersect;
  }

  public function GetNoLoginPrivArr(){
    $sql ="select `priv` from `role` where `roleID`=1";
    $one =$this->DB->GetOne( $sql );
    return $privarr = explode(",", $one);
  }

  /* Imagickize 将传入的档案製作缩图及处理
    @param $file 上传的档案阵列 $_FILES['MyFile']
    @param $imgDir 图档要放置的目录 ex. 2010JanAlbum/
    @param $thW 缩图的宽
    @param $thH 缩图的高
    @param $cut 切图参数
    @param &$msg 处理的讯息，正确回传图档阵列，失败回传错误讯息
    @return 1:正确 2:测试正确未处理任何图档 -1~-5错误
    ex: 
    */
  public function Imagickize($file, $imgDir, $thW, $thH, $cut, &$msg){
    $currDir = UPLOAD_DIR .$imgDir;
    $fn = strtolower($file['name']);    // ex. thread_jh.gif
    $fs = $file['size']; // ex. 340 (in bytes)
    $tempfs = $file['tmp_name'];  // ex. /var/tmp/phpF390NL
    $err = $file['error'];  // error:0
    if ($err>0 || $fs <=0){ $msg= "传输错误，错误次". $err; return -1; }
    $IsOk =$this->ChkImgFileExt($fn, $this->AllowedImgExtNameArr, $ext);
    if($IsOk <= 0){ $msg= "图档限传 ". implode(',',$this->AllowedImgExtNameArr); return -2;}
    if( $this->CreateDirectory($currDir)<= 0){ $msg= "建立目录[$currDir]失败";return -3;}
    $image=new Imagick( $tempfs);
    $height = $image->getImageHeight();
    $width = $image->getImageWidth();
    $size = $image->getImageSize();
    if( $height> MAX_IMG_HEIGHT || $width> MAX_IMG_WIDTH || $size> MAX_IMG_SIZE){
      $maxsizeKB= MAX_IMG_SIZE>>10;
      $msg= "<font color='red'>上传图档失败 超过限制</font> 图幅最大宽 " . MAX_IMG_WIDTH ."、最大高 ".MAX_IMG_HEIGHT . "、最大大小为{$maxsizeKB}KB<br />";
      $msg.= "你上传图档为 W{$width} x H{$height} Size{$size}<BR>";
      return -4;
    }
    $format = $image->getFormat();
    $msg= "原图 W{$width} x H{$height} Size{$size} Format {$format} <BR>";
    $_date = date("YmdHis"). rand(0, 100) ;    //ex: 20080911123456
    $newfn= "{$_date}.{$ext}";
    $newthumb= "t".$newfn;
    $newurl =  $currDir. $newfn;
    $newthumburl= $currDir. $newthumb;
    // Thumbnail
    $thumb = $image->clone();
    if($height/$width>$thH/$thW ){ //Higher
       $thumb->scaleImage($thW,0);
       $y= intval( ($height * $thW/$width -$thH)*0.5);
       $thumb->cropImage  ($thW, $thH, 0, $y);
       $thumb->writeImage( $newthumburl);
    }else{
       $thumb->scaleImage(0,$thH);
       $x =intval( ($width * $thH/$height -$thW)*0.5);
       $thumb->cropImage  ($thW, $thH, $x, 0);
       $thumb->writeImage( $newthumburl);
    }
    $msg .= "缩图 W". $thumb->getImageWidth()." H". $thumb->getImageHeight() ." Size". $thumb->getImageSize() . " Format".$thumb->getFormat()."<br>";
    $thumb->clear();
    $thumb->destroy();
    $operation= substr( $cut, 0, 1);  //First letter is the operation parameter
    switch( $operation){
      case 'a':
        @copy( $tempfs,  $newurl);
        break;
      case 'c':
        preg_match('/cw(\d+)h(\d+)/' ,$cut, $match);
        $thW= $match[1];
        $thH= $match[2];
        if($height/$width>$thH/$thW ){ //Higher
          $image->scaleImage($thW,0);
          $y= intval( ($height * $thW/$width -$thH)*0.5);
          $image->cropImage  ($thW, $thH, 0, $y);
          $image->writeImage( $newurl);
        }else{
          $image->scaleImage(0,$thH);
          $x =intval( ($width * $thH/$height -$thW)*0.5);
          $image->cropImage  ($thW, $thH, $x, 0);
          $image->writeImage( $newurl);
        }
        break;
      case 'n': //do nothing for testing usage
        $msg .= "未做任何操作";
        return 2;
        break;
      case 'r':  //ratio the image to n%
        preg_match('/r(\d+)/' ,$cut, $match);
        $ratio= $match[1]/100;
        $toW = $width *$ratio;
        $toH = $height *$ratio;
        $image->resizeImage  ( $toW, $toH ,imagick::FILTER_UNDEFINED ,1);
        $image->writeImage( $newurl);
        break;
      case 's': // scale the image to w120h400
        preg_match('/sw(\d+)h(\d+)/' ,$cut, $match);
        $toW= $match[1];
        $toH= $match[2];
        $image->resizeImage  ( $toW, $toH ,imagick::FILTER_UNDEFINED ,1);
        $image->writeImage( $newurl);
        break;
      default: // no option err
        $msg .= "未做任何操作";
        return -5;
        break;
    }
    $h =$image->getImageHeight();
    $w = $image->getImageWidth();
    $s = $image->getImageSize();
    $f = $image->getFormat();
    $msg .= "After: W{$w} x H{$h} Size{$s} Format {$f}<BR>";
    $image->clear();
    $image->destroy();
    @unlink($temp);
    $subdir=$imgDir; 
    $msg= array("img"=> $subdir.$newfn, "thumbimg"=> $subdir.$newthumb, "size"=>$size, "w"=>$w, "h"=>$h, "ext"=>$ext);
    return 1; //Succeed
  } 

  public function InitAllCatArr(){
    $this->AllCatArr = $this->GetAllCatArr(); 
  }

  /* IsValidDateTime 检查 DateTime 是否为正确的格式，日期时间字串仅接受数字和:-
    @param $str 输入日期 字串
    @param $datetime 正确的DateTime 字串
    @return 1:正确 -1:参数错误 -2:非正确格式
    ex: if($obj->IsValidDateTime($datetime, $purifiededatetime)>0)$datetime =$purifiededatetime; else $datetime="defalut DateTime";
    */
  public function IsValidDateTime($str, &$datetime){
    if( empty( $str)) return -1;
    $str= trim($str);
    $res = preg_match('/^[0-9:\-\s]+$/', $str);
    if( !$res) return -2;
    $datetime = $str;
    return 1;
  }

  /* IsValidEmail 检查 Email 是否为正确的格式
    @param $str 输入email 字串
    @param $email 正确的email 字串，已将 Email 转成小写，虽然RFC同意Email使用大写帐号。
    @return 1:正确 -1:参数错误 -2:非正确格式
    ex: if($obj->IsValidEmail($email, $purifiedemail)>0)$email =$purifiedemail; else $email="defalut@domain.name";
    */
  public function IsValidEmail($str, &$email){
    if( empty( $str)) return -1;
    $str= trim(strtolower($str));
    $res = preg_match('/^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $str);
    if( !$res) return -2;
    $email = $str;
    return 1;
  }

  /**
   * Create a good telephone string. Remove non-telephone characters.
   * @param $tel unarranged tel string. ex: (02)aA０123-4567 -->(02)0123-4567
   * @return An arranged tel string ex: (02)0123-4567ext123
   * IsValidTel 检查是否为正确电话格式，字串仅接受数字和 0-9 空白()ext-#*
   * ex if($obj->IsValidTel($_POST['tel'], $purifiedtel) <= 0)$purifiedtel="";
  */
  public function IsValidTel($str, &$tel){
    if( empty( $str)) return -1;
    $str= trim(strtolower($str));
    $sarr= array('０','１','２','３','４','５','６','７','８','９','－','（','）','、','，');
    $rarr= array('0','1','2','3','4','5','6','7','8','9','-','(',')',',',',');
    $newtel = str_replace($sarr, $rarr, $str);   //Transform Full-type to half type
    $res = preg_match('/^[0-9\-\s\(\)ext*#,]+$/', $newtel);
    if( !$res) return -2;
    $tel = $newtel;
    return 1;
  }

  public function JS_CntDn( $newlocation="", $cdtime=3000, $top=false)
  {
    if( !$newlocation ) $newlocation= $_SERVER["PHP_SELF"];
    if( $top )$otop="top";else $otop="this";
    $TJS_countdown=<<<DOC
	<BR><BR><form name="f">
    <input type="button" value="Click me" id="btn")>
    <script type="text/javascript">
      var time={$cdtime};
      function DisableEnable(objid){
  	  if(time<=0){
      {$otop}.location="{$newlocation}";
      }else{
        document.getElementById(objid).disabled = true;
        document.getElementById(objid).value = (time/1000) + " 秒后重新整理...";
        setTimeout("DisableEnable('" + objid + "')",1000);
      }
      time-=1000;
      }
      DisableEnable("btn");
    </script>
    </form>
DOC;
    return $TJS_countdown;
  }

  // Log 记录
  protected function LogError( $desc=""){
    $IP=$_SERVER["REMOTE_ADDR"];
    $email =$this->GetSession('email');
    $sql= "insert into recerror( `RecDT`, `Email`, `IP`, `Type`, `Desc` ) values ( NOW(), '{$email}', '{$IP}', '{$this->f}', '{$desc}')";
    return $IsOk = $this->DB->Execute( $sql );
  }

  protected function LogLogin( $desc=""){
    $IP=$_SERVER["REMOTE_ADDR"];
    $email =$this->GetSession('email');
    $sql= "insert into reclogin( `RecDT`, `Email`, `IP`, `Type`, `Desc` ) values ( NOW(), '{$email}', '{$IP}', '{$this->f}', '{$desc}')";
    return $IsOk = $this->DB->Execute( $sql );
  }

  protected function LogManage( $desc=""){
    $IP=$_SERVER["REMOTE_ADDR"];
    $email =$this->GetSession('email');
    $sql= "insert into recmng( `RecDT`, `Email`, `IP`, `Type`, `Desc` ) values ( NOW(), '{$email}', '{$IP}', '{$this->f}', '{$desc}')";
    $IsOk = $this->DB->Execute( $sql );
    if($IsOk)return true;
    sleep(1);
    return $this->DB->Execute( $sql );
  }

  protected function LogNobody( $desc=""){
    $IP=$_SERVER["REMOTE_ADDR"];
    $email =$this->GetSession('email');
    $sql= "insert into recnobody( `RecDT`, `Email`, `IP`, `Type`, `Desc` ) values ( NOW(), '{$email}', '{$IP}', '{$this->f}', '{$desc}')";
    return $IsOk = $this->DB->Execute( $sql );
  }

  protected function LogUser( $desc=""){
    $IP=$_SERVER["REMOTE_ADDR"];
    $email =$this->GetSession('email');
    $sql= "insert into recuser( `RecDT`, `Email`, `IP`, `Type`, `Desc` ) values ( NOW(), '{$email}', '{$IP}', '{$this->f}', '{$desc}')";
    return $IsOk = $this->DB->Execute( $sql );
  }

 /**
  * function LongEncode(): Encrypted a integer to a string.
  * @scope public
  * @param string $v input number
  * @return encrypted integer string
  * ex. LongEncode("5611236889745123")   //IBmyw1tytILDi
  */
  public function LongEncode($v){
    $code=  $this->DEFAULT_CODE;
    $shift= $this->CODE_SHIFT;  //6
    $len = strlen($code); //62
    $vs = (string)$v; 
    $segment = floor( log10( PHP_INT_MAX >>$shift));  //7
    $segnum = ceil(strlen($vs)/ $segment);
    $out="";
    for($ii=0; $ii<$segnum;$ii++){
      $seg = substr($vs,$ii*$segment, $segment);
      $v = (int)$seg <<$shift;
      if($v===0){
        $zeronum =strlen($seg);
        $out .= $code[0]. $code[$zeronum];
      }
      else
        do{
          $r = $v%$len;
          $out .= $code[$r];
          $v = ($v - $r)/$len;
        }while($v>0);
    }
    // 加入检查码
    $c=0;
    for($ii=0;$ii< strlen($out);$ii++)$c+=strpos($code, $out[$ii]);
    $out .= $code[$c%$len];
    return $out;
  }

 /**
  * function LongDecode(): Decrypted a string to a number string.
  * @scope public
  * @param string $s encrypted string
  * @return -1: string too short, -2: not a valid encrypted string and success: a number string
  * ex. LongDecode("Bmyw1tytILDi"); //5611236889745123
  * ex. $obj= new LongDEnCrypt();
  *     if( $v=$obj->LongDecode("SOMESTRING") <=0) print "Invalid encrypted string"; //Error control
  *     else // Do something
  */
  function LongDecode($s){
    $code=  $this->DEFAULT_CODE;
    $shift= $this->CODE_SHIFT;  //6
    //check decoded string
    $len = strlen($code);
    $slen = strlen($s)-1;
    if($slen<=1)return -1;
    $c=0;
    for($ii=0;$ii<$slen;$ii++)$c+=strpos($code, $s[$ii]);
    if($code[$c%$len] !== $s[$slen])return -2;   //Validate Err
    $segment = ceil( log( PHP_INT_MAX >>$shift)/log($len) );
    $dsegnum = ceil($slen/ $segment);
    $out="";
    for($ii=0; $ii<$dsegnum;$ii++){
      $v=0;
      for($jj=0;$jj<$segment;$jj++){
        if($jj+$ii*$segment >= $slen)break;
        $r=strpos($code, $s[(int)($jj+$ii*$segment)]);
        $v += $r* pow($len,$jj);
      }
      $v >>= $shift;
	  if( $v == 0){
        $zeronum=strpos($code, $s[1+$ii*$segment]);
        $v = str_repeat("0", $zeronum);
      }
      $out .= $v;
    }
    return $out;
  }


 /**
  * function MaskString(): Mask a string for security.
  * @scope public
  * @param string $s : input string
  * @param interger $masknum : the number of characters in the middle of a string to be masked, if masknum is negative,
           the returned string will leave abs(masknum) characters in both end untouched. 
  * @return a masked string 
  * ex. MaskString( "12345678",3)  : 123***78
  * ex. MaskString( "12345678",-3)  : 12*****8
  */
  public function MaskString($s, $masknum=3){
    $len= strlen($s);
    if($masknum<0) $masknum = $len + $masknum;
    if($len<3)return $s;
    elseif( $len< $masknum+1)return substr( $s, 0,1). str_repeat('*',$len-2). substr( $s, -1);
    $right=  ($len-$masknum)>>1;
    $left= $len- $right- $masknum;
    return substr( $s, 0,$left). str_repeat('*',$len-$right-$left). substr( $s, -$right);
  }

  public function ProduBannerMenuHtml(){
    $arr = $this->GetChildCatArrFromInitByParentID(0);
    $html="";
    $ii=0;
    foreach($arr as $it){
      $zarr = array("catID"=>$it['catID'], "zone"=>$ii );
      $c = $this->Encrypt_Arr2c( $zarr);
      $html .=<<<DOM
   <li class="btn{$ii}"><a class="drop" href="/index/MnPg&c={$c}" target="_self">&nbsp;<!--[if IE 7]><!--></a><!--<![endif]-->\n
DOM;
      $sbarr =$this->GetChildCatArrFromInitByParentID($it['catID']);
      $n = sizeof($sbarr);
      if($n>0){
        $html .= "<table class='ttb'><tr><td><ul>";
        foreach($sbarr as $sbit){
	      $zarr = array("catID"=>$sbit['catID'], "zone"=>$ii );
	      $c = $this->Encrypt_Arr2c( $zarr);
          $html .=<<<DOSM
     <li><a href="/index/MnPg&c={$c}" target="_self">{$sbit['text']}</a></li>\n
DOSM;
		}
        $html .= "</ul></td></tr></table>";
      }
      $html .= "</li>";
      $ii ++;
    }
    return $html;
  }

  public function ProduBannerBlock(){
    $cat0 = $this->GetChildCatArrFromInitByParentID(0);
    $cnt= sizeof( $cat0);
    $res="";
    for($ii=0; $ii <$cnt; $ii++){	//用 for 不用 foreach 为了下面的 encrypt
      $catID= $cat0[$ii]['catID'];
      $arr = array("catID" => $catID, "zone"=>$ii);
      $EcatID = $this->Encrypt_Arr2c( $arr);
      $cat0sub = $this->GetChildCatArrFromInitByParentID( $catID);
      $subhtml = "";
      $jj=0;
      foreach($cat0sub as $it){
        $zarr = array("catID" => $it['catID'], "zone"=>$ii, "subzone"=>$jj);
        $zEcatID = $this->Encrypt_Arr2c( $zarr);
        $subhtml .= "<li><a href='"/"?f=SbPg&cID={$zEcatID}'>{$it['text']}</a></li>";
		$jj++;
      }
      $dox = <<<DOX
    <ul class="select{$ii}"><li><a href="/index/MnPg&cID={$EcatID}"><b>{$cat0[$ii]['text']}</b></a>
    <div class="select_sub">
    <ul class="sub">
    {$subhtml}
    </ul>
    </div>
    </li></ul>
DOX;
      $res .= $dox;
    }
    return $res;
  }

  public function ProcClickCount( $nID=0){
    // 删除旧资料 日数 90 天
    $obsdays=90;
    if( $nID<=0 )return;
/*
    $sql ="select `IP` from `article_click` where `IP`= '{$_SERVER['REMOTE_ADDR']}' and `nID`={$nID}";
    $one= $this->DB->GetOne( $sql );
    if( !empty($one))return;
    $sql= "insert into article_click( `clickedDT`, `nID`, `IP`) values ( now(), {$nID}, '{$_SERVER['REMOTE_ADDR']}')";
    $IsOk= $this->DB->Execute( $sql );
    if( ! $IsOk)return;
*/
    $sql= "update `article` set `clicked`=`clicked`+1 where `nID` ={$nID}";
    $IsOk= $this->DB->Execute( $sql );
    return;
  }

  public function ProduSearchOptions(){
  	$cat = $this->GetChildCatArrFromInitByParentID(0);
    $res="";
  	foreach($cat as $it)$res .= "<option value={$it['catID']}>{$it['text']}</option>";
    return $res;
  }

  // 产生SELECT HTML 元件
  public function ProduSelect($arr, $name, $style=''){
    $html="<select name='{$name}' id='{$name}'  style='{$style}'>";
    foreach($arr as $k=>$v)$html .= "<option value='{$k}'>{$v}</option>";
    return $html. "</select>";
  }

  // 移除字串中非中文字/a-z0-9_的字元
  public function RemoveNonWord($str){
    mb_internal_encoding("UTF-8");
    mb_regex_encoding("UTF-8");
    return $str = mb_ereg_replace('[^一-龘a-zA-Z0-9_\.]', '', $str);
  }

  public function StubUTF8Width($str, $width=100, $add=""){
	$str= $this->StubUTF8String($str, $width); 	// 先取出纯字串
	return mb_strimwidth( $str ,0 ,$width, $add, "UTF-8" );
  }

  /**
  	 * StubUTF8String() -- UTF8下取出字串中特定的字数
    * @param string $str 传入的字串
    * @param int $length 取出的字数
    * @return 取回的字串
  */
  public function StubUTF8String($str, $length=100, $add="..."){
    $str = stripslashes( $str );
    $str = strip_tags( $str);  //拿掉HTML的Tag
    $str = str_replace("&nbsp;", " ", $str);
    // 移除非空白的间距变成一般的空白
    $str = preg_replace('/[\n\r\t]/', ' ', $str);
    // 移除重覆的空白
    $str = preg_replace('/\s(?=\s)/', '', $str);
    $str = trim($str);
    $olen = mb_strlen($str, "UTF-8");
    $str =mb_substr( $str, 0, $length, "UTF-8");
    if( mb_strlen($str, "UTF-8") != $olen) $str .= $add;
    return $str;;

  }

  public function URIAuthcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    if( $operation == 'DECODE') $string=str_replace(array("-","_"), array('+','/'),$string);
    $ckey_length = 4;
    $key = md5($key ? $key : "defaultKey" );
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
      $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for($j = $i = 0; $i < 256; $i++) {
      $j = ($j + $box[$i] + $rndkey[$i]) % 256;
      $tmp = $box[$i];
      $box[$i] = $box[$j];
      $box[$j] = $tmp;
    }
    for($a = $j = $i = 0; $i < $string_length; $i++) {
      $a = ($a + 1) % 256;
      $j = ($j + $box[$a]) % 256;
      $tmp = $box[$a];
      $box[$a] = $box[$j];
      $box[$j] = $tmp;
      $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if($operation == 'DECODE') {

    if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
      return substr($result, 26);
    } else {
      return '';
    }
    } else {
      return $keyc.str_replace(array("=","+","/"), array('','-','_'), base64_encode($result));
    }
  }

  /* Create a good url string
   * @param $link = raw url input ex: note.tcc.edu.tw
   * @return An arranged url string ex: http://note.tcc.edu.tw
  */
  public function URLStrMake($link){
    $scheme="";
    $link = trim( $link);
    if( preg_match ("/^((http:|https:|ftp:)\/*)/i", $link, $res) ){
      $schemefull=$res[1];
      $scheme=$res[2];
      $link= "{$scheme}//". preg_replace ( "/^((http:|https:|ftp:)\/*)/" ,"" ,$link );
      return $link;
    }
    return $link;
  }
}//End Class


class basic_class {
  public function EmailTo($From, $To, $subject, $message)
  {
    $headers = "MIME-Version: 1.0 \nContent-type: text/html; charset=UTF-8 \n";
    $headers .= "From: {$From}\r\nReply-To: {$From}\r\nX-Mailer: PHP/".phpversion();
    $content = "<html><head><title>{$subject}</title></head><body>". $message;
    $bool = mail($To, $subject." (UTF-8 Encoded)", $content, $headers);
    return $bool;
  }

}


/*高精度的缩图类别
功能：利用PHP的GD函式生成高质量缩图 运行环境:PHP5.01/GD2
类别说明：
可以选择是/否裁图，是/否放大图像。如果裁图则生成的图的尺寸与您输入的一样。
原则：尽可能保持原图完整，如果不裁图，则按照原图比例生成新图； 比例以输入的长或者宽为基准，如果不放大图像，则当原图尺寸不大于新图尺寸时，维持原图尺寸
$imgout:输出图片的位址
$imgsrc:源图片位址
$width:新图的宽度
$height:新图的高度
$cut:是否裁图，1为是，0为否
$enlarge:是否放大图像，1为是，0为否
*/

class Image_Proc_class {
  //图片类型
  var $type;
  //实际宽度
  var $width;
  //实际高度
  var $height;
  //改变后的宽度
  var $resize_width;
  //改变后的高度
  var $resize_height;
  //是否裁图
  var $cut;
  //是否放大图像
  var $enlarge;
  //来源图档
  var $srcimg;
  //目标图档位址
  var $dstimg;
  //临时建立的图档
  var $im;
  //回传状态
  var $status;

  //检查图档附档名是否合规定
  public function ChkImgFileExt($filename, $AllowedImgExtNameArr, &$ext)
  {
    $ext_fname = strtolower(end( explode(".", $filename ))); //取最后的副档名
    if (@in_array( $ext_fname ,$AllowedImgExtNameArr )){ $ext= $ext_fname;	 return 1; }
    return -1;
  }

  static public function GetImgExif($img)
  {
    $exif = @read_exif_data ( $img );
    return $exif;
  }

  public function ResizeImg($imgout, $imgsrc, $width=0 ,$height=0 ,$cut="0" ,$enlarge="0")
  {
    //目标图档位址
    $this->dstimg = $imgout;
    //来源图档
    $this->srcimg = $imgsrc;
    //是否裁图
    $this->cut = $cut;
    //是否放大图像
    $this->enlarge = $enlarge;
    //初始化图档
    $this->initi_img();
    //来源图档实际宽度
    $this->width = imagesx($this->im);
    //来源图档实际高度
    $this->height = imagesy($this->im);

    //改变后的宽度
    if( $width >1 )
      $this->resize_width = $width;
    elseif( $width == 0 )
      $this->resize_width = $this->width;
    else{
      $r =$width;
      $this->resize_width = (int)($this->width* sqrt($r ));
    }

    //改变后的高度
    if( $height >1 )
      $this->resize_height = $height;
    elseif( $height == 0 )
      $this->resize_height = $this->height;
    else{
      $r =$height;
      $this->resize_height = (int)($this->height* sqrt($r ));
    }
    //生成新图档
    $this->newimg();
    //结束图形
    ImageDestroy ($this->im);
  }

 public function ResizeImgOnBorderRatio($imgout, $imgsrc, $width=0 ,$height=0 ,$cut="0" ,$enlarge="0")
 {
  $this->dstimg = $imgout;
  $this->srcimg = $imgsrc;
  $this->cut = $cut;
  $this->enlarge = $enlarge;
  $this->initi_img();
  $this->width = imagesx($this->im);
  $this->height = imagesy($this->im);
  if( $width >1 ) $this->resize_width = $width;
  elseif( $width == 0 ) $this->resize_width = $this->width;
  else $this->resize_width = (int)($this->width* $width);

  if( $height >1 ) $this->resize_height = $height;
  elseif( $height == 0 ) $this->resize_height = $this->height;
  else $this->resize_height = (int)($this->height* $height );
  $this->newimg();
  ImageDestroy ($this->im);
 }

 public function newimg()
 {
  if(($this->cut)=="1"){
    if($this->enlarge=='0')//不放大图像，只缩图
    {
      //调整输出的图片大小，如不超过指定的大小则维持原大小
      if($this->resize_width < $this->width)
      $resize_width = $this->resize_width;
      else
      $resize_width = $this->width;

      if($this->resize_height < $this->height)
      $resize_height = $this->resize_height;
      else
      $resize_height = $this->height;
    }
    else//放大图像
    {
      $resize_width = $this->resize_width;
      $resize_height = $this->resize_height;
    }

    //改变后的图档的比例
    $resize_ratio = ($this->resize_width)/($this->resize_height);
    //实际图档的比例
    $ratio = ($this->width)/($this->height);

    if($ratio>$resize_ratio)
    //高度优先
    {
      $newimg = imagecreatetruecolor($resize_width,$resize_height);
      //生成白色背景
      $white = imagecolorallocate($newimg, 255, 255, 255);
      imagefilledrectangle($newimg,0,0,$resize_width,$resize_height,$white);
      $mod_width = ($this->height)*$resize_ratio;
      $mod_height = $this->height;

      imagecopyresampled($newimg, $this->im, 0, 0, 0, 0, $resize_width,$resize_height, $mod_width, $mod_height);

      $this->status = ImageJpeg ($newimg,$this->dstimg,100);
    }
    else
    //宽度优先
    {
      $newimg = imagecreatetruecolor($resize_width,$resize_height);
      //生成白色背景
      $white = imagecolorallocate($newimg, 255, 255, 255);
      imagefilledrectangle($newimg,0,0,$resize_width,$resize_height,$white);
          $mod_width = $this->width;
          $mod_height = (int)(($this->width)/$resize_ratio);
      imagecopyresampled($newimg, $this->im, 0, 0, 0, 0, $resize_width, $resize_height, $mod_width, $mod_height);
      $this->status = ImageJpeg ($newimg,$this->dstimg,100);
    }
  }
  else    //不裁图
  {
    if($this->enlarge=='0')//不放大图像，只缩图
    {
      //调整输出的图片大小，如不超过指定的大小则维持原大小
      if($this->resize_width < $this->width)
      $resize_width = $this->resize_width;
      else
      $resize_width = $this->width;

      if($this->resize_height < $this->height)
      $resize_height = $this->resize_height;
      else
      $resize_height = $this->height;
    }
    else//放大图像
    {
      $resize_width = $this->resize_width;
      $resize_height = $this->resize_height;
    }
    //改变后的图档的比例
    $resize_ratio = ($this->resize_width)/($this->resize_height);
    //实际图档的比例
    $ratio = ($this->width)/($this->height);

    $newimg = imagecreatetruecolor($resize_width, $resize_height);
    $white = imagecolorallocate($newimg, 255, 255, 255);
      //imagefilledrectangle($newimg,0,0,$resize_width,($resize_width)/$ratio,$white);
          imagefilledrectangle($newimg,0,0,$resize_width, $resize_height,$white);
      //imagecopyresized($newimg, $this->im, 0, 0, 0, 0, $resize_width, ($resize_width)/$ratio, $this->width, $this->height);
      imagecopyresized($newimg, $this->im, 0, 0, 0, 0, $resize_width, $resize_height, $this->width, $this->height);
    //imagecopyresampled($newimg, $this->im, 0, 0, 0, 0, $resize_width, ($resize_width)/$ratio, $this->width, $this->height);
    $this->status = ImageJpeg ($newimg,$this->dstimg,100);

  }//end else
 }

  //初始化图档
  function initi_img()
  {
    //取得图片的类型
    $getimgdata=@getimagesize($this->srcimg);
    $this->type = $getimgdata['mime'];

    //根据类型选择读取方式
    if($this->type=='image/gif')
    {
    $this->im = imagecreatefromgif($this->srcimg);
    }
    else if($this->type=='image/png')
    {
    $this->im = imagecreatefrompng($this->srcimg);
    }
    else
    {
    $this->im = imagecreatefromjpeg($this->srcimg);
    }
  }
} //End Class Image


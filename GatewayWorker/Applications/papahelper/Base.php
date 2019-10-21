<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/16
 * Time: 12:06
 */



class Base
{
  public static function intToBytes($val) {
        $val = (int)$val;
        $byte = array();
        //低位在前，即小端法表示
        $byte[0] = ($val >> 24 & 0xff);
        $byte[1] = ($val >> 16 & 0xFF);
        $byte[2] = ($val >> 8 & 0xFF);
        $byte[3] = ($val & 0xFF);//掩码运算
        return $byte;
    }
  //格式化发送的数据
    public static function formatData($type,$uid,$text=""){
  		$byte1[0]=$type;
      	//uid为PC单发
      	if($text==""){
          $text=$uid;
        }else
          $text=$uid.",".$text;
  		$byte2=Base::intToBytes(strlen($text));
      	$byte=array_merge($byte1,$byte2);
      	echo $text;
        return Base::toStr($byte).$text;
	}
  //格式化发送的数据 ----不带文本
    public static function formatDataType($type,$text){
  		$byte1[0]=$type;
  		if($type==4){
            $byte2=Base::intToBytes($text);
            $byte=array_merge($byte1,$byte2);
            return Base::toStr($byte);
        }else{
            $byte2=Base::intToBytes(strlen($text));
            $byte=array_merge($byte1,$byte2);
            return Base::toStr($byte).$text;
        }
	}

    public static function formatDataText($type,$text){
        $byte1[0]=$type;
        $byte2=Base::intToBytes(strlen($text));
        $byte=array_merge($byte1,$byte2);
        return Base::toStr($byte);
    }

    //均分文本 arr文本数组  $allnum分配个数
    public static function setEqualList($arr,$allnum){
        $mod=count($arr)%$allnum;
        $num=(count($arr)-$mod);
        $n=0;
        $jg=[$allnum];
        var_dump($arr);
        for($i=0;$i<$allnum;$i++){
            $msgall = "";
            $en = $num;
            if ($i < $mod) $en = $num + 1;
            for ($j = $n; $j < $n + $en; $j++)
            {
                if($arr[$j]!=("")){
                    $msgall = $msgall.$arr[$j] . "|";
                }
            }
            $n += $en;
            if ($msgall!=(""))
                $jg[$i] =substr($msgall,0,strlen($msgall)-1);
            else
                $jg[$i] = "";
        }
        return $jg;
    }

    public static function getConnList($uid){
        $where['conn_stat']=1;
        $where['uid']=$uid;
        $where['is_checked']=1;
        $list = Db::name('ab_devices')->where($where) ->field('run_code')->select();
        return $list;
    }
  //取出中间字符串
      public static function subStartEnd($text,$star,$end){
        $s1=-1;
        $e1=-1;
        if(strlen($star)>0)
  			$s1=strpos($text,$star);
      	if($s1<=0)$s1=0;
        else $s1+=strlen($star);
        //echo $s1."\n";
        if(strlen($end)>0)
        	$e1=strpos($text,$end,$s1+1);
      	if($e1>strlen($text)||$e1<0)$e1=strlen($text);
        //else $e1+=strlen($end);
        //echo $e1."\n";

		if($e1>$s1)return substr($text,$s1,$e1-$s1);
        return "";
	}
    /**

* 转换一个String字符串为byte数组

* @param $str 需要转换的字符串

* @param $bytes 目标byte数组

* @author Zikie

*/
	public static function getBytes($string) {

        $bytes = array();
        for($i = 0; $i < strlen($string); $i++){
             $bytes[] = ord($string[$i]);
        }
        return $bytes;
    }

  //加密
      public static function entokey($v)
  {
      $miao=Base::getBytes("5bCP54aK6ZuG5o6n5bCx5piv5aW95aSp5LiL56ys5LiA5peg5pWM5omL");
      $textbyte=Base::getBytes($v);
      $n=0;
      for($i=0;$i<count($textbyte);$i++){
        $textbyte[$i]+=$miao[$n];
        $n++;
        if($n>=count($miao))$n=0;
      }
        //没有base64
      return Base::toStr($textbyte);
   }

  //解密
      public static function defromkey($v)
  {
      $miao=Base::getBytes("5bCP54aK6ZuG5o6n5bCx5piv5aW95aSp5LiL56ys5LiA5peg5pWM5omL");
      $textbyte=Base::getBytes(base64_decode($v));
      $n=0;
      for($i=0;$i<count($textbyte);$i++){
        $textbyte[$i]-=$miao[$n];
        $n++;
        if($n>=count($miao))$n=0;
      }
      return Base::toStr($textbyte);
   }
/**

* 将字节数组转化为String类型的数据

* @param $bytes 字节数组

* @param $str 目标字符串

* @return 一个String类型的数据

*/
	public static function toStr($bytes) {
        $str = '';
        foreach($bytes as $ch) {
            $str .= chr($ch);
        }

           return $str;
    }
}
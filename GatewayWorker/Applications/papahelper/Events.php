<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;
use \GatewayWorker\Lib\Db;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    public static $dataArray=[];
    public static $dataJsSendSize=[];

    public static $db;
    public static function onWorkerStart($businessWorker)
    {
        self::$db=Db::instance('user');
    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        //echo "连接成功了".$client_id."     IP:".$_SERVER['REMOTE_ADDR']."\n";

        // 向当前client_id发送数据
        //Gateway::sendToClient($client_id, "Hello $client_id\r\n");
        // 向所有人发送
        //Gateway::sendToAll("$client_id login\r\n");
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function onMessage($client_id, $message)
    {

        if($_SERVER['GATEWAY_PORT']==7855){
            if(!isset($_SESSION['run'])){
                self::bingMobile($client_id, $message);
            }else{
                self::MobileEvent($client_id, $message);
            }
        }else{
            if(!isset($_SESSION['run'])){
                $array_message=explode("|",$message);
                $_SESSION['run']=$array_message[0];
                echo "绑定消息js:".$message."\n";
                if(isset(self::$dataArray[$_SESSION['run']])){
                    $array= Gateway::getClientIdByUid($_SESSION['run']);
                    foreach($array as $item){
                        Gateway::closeClient($item);
                    }
                    Gateway::bindUid($client_id, $_SESSION['run']);
                    $SESSION=Gateway::getSession(self::$dataArray[$_SESSION['run']]);
                    if($SESSION){
                        $quest="SELECT id FROM `fk_ab_login_user` WHERE tel='$array_message[1]'";
                        $uid=self::$db->row($quest)['id'];

                        $quest="SELECT sum(Receive_size) FROM `fk_ab_login_log` WHERE uid=$uid";
                        $connum=self::$db->row($quest)['sum(Receive_size)'];

                        $text=$SESSION['width']."|".
                            $SESSION['height']."|".$SESSION['isAccessibility']."|".$SESSION['isRoot'];
                        Gateway::sendToClient($client_id,"6,".$text."|".$SESSION['sendText']."|".$connum);

                        $pid=$SESSION['uid_data'];
                        $time=time();
                        $ip=$_SERVER['REMOTE_ADDR'];

                        self::$db->query("INSERT INTO `fk_ab_login_log` (uid,pid,login_time,ip) VALUES ($uid,$pid,$time,'$ip') ");
                        $_SESSION['time']=$time;
                        self::$dataJsSendSize[$_SESSION['run']]=0;
                        $_SESSION['uid_data']=$uid;
                    }
                    Gateway::sendToClient(self::$dataArray[$_SESSION['run']],self::formatData(5,$array_message[1]));



                }else{
                    Gateway::sendToClient($client_id,"5,2");
                }
            }else{
                //echo "js消息".$message."\n";
                $array=explode(";",$message);
                foreach ($array as $list){
                    $ls=explode(",",$list);
                    if (count($ls)>1){
                        Gateway::sendToClient(self::$dataArray[$_SESSION['run']],self::formatData(9,$list));
                    }
                }
            }
        }
        // 向所有人发送
        //Gateway::sendToAll("$client_id said $message\r\n");
    }


    //绑定手机
    public static function bingMobile($client_id, $message){
        $textls=explode('|',$message['text']);
        if(isset(self::$dataArray[$textls[0]])){
            Gateway::sendToClient($client_id,self::formatData(4,13));
            Gateway::closeClient($client_id);
        }else if(count($textls)>7){
            $ip=$_SERVER['REMOTE_ADDR'];
            $_SESSION['run']=$textls[0];
            $tel=self::subStartEnd($textls[0],"","-");
            $time=time();
            $quest="SELECT uid FROM `fk_ab_devices_user` WHERE imei='$textls[1]'";
            $find_devices=self::$db->row($quest);
            if(!$find_devices) {
                self::$db->row("INSERT INTO `fk_ab_devices_user` (imei,mobile_model,reg_time,reg_ip,isRoot,mobile_board,mobile_tel,sdk_ver) VALUES ('$textls[1]','$textls[2]',$time,'$ip',$textls[6],'$textls[7]','$tel',$textls[8])");
                $find_devices=self::$db->row($quest);
            }else{
                $uid=$find_devices["uid"];
                self::$db->query("UPDATE `fk_ab_devices_user` set mobile_tel='$tel',isRoot=$textls[6]  WHERE uid='$uid'");
            }
            $uid=$find_devices["uid"];
            $_SESSION['uid_data'] = $uid;
            $_SESSION['width'] = $textls[3];
            $_SESSION['height'] = $textls[4];
            $_SESSION['isAccessibility'] = $textls[5];
            $_SESSION['isRoot'] = $textls[6];
            $_SESSION['sendText'] = $textls[7]."|".$textls[2]."|".$textls[8];
            $_SESSION['num']=0;
            $_SESSION['time']=$time;
            self::$db->row("INSERT INTO `fk_ab_devices_log` (uid,login_time,ip) VALUES ($uid, $time,'$ip')");

            //初始化保存对象
            self::$dataArray[$textls[0]]=$client_id;
            echo "绑定消息:uid->".$message['text']."\n";
        }else{
            //未授权----拒绝连接   断开
            Gateway::sendToClient($client_id,self::formatData(4,12));
            Gateway::closeClient($client_id);
        }

    }
    //手机消息事件
    public static function MobileEvent($client_id, $message){
        $text=$message['text'];
        $run_code=$_SESSION['run'];
        switch ($message['type']){
            case 1:
                Gateway::sendToUid($run_code, $text);
                $len=strlen($text);
                $_SESSION['num']+=$len;
                if(isset(self::$dataJsSendSize[$run_code])){
                    self::$dataJsSendSize[$run_code]+=$len;
                }
                break;
            case 2:
            {
                switch($text){
                    case 2://无障碍未开启
                        $_SESSION['isAccessibility'] = 0;
                        break;
                    case 5://无障碍开了
                        $_SESSION['isAccessibility'] =1;
                        break;
                }
            }
                break;
            case 3:
                //echo "手机回传:".$text."\n";
                break;
            case 4:
                //echo "手机回传:".$text."\n";
                break;
            case 6://分辨率
                //echo "手机回传:".$text."\n";
                Gateway::sendToUid($run_code, $message['type'].",".$text);

                break;
        }
    }



//------------------------------------------------------------------------------------------
    public static function formatData($type,$text){
        $byte1[0]=$type;
        if($type==4){
            $byte2=self::intToBytes($text);
            $byte=array_merge($byte1,$byte2);
            return self::toStr($byte);
        }else{
            $byte2=self::intToBytes(strlen($text));
            $byte=array_merge($byte1,$byte2);
            return self::toStr($byte).$text;
        }
    }

    public static function toStr($bytes) {
        $str = '';
        foreach($bytes as $ch) {
            $str .= chr($ch);
        }
        return $str;
    }

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
        $miao=self::getBytes("5bCP54aK6ZuG5o6n5bCx5piv5aW95aSp5LiL56ys5LiA5peg5pWM5omL");
        $textbyte=self::getBytes($v);
        $n=0;
        for($i=0;$i<count($textbyte);$i++){
            $textbyte[$i]+=$miao[$n];
            $n++;
            if($n>=count($miao))$n=0;
        }
        //没有base64
        return self::toStr($textbyte);
    }

    //取出中间字符串
    public static function subStartEnd($text,$star,$end){
        $s1=-1;
        $e1=-1;
        if(strlen($star)>0)
            $s1=strpos($text,$star);
        if($s1<0)$s1=0;
        else $s1+=strlen($star);
        //echo $s1."\n";
        if(strlen($end)>0)
            $e1=strpos($text,$end,$s1+1);
        if($e1>strlen($text)||$e1<0)$e1=strlen($text);
        //else $e1+=strlen($end);
        if($e1>$s1)return substr($text,$s1,$e1-$s1);
        return "";
    }
    //-----------------------------------------------------------------------------------
    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id)
    {
        echo "断开:".$client_id."\n";
        $time=time();
        if(isset($_SESSION['run'])) {
            $timelen=$time-$_SESSION['time'];

            $uid=$_SESSION['uid_data'];
            if($_SERVER['GATEWAY_PORT']==7855) {
                $size=$_SESSION['num'];
                self::$db->query("UPDATE `fk_ab_devices_log` set out_time=$timelen,send_size=$size  WHERE uid='$uid' ORDER BY login_time DESC LIMIT 1");
                //发给PC----断线
                Gateway::sendToUid($_SESSION['run'], "5,1");
                unset(self::$dataArray[$_SESSION['run']]);
            }else{
                if(isset(self::$dataArray[$_SESSION['run']]))
                    Gateway::sendToClient(self::$dataArray[$_SESSION['run']],self::formatData(4,4));
                $size=self::$dataJsSendSize[$_SESSION['run']];

                self::$db->query("UPDATE `fk_ab_login_log` set connect_time=$timelen,Receive_size=$size  WHERE uid='$uid' ORDER BY login_time DESC LIMIT 1");
                unset(self::$dataJsSendSize[$_SESSION['run']]);
            }
        }
        // 向所有人发送
        //GateWay::sendToAll("$client_id logout\r\n");
    }
}

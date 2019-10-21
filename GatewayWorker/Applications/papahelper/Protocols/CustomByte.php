<?php
namespace Protocols;
class CustomByte
{
    /**
     * 检查包的完整性
     * 如果能够得到包长，则返回包的在buffer中的长度，否则返回0继续等待数据
     * 如果协议有问题，则可以返回false，当前客户端连接会因此断开
     * @param string $buffer
     * @return int
     */
    public static function input($buffer)
    {
        if(strlen($buffer)<5)
        {
          //echo $buffer."\n";
            return 0;
        }
        $nType=ord(substr($buffer, 0,1));
        //echo $nType."\n";

       // echo "1111  ".ord(substr($buffer, 4,1))."\n";
        //echo (substr($buffer, 2,1))."\n";
        if($nType==2)
          return 5;
        else{
          $size=unpack('Ntotal_length', substr($buffer, 1,4))['total_length'];
          //echo $size."\n";
          return $size+5;
        }
    }

    /**
     * 打包，当向客户端发送数据的时候会自动调用
     * @param string $buffer
     * @return string
     */
    public static function encode($buffer)
    {
        return $buffer;
    }

    /**
     * 解包，当接收到的数据字节数等于input返回的值（大于0的值）自动调用
     * 并传递给onMessage回调函数的$data参数
     * @param string $buffer
     * @return string
     */
    public static function decode($buffer)
    {

      
      $nType=ord(substr($buffer, 0,1));
      if($nType==2){
          $size=unpack('Ntotal_length', substr($buffer, 1,5))['total_length'];
          $jg['type']=$nType;
          $jg['text']=$size;
      }else{
          $jg['type']=$nType;
          $jg['text']=substr($buffer,5);
      }
      return $jg ;
    }
}
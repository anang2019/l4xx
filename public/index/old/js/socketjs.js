

var ws;

var position;
var nTime;
var isNoMove;
var isMove;
var mobile_width=540;
var mobile_height=960;
var img_width=248;
var img_height=441;
var isConnnct;
var tel;
var tim=0;
var runDsq=0;
function connect_click(){
    tel=document.getElementById("connect_tel").value;
    var num=document.getElementById("connect_num").value;
    if(tel=="" && num==""){
        oAlert('连接码有误!');
        //return;
    }
    ws = new WebSocket('ws://159.138.232.54:2346');
    ws.onopen = function(){
        var img=document.getElementById("d");
        img_width=img.width;
        img_height=img.height;
        //console.log(img_width+"   rrr  "+img_height);
        //var uid =tel+"-"+num;
        var uid = '13945008550-60e5d90';

        ws.send(uid+"|"+$("#user_tel").text());
        //console.log("接连成功:"+img_width+"  "+img_height);
        isConnnct=true;
    };
    ws.onmessage = function(event){
        var text=event.data;
        //console.log(text);
        if(text.size>1024){
            var url1 = URL.createObjectURL(text);
            document.getElementById("d").src=url1;
            //document.getElementById("d").style.backgroundImage="url("+url1+")"
        }else{
            var reader = new FileReader();
            reader.readAsText(text, 'utf-8');
            reader.onload = function (e) {
                console.info(reader.result);
                var ls=reader.result.split(",");
                if(ls.length>1){
                    switch (ls[0]){
                        case "5":
                            switch (ls[1]){
                                case "1":
                                    oAlert1("手机连接已断开!");
                                    closeSocket();
                                    break;
                                case "2":
                                    oAlert1("手机不在线!");
                                    closeSocket();
                                    showMobile();
                                    break;
                            }
                            break;
                        case "6":
                            var ts=ls[1].split("|");
                            mobile_width=Number(ts[0]);
                            mobile_height=Number(ts[1]);
                            //console.log("1111  "+ls[1]);
                            //2----3
                            showMobile(true);
                            setShow(ts[4],ts[5],ts[6],ts[2],ts[3]);
                            if(runDsq==0){
                                setThread();
                            }
                            break;
                    }
                }
            }
        }
        //console.log(event.data);
    };
    ws.onclose = function(event){
        isConnnct=false;
        $('.phone-btn1').text("重新连接");
        $('.phone-btn2').css('display','block');

    }
}

function setThread(){
    runDsq=setInterval(function () {
        if(isConnnct){
            tim++;
            $('#mb_js').text(tim);
        }else{
            tim=0;
        }
    }, 1000);
}
function closeSocket(){
    ws.close();
}

function send(text){
    if(isConnnct)
    ws.send(text)
}

function getAndroidVer(num){
    switch (num){
        case "18":
            return "4.3";
        case "19":
            return "4.4";
        case "20":
            return "4.4w";
        case "21":
            return "5.0";
        case "22":
            return "5.1";
        case "23":
            return "6.0";
        case "24":
            return "7.0";
        case "25":
            return "7.1";
        case "26":
            return "8.0";
        case "27":
            return "8.1";
        case "28":
            return "9.0";
        case "29":
            return "10.0";
        default:
            return num;
    }
}

function setShow(pp,xh,bb,isA,isR){
    $('#mb_pp').text(pp);
    $('#mb_xh').text(xh);
    $("#mb_num").text(tel);
    if(bb<24&&isR=="0")
        $('#mb_xtd').css('display','block');
    if(isA=="0")
        $('#mb_now').css('display','block');
    $('mb_bb').text(getAndroidVer(bb));
}

function getMousePos(){

    var objTop = getOffsetTop(document.getElementById("d"));//对象x位置
    var objLeft = getOffsetLeft(document.getElementById("d"));//对象y位置

    var mouseX = event.clientX+document.body.scrollLeft;//鼠标x位置
    var mouseY = event.clientY+document.body.scrollTop;//鼠标y位置
//计算点击的相对位置
    var objX = mouseX-objLeft;
    var objY = mouseY-objTop;

    clickObjPosition = objX + "," + objY;

    return {x:objX,y:objY};
    //alert(clickObjPosition);
}

function onMouseMove(){
    if(isNoMove){
        isMove=true;
        var pos=getMousePos();
        if(Math.abs(pos.x-position.x)>5&&Math.abs(pos.y-position.y)>5){
            var timenew=new Date().getTime();
            var t=timenew-nTime;
            nTime=timenew;
            send("2,"+getX(position.x)+","+getY(position.y)+","+getX(pos.x)+","+getY(pos.y)+","+t+";");
            position=pos;
        }
    }
}

function onMouseUpCheck() {
    //console.log("弹起");
    var pos=getMousePos();
    if(isMove){
        var t=new Date().getTime()-nTime;
        isMove=false;
        send("2,"+getX(position.x)+","+getY(position.y)+","+getX(pos.x)+","+getY(pos.y)+","+t+";");
    }else{
        send("1,"+getX(position.x)+","+getY(position.y)+";");
    }
    isNoMove=false;
}
function onMouseDownCheck() {
    position=getMousePos();
    isNoMove=true;
    nTime=new Date().getTime();
    //console.log("按下"+position.y+"  "+nTime)
}
function onMouseOutCheck() {
    if(isNoMove){
        isNoMove=false;
        var wz=getMousePos();
        if (wz.x < 0) wz.x = 1;
        if (wz.x > img_width) wz.x = img_width;
        if (wz.y < 0) wz.y = 1;
        if (wz.y > img_height) wz.y = img_height;
        if (isMove)
        {
            isMove=false;
            var t=new Date().getTime()-nTime;
            send("2,"+getX(position.x)+","+getY(position.y)+","+getX(wz.x)+","+getY(wz.y)+","+t+";");
        }
    }
}
function backCheck() {
    send("3,1;");
}
function homeCheck() {
    send("3,2;");
}
function menuCheck() {
    send("3,3;");
}

function getX(x){
    //console.log(x+"  "+mobile_width+"  "+img_width)
    return Math.ceil(x * mobile_width / img_width);
}
function getY(y){
    return Math.ceil(y * mobile_height / img_height);
}
function getOffsetTop(obj){
    var tmp = obj.offsetTop;
    var val = obj.offsetParent;
    while(val != null){
        tmp += val.offsetTop;
        val = val.offsetParent;
    }
    return tmp;
}
function getOffsetLeft(obj){
    var tmp = obj.offsetLeft;
    var val = obj.offsetParent;
    while(val != null){
        tmp += val.offsetLeft;
        val = val.offsetParent;
    }
    return tmp;
}





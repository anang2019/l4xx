/* 弹窗 */
 function oAlert(n){
	$('body').append('<div class="alert">'+n+'</div>')
	 setTimeout(function() {
		 $('.alert').fadeOut()
	 }, 2000);
 }
 
 /* 点击弹窗 */
  function oAlert1(n){
 	$('body').append('<div class="black1"></div><div class="alert1">'+n+'</div>')
	$('.black1').click(function(){
		$('.black1').hide()
		$('.alert1').hide()
	})

 }
 
 function isMobile(input){
    if(input.match(/^1[3456789]\d{9}$/)){
        return true;
    }
}
 
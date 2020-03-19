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
 
 $(function(){
	 
	 $('.yzm').click(function(){
	 	$('.yzm').addClass('yzm1')
	 	$('.yzm').text('已发送')
	 })
	 
	 $('.back1').click(function(){
	 	$('.login-1').hide()
	 	$('.login-2').show()
	 })
	 $('.back2').click(function(){
	 	$('.login-2').hide()
	 	$('.login-1').show()
	 })
	 $('.list-box li').mouseover(function(){
	 	$(this).addClass('withe')
	 	$('.list-box li').not(this).removeClass('withe')
	 })
	 $('.list-box li').mouseout(function(){
	 	$('.list-box li').removeClass('withe')	
	 })
	 $('.login-a').click(function(){
	 	$('.login-box').show()
	 	$('.login-box').addClass('bounceInDown')
	 	$('.black').show()
	 })
	 $('.center-box li').on('mouseover',function(){
	 	$(this).find('.blue-line').css({'width':'100%'})
	 	$('.center-box li').not(this).find('.blue-line').css({'width':'0%'})
	 })
	 $('.center-box li').on('mouseout',function(){
	 	$('.center-box li').find('.blue-line').css({'width':'0%'})
	 })
	 
	 
	 
	 /* 登录注册tab方法 */
	 
	 $(function(){
	 	$('.login-tab li').eq(0).css({'background':'#e14f60','color':'#fff'})
	 	$('.login').eq(0).show()
	 	$('.login-tab li').click(function(){
	 		$('.login-box').show()
	 		$('black').show()
	 		$(this).css({'background':'#e14f60','color':'#fff'}) 
	 		$('.login-tab li').not(this).css({'background':'#e7e7e7','color':'#000'})
	 		var Num=$('.login-tab li').index(this)
	 		$('.login').hide()
	 		$('.login').eq(Num).show()
	 	})
	 	$('.black').click(function(){
	 		$('.login-box').hide()
	 		$('.login-box').removeClass('bounceInDown')
	 		$('.black').hide()
	 	})
	 })
 })
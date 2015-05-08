$(window).scroll(function() {
if ($(this).scrollTop() > 15){  
    $('#home').addClass("sticky");
  }
  else{
    $('#home').removeClass("sticky");
  }
});

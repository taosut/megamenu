$(document).ready(function() {
    
	
	flash();
		    function flash(){
			$("#skin1").animate({opacity:0},0).animate({opacity:1},1000).delay(1000).animate({opacity:0},500);
			$("#hero1").animate({opacity:0},0).delay(2500).animate({opacity:1},1000).delay(1000).animate({opacity:0},500);
			$("#hero2").animate({opacity:0},0).delay(5000).animate({opacity:1},1000).delay(1000).animate({opacity:0},500);
			$("#skin2").animate({opacity:0},0).delay(7500).animate({opacity:1},1000).delay(1000).animate({opacity:0},500,flash)
			;}
   
   flash1();
		    function flash1(){
			$("#Askin1").animate({opacity:0},0).animate({opacity:1},500).delay(1000).animate({opacity:0},500);
			$("#Ahero1").animate({opacity:0},0).delay(2000).animate({opacity:1},500).delay(1000).animate({opacity:0},500);
			$("#Ahero2").animate({opacity:0},0).delay(4000).animate({opacity:1},500).delay(1000).animate({opacity:0},500);
			$("#Askin2").animate({opacity:0},0).delay(6000).animate({opacity:1},500).delay(1000).animate({opacity:0},500,flash1)
			;}
			
  treasure();
            function treasure(){		
			$(".treasure_in").animate({opacity:0},0).delay(3000).animate({opacity:1},500).delay(3000).animate({opacity:0},500,treasure)
			;}
});
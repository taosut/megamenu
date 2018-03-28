<?php
//echo phpinfo();
for ($i=1; $i<3; $i++){
	try {
			if($i == 1) throw new Exception("Haha");
			echo $i;
	} catch (Exception $e){
		
	}	
}


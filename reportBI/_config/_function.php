<?php
class _function{

	function mysqlSlashes($str)
	{
		$res_str = preg_replace('/(<script.*>)(.*)(<\/script>)/imxsU',"",$str);
		$res_str = (get_magic_quotes_gpc()) ? stripslashes($res_str) : $res_str;
		return $res_str;
	}
	//新增
	function insertDB($insertInfo='',$insertField=''){
		$insertDB = $insertInfo['DB_Name'];
		while(list($field,$value) = each($insertField))
		{
			if(!empty($field))
			{
				$in_col .= "`".$field.'`,';
				$in_values .= "? ,";
			}
		}
		
		$in_col = substr($in_col,0,-1);
		$in_values = substr($in_values,0,-1);
		$results = "insert into {$insertDB} ($in_col) values ($in_values)";
		
		return $results; 
	}
		
	function updateDB($updateInfo='',$updateField='',$updateClause='',$accumulation=false){
		$updateDB = $updateInfo['DB_Name'];
		
		$update_str = "";
		while(list($field,$value) = each($updateField))
		{
			if($accumulation)
			{
				$update_str .= "`".$field."`=?,";
			}
			else
			{
				$update_str .= "`".$field."`=?,";
			}
		}
		$clause_str = "";
		while(list($field,$value) = each($updateClause))
		{
			$clause_str .="`".$field."`=? and ";
		}
		
		$update_str = substr($update_str,0,-1);
		$clause_str = substr($clause_str,0,-5);
		$uSQL = "update {$updateDB} set $update_str";
		$uSQL .= (!empty($clause_str)) ? " where {$clause_str}" : "";
		$results = $uSQL;
		return $results; 
	}
	


	function keephtml($string){
	  $res = $string;
	  $res = str_replace("&lt;","<",$res);
	  $res = str_replace("&gt;",">",$res);
	  $res = str_replace("&quot;",'"',$res);
	  $res = str_replace("&amp;",'&',$res);
	  return $res;
	}

}		
?>
<?php
// database using PDO
$GLOBALS['debug'] = false;



class project_control{
//****************************** porject_control class *********

	//strips slashes
	function remove_arg_slashes($where_args){
		foreach($where_args as &$where_arg){
			if( is_string($where_arg) ){
				$where_arg = stripslashes($where_arg);
			}
		}
		return $where_args;
	}
	
	function post_add($Rs_add,$table_name,$log=true){
		$_function = new _function();
		$table_name = array("DB_Name"=>$table_name);
		@$insertResults = $_function->insertDB($table_name,$Rs_add);
		$values =array_values( $Rs_add );
		if ($GLOBALS['debug']){  project_control::_print_error(  $insertResults."  :  ".implode(" ,",$values )."<br>" );}
		try {
			$stmt = $GLOBALS['my_conn']->prepare($insertResults);
			$stmt->execute($values);
			return true;
		}
		catch (PDOException $e) {
			if ($GLOBALS['debug']) {   project_control::_print_error( $e->getMessage()."<br>" ); }
			return false;
		}
	}
	
	function post_getlastID(){
		try {
			$id = $GLOBALS['my_conn']->lastInsertId();
			return $id;
		}
		catch (PDOException $e) {
			if ($GLOBALS['debug']) {   project_control::_print_error( $e->getMessage()."<br>" ); }
			return 0;
		}
	}
	
	function post_edit($Rs_edit,$table_name,$updateClause,$log=true){
		$_function = new _function();
		$table_name = array("DB_Name"=>$table_name);

		$updateResults = $_function->updateDB($table_name,$Rs_edit,$updateClause);
		$values = array_merge( array_values( $Rs_edit), array_values($updateClause));
		if ($GLOBALS['debug']){  project_control::_print_error(  $updateResults."  |---->  ".implode(" ,",$values )."<br>");}
		try {
			$stmt = $GLOBALS['my_conn']->prepare($updateResults);
			$stmt->execute($values);
			return true;
		}
		catch (PDOException $e) {
			if ($GLOBALS['debug']) {   project_control::_print_error( $e->getMessage()."<br>" ); }
			return false;
		}
	}
	
	function post_del($table_name,$id,$s_id="id"){
		$sql = sprintf("delete from `".$table_name."` where `".$s_id."` = ?");
		if ($GLOBALS['debug']){project_control::_print_error( $sql."  |---->  ".$id."<br>");}
		try {
			$stmt = $GLOBALS['my_conn']->prepare($sql);
			$stmt->execute(array($id));
			return true;
		}
		catch (PDOException $e) {
			if ($GLOBALS['debug']) {   project_control::_print_error( $e->getMessage()."<br>" ); }
			return false;
		}
	}
	
	function get_counts($table_name,$s_where='', $where_args = array() ,$ids="0"){
		$where_args = self::remove_arg_slashes($where_args);

		$sql = sprintf("select count(%s) from %s %s ",$ids,$table_name,$s_where);
		try {
			$stmt = $GLOBALS['my_conn']->prepare($sql);
			//echo $sql;
			$stmt->execute($where_args);
			//var_dump($where_args);
		}
		catch (PDOException $e) {
			if ($GLOBALS['debug']) {   project_control::_print_error( $e->getMessage()."<br>" ); }
			return false;
		}
		if ($GLOBALS['debug']){project_control::_print_error( $sql."  |---->  ".implode(" ,",$where_args )."<br>");}

		@$Rs_count = $stmt->fetch(PDO::FETCH_BOTH);
		$total = $Rs_count[0];
		return $total;
	}
	
	function get_allrows($table_name,$s_where='', $where_args = array(),$ids='*'){
		$where_args = self::remove_arg_slashes($where_args);

		$sql = sprintf("select %s from %s  %s",$ids,$table_name,$s_where);
    //var_dump($sql,$where_args);
		try {
			$stmt = $GLOBALS['my_conn']->prepare($sql);
			$stmt->execute( $where_args );
		}
		catch (PDOException $e) {
			if ($GLOBALS['debug']) {   project_control::_print_error( $e->getMessage()."<br>" ); }
			return $e;
		}
		if ($GLOBALS['debug']){project_control::_print_error(  $sql."  |---->  ".implode(" ,",$where_args )."<br>");}

		$Rs_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $Rs_rows;
	}

	
	function get_allrows_sql($sql){
		$sql = self::remove_arg_slashes($sql);

		$sql = sprintf("%s",$sql);
		try {
			$stmt = $GLOBALS['my_conn']->prepare($sql);
			$stmt->execute( $where_args );
		}
		catch (PDOException $e) {
			if ($GLOBALS['debug']) {   project_control::_print_error( $e->getMessage()."<br>" ); }
			return false;
		}
		if ($GLOBALS['debug']){project_control::_print_error(  $sql."  |---->  ".implode(" ,",$where_args )."<br>");}

		$Rs_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $Rs_rows;
	}
	
	function get_array($table_name,$s_where='', $where_args = array() ,$ids='*'){
		$where_args = self::remove_arg_slashes($where_args);

		$sql = sprintf("select %s from %s  %s limit 1",$ids,$table_name,$s_where);
		try{
			$stmt = $GLOBALS['my_conn']->prepare($sql);
			$stmt->execute( $where_args );
		}
		catch (PDOException $e) {
			if ($GLOBALS['debug']) {   project_control::_print_error( $e->getMessage()."<br>" ); }
			return false;
		}
		if ($GLOBALS['debug']){project_control::_print_error(  $sql."  |---->  ".implode(" ,",$where_args )."<br>");}

		$Rs_rows =  $stmt->fetch(PDO::FETCH_ASSOC);
		return $Rs_rows;
	}
	
	function get_onerow($ids,$table_name,$s_where='',$where_args= array() ){
		$where_args = self::remove_arg_slashes($where_args);

		$sql = sprintf("select %s from %s  %s limit 1",$ids,$table_name,$s_where);
		try{
			$stmt = $GLOBALS['my_conn']->prepare($sql);
			$stmt->execute( $where_args );
		}
		catch (PDOException $e) {
			if ($GLOBALS['debug']) {   project_control::_print_error( $e->getMessage()."<br>" ); }
			return false;
		}
		if ($GLOBALS['debug']){project_control::_print_error(  $sql."  |---->  ".implode(" ,",$where_args )."<br>");}

		$Rs_rows =  $stmt->fetch(PDO::FETCH_BOTH);
		return $Rs_rows[0];
	}
	
	function get_lastInsertId() {
		try{
			$last_id = $GLOBALS['my_conn']->lastInsertId();
		}catch(PDOException $e){
			if ($GLOBALS['debug']) {   project_control::_print_error( $e->getMessage()."<br>" ); }
			return false;
		}
		return $last_id;
	}
	
	function set_db($dbase){
		$GLOBALS['my_conn']->exec('USE '.$dbase);
	}
	
	function create_index($table, $index_name, $index_field){
		$GLOBALS['my_conn']->exec('CREATE INDEX '.$index_name .' ON ' . $table . '(' . $index_field . ') USING BTREE');
	}

	
	function show_powerful($menu,$kind,$powerID,$m_id){
		if($menu == "main"){
			return project_control::get_counts(
				'account_power a, menu_list b',
				sprintf('where a.manage_id  = ? and a.power = b.id and a.%s = 1  and b.menu_id = ?' , $kind ) ,
				array($m_id,$powerID) ,
				"*" );
		}else{
			return project_control::get_counts(
				'account_power a, menu_list b',
				sprintf('where a.manage_id  = ? and a.power = b.id and a.%s = 1  and b.id = ?' , $kind ) ,
				array($m_id,$powerID) ,
				"*" );
		}
	}
	
	function get_smenu($ac_base_str){
		$row = project_control::get_array("menu_list", "WHERE `url_link`=  ?  ", array(  "index.php?ac=".$ac_base_str."_list"));
		return $row['id'];
	}

	//Js Alert
	function alert_error($msg,$back,$link='',$print_meta=false){
		$Result = '';
		if ($print_meta) {
			$Result = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		}
		if($back){
			$Result .= "<script language=javascript>alert('".$msg."');history.back(1);</script>";
		}else{
			$Result .= "<script language=javascript>alert('".$msg."');window.location.href='".$link."';</script>";
		}
		return $Result;
	}
	//Js Alert
	function alert_msg($msg,$back,$link=''){
		if($back){
			$Result = "<script language=javascript>alert('".$msg."');history.back(1);</script>";
		}else{
			$Result = "<script language=javascript>alert('".$msg."');window.location.href='".$link."';</script>";
		}
		return $Result;
	}
	//Js
	function trans_link($link){
		$Result = "<script language=javascript>window.location.href='".$link."'</script>";
		return $Result;
	}
	//tb_remove
	function tb_remove_msg($msg,$link=''){
		if($link == ""){
			$Result = "<script language=javascript>alert('".$msg."');self.parent.tb_remove();</script>";
		}else{
			$Result = "<script language=javascript>alert('".$msg."');self.parent.location.href='".$link."';self.parent.tb_remove();</script>";
		}
		return $Result;
	}
	//Js reload
	function page_reload(){
		$Result = "<script language=javascript>location.reload();</script>";
		return $Result;
	}

	
	function rmdir_folder($file) {
	  @chmod($file,0777);
	  if (is_dir($file)) {
		$handle = opendir($file);
		while(false !== ($filename = readdir($handle))) {
		  if ($filename != "." && $filename != "..") rmdir_folder($file."/".$filename);
		}
		closedir($handle);
		@rmdir($file);
	  } else {
		@unlink($file);
	  }
	}
	
	function delFolder($dir) {
		$files = glob( $dir . '*', GLOB_MARK );
		foreach( $files as $file ){
			if( substr( $file, -1 ) == '/' )
				project_control::delFolder( $file );
			else
				unlink( $file );
		}
		if (is_dir($dir)) rmdir( $dir );
	}
	
	function chk_file_exists($path){
		return file_exists($path);
	}
	
	function file_write($path,$Result){
		$fp = fopen($path, "w");
		fwrite($fp,$Result);
		fclose($fp);
	}
	


	function  browsertype($browser)
	{
		if(ereg("MSIE([0-9].[0-9]{1,2})",$_SERVER["HTTP_USER_AGENT"],$log_version))
		{
			  $browserver   =   $log_version[1];
			  $browsertype   =   "Internet   Explorer";
		}
		elseif(ereg("Opera([0-9].[0-9]{1,2})",$_SERVER["HTTP_USER_AGENT"],$log_version))
		{
			$browserver  =  $log_version[1];
			$browsertype =  "OPERA";
		}
		elseif(ereg("Mozilla/([0-9].[0-9]{1,2})",$_SERVER["HTTP_USER_AGENT"],$log_version))
		{
			$browserver = $log_version[1];
			$browsertype = "MOZILLA";
		}
		else
		{
			$browserver = 0;
			$browsertype  = "未知";
		}
		return   $browsertype   =   "$browsertype   $browserver";

	}

	

}

?>
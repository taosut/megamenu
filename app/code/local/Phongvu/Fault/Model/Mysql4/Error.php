<?php
/**
 * @author hoang.pt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Model_Mysql4_Error extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_buffer = array();
    protected $_bufferCnt = 0;
    protected $_bufferMax = 500;
    protected $_dates = array();
    
    protected $_skipPatterns = array();
    
    protected $_errors = array();
    
    const TYPE_LOG    = 1;
    const TYPE_REPORT = 2;
    const TYPE_CRON   = 3;
    
    
    const PATH_LAST_RUN   = 'pvfault/errors/last_run';
    
    public function _construct()
    {    
        $this->_init('pvfault/error', 'log_id');
    }
    
    public function clear()
    {    
        $this->_getWriteAdapter()
            ->raw_query('TRUNCATE TABLE `' . $this->getMainTable() . '`');
        
        // clear log files
        $types = array(0, 1);
        foreach ($types as $flag){
            $file = $this->_getLogFilename($flag);
            if ($file){
                file_put_contents($file, ''); 
            }           
        }
        
        // clear reports
        $path = $this->_getReportPath();
        $handle = opendir($path);
        if ($handle){
            while (false !== ($fileName = readdir($handle))) {
                if (is_numeric($fileName)){
                    $fileName = $path . DS . $fileName;
                    @unlink($fileName);
                }
            }
            closedir($handle);             
        }
        
        //clear crons
        $this->_getWriteAdapter()
            ->raw_query('DELETE FROM `' . $this->getTable('cron/schedule') . '` WHERE `status` = "error"');
        
    }
    
    public function getLastRun()
    {
        $time = Mage::getStoreConfig(self::PATH_LAST_RUN);
        if (!$time){ // first run
            $time = time() - 3600*24*2; // last 2 days, as log files can be too big
        }
            
        return $time;
    }
    
    public function setLastRun($time=null)
    {
        if (!$time){
            
            
            $this->_dates[time()] = date("Y-m-d H:i:s");
            krsort($this->_dates);
            
            $dates = array_keys($this->_dates);
            
            $time = $dates[0];
        }
        
        Mage::getModel('core/config_data')
            ->load(self::PATH_LAST_RUN, 'path')    
            ->setScope('default')
            ->setPath(self::PATH_LAST_RUN)
            ->setValue($time)
            ->save(); 
        
        Mage::getConfig()->cleanCache();
    }      
    
    public function collect($lastRun)
    {  
        $lastRun = $lastRun ? $lastRun : $this->getLastRun();
        
        $this->_skipPatterns = array();
        $pattrns = Mage::getStoreConfig('pvfault/error/skip_pattern');
        if ($pattrns){
            $pattrns = explode(',', $pattrns);
            foreach ($pattrns as $value)
                $this->_skipPatterns[] = trim($value);
        }
        
        //get log errors  
        $types = array(0, 1);
        foreach ($types as $flag){
            $file = $this->_getLogFilename($flag);
            if (!file_exists($file)) {
                $this->_errors[] = Mage::helper('pvfault')
                    ->__('Can not open a log file %s, please make sure logging is enabled in Admin > Configuration > Developer > Log Settings', $file);
                continue;
            }
            if (1048576 < filesize($file)) {
                $this->_errors[] = Mage::helper('pvfault')
                    ->__('The %s file is too big, so parsing of it makes no sense, because it can include non valid information. We advise clear this file and collect valid information', $file);
                continue;
            }
            $this->_parseLog($file, $lastRun);
        }
        
        $this->_parseCronJobs($lastRun);
        
        $this->_parseReportDir($lastRun);
        
        $this->setLastRun();
        
        return $this->_errors ? $this->_errors : true;
    }    
    
    protected function _parseLog($fileName, $lastRun)
    {
        if (filemtime($fileName) < $lastRun){
            return false;
        }
        
        $lastMsg        = '';
        $skipDuplicates = false;

        $handle = fopen($fileName, 'r');
        if (!$handle)
            return false;
            
        $maxLines = 1000;    
        while (!feof($handle) && $maxLines) {
            $vars = explode(' ', fgets($handle, 4096), 4);
            
            if (count($vars) < 4){
                continue;
            }
            
            // the function can't parse minutes, skip for now
            $time = strtotime($vars[0]);
            if ($time < $lastRun){ // skip already parsed records
                continue;
            }

            if ($skipDuplicates && $lastMsg == $vars[3]){
                continue;
            }
            
            $this->_addToBuffer(date("Y-m-d H:i:s", $time), self::TYPE_LOG, $vars[3]);
            $lastMsg = $vars[3];
            
            --$maxLines;
        }
        fclose($handle);
        
        $this->_flushBuffer();        

        return true;
    }
    
    protected function _parseCronJobs($lastRun)
    {
        $db = $this->_getReadAdapter();
        $sql = 'SELECT job_code, messages, executed_at'
             . ' FROM ' . $this->getTable('cron/schedule') 
             . ' WHERE `status` = "error" AND executed_at > "'.date("Y-m-d H:i:s", $lastRun).'"'
        ;

        $rows = $db->fetchAll($sql);
        foreach ($rows as $row){
            $err = $row['job_code'] . ': ' . $row['messages'];
            $this->_addToBuffer($row['executed_at'], self::TYPE_CRON, $err);
        }
        $this->_flushBuffer();  // todo - move directly from one table to another
               
        return true;
    }
    
    protected function _parseReportDir($lastRun)
    {
        $path = $this->_getReportPath();
        if (!file_exists($path))
            return false;
        
        $handle = opendir($path);
        if (!$handle)
            return false;

        while (false !== ($fileName = readdir($handle))) {
            if (!is_numeric($fileName)){
                continue;
            }
            
            $fileName = $path . DS . $fileName;
            $time     = filemtime($fileName);
            if ($time < $lastRun){
                continue;
            }
            $err = file_get_contents($fileName);
            $this->_addToBuffer(date("Y-m-d H:i:s", $time), self::TYPE_REPORT, $err);
                
        }
        closedir($handle);
        
        $this->_flushBuffer();
        
        return true;
    }
    
    protected function _getLogFilename($type)
    {
        $file = $type ? 'exception_file' : 'file';
        $file = Mage::getStoreConfig('dev/log/' . $file);
        $file = empty($file) ? 'system.log' : $file;
        
        $file = Mage::getBaseDir('var') . DS . 'log' . DS . $file;
        return $file;
    }
    
    protected function _getReportPath()
    {
        return Mage::getBaseDir('var') . DS . 'report';
    }    
    
    protected function _addToBuffer($date, $type, $err)
    {
        $err = trim($err);

        if (!empty($err) && $err!="") {
            foreach($this->_skipPatterns as $value){
                if (!empty($value) && false !== strpos($err, $value))
                    return false;
            }

            $this->_buffer[] = array($date, $type, $err);
            $this->_dates[strtotime($date)] = $date;
            ++$this->_bufferCnt;
            if ($this->_bufferCnt > $this->_bufferMax){
                $this->_flushBuffer();
            }
        }

        return true;
    }
    
    protected function _flushBuffer()
    {
        if (!$this->_bufferCnt)
            return true;
            
        $db = $this->_getWriteAdapter(); 
        $sql = 'INSERT INTO `' . $this->getMainTable() . '` (`date`, `type`, `error`) VALUES';
        foreach ($this->_buffer as $line){
            $sql .= $db->quoteInto('(?),', $line);
        }
        $sql = substr($sql, 0, -1);
        //echo $sql; exit;

        $db->raw_query($sql);
        
        $this->_buffer    = array();
        $this->_bufferCnt = 0;
        
        return true;
    }
       
}

<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 4/20/2017
 * Time: 9:53 AM
 */
class Ved_AdminApi_Controller_BaseController extends Mage_Adminhtml_Controller_Action
{
    const BREAK_MODULO       = 5;        /* The lock will only be broken one of of this many tries to prevent multiple processes breaking the same lock */
    const FAIL_AFTER         = 15;       /* Try to break lock for at most this many seconds */
    const DETECT_ZOMBIES     = 10;       /* Try to detect zombies every this many seconds */
    const MAX_LIFETIME       = 2592000;  /* Redis backend limit */
    const ACCESS_TOKEN_PREFIX     = 'auth_';
    const LOG_FILE           = 'redis_authen.log';

    /* Bots get shorter session lifetimes */
    const BOT_REGEX          = '/^alexa|^blitz\.io|bot|^browsermob|crawl|^curl|^facebookexternalhit|feed|google web preview|^ia_archiver|^java|jakarta|^load impact|^magespeedtest|monitor|nagios|^pinterest|postrank|slurp|spider|uptime|yandex/i';

    const XML_PATH_HOST            = 'global/redis_session/host';
    const XML_PATH_PORT            = 'global/redis_session/port';
    const XML_PATH_PASS            = 'global/redis_session/password';
    const XML_PATH_TIMEOUT         = 'global/redis_session/timeout';
    const XML_PATH_PERSISTENT      = 'global/redis_session/persistent';
    const XML_PATH_DB              = 'global/redis_session/db';
    const XML_PATH_COMPRESSION_THRESHOLD = 'global/redis_session/compression_threshold';
    const XML_PATH_COMPRESSION_LIB = 'global/redis_session/compression_lib';
    const XML_PATH_LOG_LEVEL       = 'global/redis_session/log_level';
    const XML_PATH_MAX_CONCURRENCY = 'global/redis_session/max_concurrency';
    const XML_PATH_BREAK_AFTER     = 'global/redis_session/break_after_%s';
    const XML_PATH_BOT_LIFETIME    = 'global/redis_session/bot_lifetime';

    const DEFAULT_TIMEOUT               = 2.5;
    const DEFAULT_COMPRESSION_THRESHOLD = 2048;
    const DEFAULT_COMPRESSION_LIB       = 'gzip';
    const DEFAULT_LOG_LEVEL             = 1;
    const DEFAULT_MAX_CONCURRENCY       = 6;        /* The maximum number of concurrent lock waiters per session */
    const DEFAULT_BREAK_AFTER           = 30;       /* Try to break the lock after this many seconds */
    const DEFAULT_BOT_LIFETIME          = 7200;     /* The session lifetime for bots - shorter to prevent bots from wasting backend storage */

    /** @var bool */
    protected $_useRedis = true;

    /** @var Credis_Client */
    protected $_redis;

    /** @var int */
    protected $_dbNum;

    protected $_compressionThreshold;
    protected $_compressionLib;
    protected $_logLevel;
    protected $_maxConcurrency;
    protected $_breakAfter;
    protected $_botLifetime;
    protected $_isBot = FALSE;
    protected $_hasLock;
    protected $_sessionWritten; // avoid infinite loops
    protected $_timeStart; // re-usable for timing instrumentation

    static public $failedLockAttempts = 0; // for debug or informational purposes

    /**
     * Check DB connection
     *
     * @return bool
     */
    public function hasConnection()
    {
        if( ! $this->_useRedis) return false;

        try {

            $this->_timeStart = microtime(true);
            $host = (string)   (Mage::getConfig()->getNode(self::XML_PATH_HOST) ?: '127.0.0.1');
            $port = (int)      (Mage::getConfig()->getNode(self::XML_PATH_PORT) ?: '6379');
            $pass = (string)   (Mage::getConfig()->getNode(self::XML_PATH_PASS) ?: '');
            $timeout = (float) (Mage::getConfig()->getNode(self::XML_PATH_TIMEOUT) ?: self::DEFAULT_TIMEOUT);
            $persistent = (string) (Mage::getConfig()->getNode(self::XML_PATH_PERSISTENT) ?: '');
            $this->_dbNum = 1;
            $this->_compressionThreshold = (int) (Mage::getConfig()->getNode(self::XML_PATH_COMPRESSION_THRESHOLD) ?: self::DEFAULT_COMPRESSION_THRESHOLD);
            $this->_compressionLib = (string) (Mage::getConfig()->getNode(self::XML_PATH_COMPRESSION_LIB) ?: self::DEFAULT_COMPRESSION_LIB);
            $this->_logLevel = (int) (Mage::getConfig()->getNode(self::XML_PATH_LOG_LEVEL) ?: self::DEFAULT_LOG_LEVEL);
            $this->_maxConcurrency = (int) (Mage::getConfig()->getNode(self::XML_PATH_MAX_CONCURRENCY) ?: self::DEFAULT_MAX_CONCURRENCY);
            $this->_breakAfter = (int) (Mage::getConfig()->getNode(sprintf(self::XML_PATH_BREAK_AFTER, session_name())) ?: self::DEFAULT_BREAK_AFTER);
            $this->_botLifetime = (int) (Mage::getConfig()->getNode(self::XML_PATH_BOT_LIFETIME) ?: self::DEFAULT_BOT_LIFETIME);
            if ($this->_botLifetime) {
                $userAgent = empty($_SERVER['HTTP_USER_AGENT']) ? FALSE : $_SERVER['HTTP_USER_AGENT'];
                $this->_isBot = ! $userAgent || preg_match(self::BOT_REGEX, $userAgent);
            }
            $this->_redis = new Credis_Client($host, $port, $timeout, $persistent);
            if (!empty($pass)) {
                $this->_redis->auth($pass) or Zend_Cache::throwException('Unable to authenticate with the redis server.');
            }
            $this->_redis->setCloseOnDestruct(FALSE);  // Destructor order cannot be predicted
            $this->_useRedis = TRUE;

            $this->_redis->connect();

            return TRUE;
        }
        catch (Exception $e) {
            Mage::logException($e);
            $this->_redis = NULL;

            // Fall-back to MySQL handler. If this fails, the file handler will be used.
            $this->_useRedis = FALSE;
            return false;
        }
    }


    public function preDispatch ()
    {
        Mage::app ()->getRequest ()->setParam ( 'forwarded', true );
        $accessToken = $this->getRequest()->getParam('access_token');
        if($this->hasConnection()){
            $userInfo = $this->read($accessToken);
            if($userInfo){
                return parent::preDispatch ();
            }
        }
        $data = $this->check($accessToken);
        if($data){
            return parent::preDispatch ();
        }else{
            header("HTTP/1.1 401 Unauthorized");
            die();
        }
    }



    /**
     * Fetch session data
     *
     * @param string $tokenId
     * @return bool/string
     */
    private function read($tokenId)
    {
        if ( ! $this->_useRedis) return false;

        $tokenId = self::ACCESS_TOKEN_PREFIX.$tokenId;

        if($this->_dbNum) $this->_redis->select($this->_dbNum);

        $userInfo = $this->_redis->get($tokenId);

        if($userInfo)
            return $userInfo;
        else
            return false;
    }

    /**
     * Validate token in SSO module
     *
     * @param $tokenId
     * @return bool
     */
    private function check($tokenId){
        $ssoUrl = (string)Mage::getConfig()->getNode('global/sso_url') . 'validate_access_token';

        $client = new Varien_Http_Client($ssoUrl);
        $client->setMethod(Varien_Http_Client::GET);
        $client->setParameterGet('accessToken', $tokenId);
        try{
            $response = $client->request();
            if($response->getStatus() != 200){
                return false;
            }

            $this->write($tokenId, $response->getBody());
            return true;
        }catch(Exception $e){
            var_dump($e->getMessage());
            return false;
        }

        return false;
    }

    /**
     * Write access token to Redis
     *
     * @param string $tokenId
     * @param string $userInfo
     * @return boolean
     */
    public function write($tokenId, $userInfo)
    {
        if ( ! $this->_useRedis) return false;

        // Do not overwrite the session if it is locked by another pid
        try {
            $tokenId = self::ACCESS_TOKEN_PREFIX.$tokenId;
            $this->_redis->pipeline()
                ->select($this->_dbNum)
                ->set($tokenId, $userInfo)
                ->exec();
        }
        catch(Exception $e) {
            if (class_exists('Mage', false)) {
                Mage::logException($e);
            } else {
                error_log("$e");
            }
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Destroy session
     *
     * @param string $tokenId
     * @return boolean
     */
    public function destroy($tokenId)
    {
        if ( ! $this->_useRedis) return false;

        $this->_redis->pipeline();
        if($this->_dbNum) $this->_redis->select($this->_dbNum);
        $this->_redis->del(self::ACCESS_TOKEN_PREFIX.$tokenId);
        $this->_redis->exec();
        return TRUE;
    }

    /**
     * Overridden to prevent calling getLifeTime at shutdown
     *
     * @return bool
     */
    public function close()
    {
        if ( ! $this->_useRedis) return false;

        if ($this->_redis) $this->_redis->close();
        return TRUE;
    }
}
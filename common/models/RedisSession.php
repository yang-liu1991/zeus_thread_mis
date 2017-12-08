<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-11-22 11:37:31
 */

namespace common\models;

use Yii;
use yii\base\Object;

class RedisSession extends Object implements \SessionHandlerInterface
{

	protected $redis;

	public $host;

	public $port;

	public $timeOut;

	public $keyPrefix = 'zeus_thread_session_';

	public $log_level = 2;

	public $log_file = '/tmp/tmp.log';

	const DEBUG = 0;

	const INFO = 1;

	const ERROR = 2;

	public function init(){
		$this->redis = new \Redis();
		$this->connect();
	}

	public function connect(){
		try{
			$conn = $this->redis->connect($this->host, $this->port, $this->timeOut);
			if($conn){
				$this->log(self::INFO, 'connect redis success , host:' . $this->host . ', port:' . $this->port . ', timeOut:' . $this->timeOut);
			} else {
				$this->log(self::ERROR, __LINE__ . " connect redis faild host:" . $this->host . ', port:' . $this->port . ', timeOut:' . $this->timeOut);
			}
		 } catch (\Exception $e){
			 $this->log(self::ERROR, __LINE__ . ' ' . $e->getMessage());
		}
	}
	public function checkRedis(){
		try{
			if(!$this->redis->ping()){
				$this->connect();
			}
		} catch (\Exception $e){
			$this->log(self::ERROR, __LINE__ . " test redis faild " . $e->getMessage());
		}
		return $this->redis;
	}

	public function open($savePath, $sessionName){
		try{
			$redis = $this->checkRedis();
			return !!$this->redis->ping();
		} catch (\Exception $e){
			$this->log(self::ERROR, __LINE__ . " open redis faild" . $e->getMessage());
		}
		return false;
	}

	public function close(){
		return true;
	}

	public function read($sessionId){
		try{
			$redis = $this->checkRedis();
			if($redis->exists($this->keyPrefix . $sessionId)){
				$this->log(self::DEBUG, "redis read {$this->keyPrefix}{$sessionId}");
				return $redis->get($this->keyPrefix . $sessionId);
			}
		} catch (\Exception $e){
			$this->log(self::ERROR, __LINE__ . " redis read {$this->keyPrefix}{$sessionId} faild" . $e->getMessage());
		}
		return false;
	}

	public function write($sessionId, $data, $time = 3600){
		try{
			$redis = $this->checkRedis();
			$this->log(self::DEBUG, "redis write {$this->keyPrefix}{$sessionId}");
			return !!$redis->set($this->keyPrefix . $sessionId, $data, $time);	
		} catch (\Exception $e){
			$this->log(self::ERROR, __LINE__ . " redis set {$this->keyPrefix}{$sessionId} faild" . $e->getMessage());
		}
		return false;

	}

	public function destroy($sessionId){
		try{
			$redis = $this->checkRedis();
			$this->log(self::DEBUG, "redis delete {$this->keyPrefix}{$sessionId}");
			return $redis->delete($this->keyPrefix . $sessionId);
		} catch (\Exception $e){
			$this->log(self::ERROR, __LINE__ . " redis delete {$this->keyPrefix}{$sessionId} faild" . $e->getMessage());
		}
		return false;
	}

	public function gc($lifetime){
		return true;
	}

	public function __get($name){
		return $this->$name;
	}

	public function __set($name, $value){
		$this->$name = $value;
	}

	public function log($level, $message){
		if($level >= $this->log_level){
			file_put_contents($this->log_file, date('Y-m-d H:i:s') . ' - ' . $message . "\r\n", FILE_APPEND);
		}
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :

<?php

/**
 * @file
 * Contains \Drupal\qyweixin\Entity\CorpBase.
 */

namespace Drupal\qyweixin;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;

class CorpBase {

	/* Const for user gender */
	const USER_GENDER_MALE=1;
	const USER_GENDER_FEMALE=2;
	
	/* Const for user status */
	const USER_STATUS_ENABLED=1;
	const USER_STATUS_DISABLED=0;
	
	/* Const for user subscribing status */
	const USER_SUBSCRIBE_STATUS_ALL=0;
	const USER_SUBSCRIBE_STATUS_SUBSCRIBED=1;
	const USER_SUBSCRIBE_STATUS_FREEZED=2;
	const USER_SUBSCRIBE_STATUS_UNSUBSCRIBED=4;
	
	/* Const for top level department id */
	const TOP_LEVEL_DEPARTMENT_ID=1;

	/**
	* The corpid of this corp.
	*
	* @var string
	*/
	protected static $corpid;

	/**
	* The corpsecret of this corp.
	*
	* @var string
	*/
	protected static $corpsecret;

	/**
	* The access_token of this qyweixin account.
	*
	* @var string
	*/
	protected static $access_token='';

	/**
	* The weight of this role in administrative listings.
	*
	* @var int
	*/
	protected static $access_token_expires_in=0;

	/**
	 * Retrieve access_token to be used in other functions
	 *
	 * @return string
	 *   The access_token return by Tencent qyweixin interface
	 */
	public static function getAccessToken() {
		if(empty(self::$corpid)) self::$corpid=\Drupal::config('qyweixin.general')->get('corpid');
		if(empty(self::$corpsecret)) self::$corpsecret=\Drupal::config('qyweixin.general')->get('corpsecret');
		if(empty(self::$access_token)) self::$access_token=\Drupal::state()->get('qyweixin.access_token');
		if(empty(self::$access_token_expires_in)) self::$access_token_expires_in=\Drupal::state()->get('qyweixin.access_token.expires_in');
		if(empty(self::$access_token) || empty(self::$access_token_expired_in) || self::$access_token_expires_in-5>=time()) {
			self::$corpid=\Drupal::config('qyweixin.general')->get('corpid');
			self::$corpsecret=\Drupal::config('qyweixin.general')->get('corpsecret');
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=%s&corpsecret=%s', self::$corpid, self::$corpsecret);
			try {
				$data = (string) \Drupal::httpClient()->get($url)->getBody();
				$r=json_decode($data);
				if(empty($r))
					throw new \Exception(json_last_error_msg(), json_last_error());
				if(!empty($r->errcode))
					throw new \Exception($r->errmsg, $response->errcode);
				\Drupal::state()->set('qyweixin.access_token', $r->access_token);
				\Drupal::state()->set('qyweixin.access_token.expires_in', $r->expires_in+time());
				self::$access_token=$r->access_token;
				self::$access_token_expires_in=$r->expires_in+time();
			} catch (\Exception $e) {
				throw new \Exception($e->getMessage(), $e->getCode());
			}
		}
		return self::$access_token;
	}

	/**
	 * Wrapper of QyWeixin's user/authsucc function.
	 *
	 * @param string $userid
	 *   The userid to set as auth succeeded.
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 */
	public static function userAuthSucc($userid) {
		try {
			$access_token=self::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/user/authsucc?access_token=%s&userid=%s', $access_token, $userid);
			$data = (string) \Drupal::httpClient()->get($url)->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \Exception(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \Exception($response->errmsg, $response->errcode);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Wrapper of QyWeixin's user/create function.
	 *
	 * @param stdClass $user
	 *   The user to push to qyweixin's contact book, must complies user object specification in qyweixin.
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 */
	public static function userCreate($user) {
		try {
			$access_token=self::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/user/create?access_token=%s', $access_token);
			$u=new \stdClass();
			$u->userid=$user->userid;
			$u->name=$user->name;
			$u->email=$user->email;
			$u->department=$user->department;
			$data = (string) \Drupal::httpClient()->post($url, ['body'=>json_encode($u, JSON_UNESCAPED_UNICODE)])->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \Exception(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \Exception($response->errmsg, $response->errcode);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Wrapper of QyWeixin's user/update function.
	 *
	 * @param stdClass $user
	 *   The user to push to qyweixin's contact book, must complies user object specification in qyweixin.
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 */
	public static function userUpdate($user) {
		try {
			$access_token=self::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/user/update?access_token=%s', $access_token);
			$u=new \stdClass();
			$u->userid=$user->userid;
			$u->name=$user->name;
			$u->email=$user->email;
			$u->department=$user->department;
			$u->enable=$user->enable;
			$data = (string) \Drupal::httpClient()->post($url, ['body'=>json_encode($u, JSON_UNESCAPED_UNICODE)])->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \Exception(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \Exception($response->errmsg, $response->errcode);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Wrapper of QyWeixin's user/delete function.
	 *
	 * @param string or array of strings $userid
	 *   The user or users to push to qyweixin's contact book.
	 *   if $user is a array, then massive deletion(user/batchdeleted) might be called, and each
	 *   of the element should be a plain uid.
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 */
	public static function userDelete($userid) {
		try {
			$access_token=self::getAccessToken();
			if(is_array($userid)) {
				$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/user/batchdelete?access_token=%s', $access_token);
				$u=new \stdClass();
				foreach($userid as $user)
				$u->useridlist[]=(string)$user;
				$data = (string) \Drupal::httpClient()->post($url, ['body'=>json_encode($u, JSON_UNESCAPED_UNICODE)])->getBody();
			} else {
				$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/user/delete?access_token=%s&userid=%s', $access_token, $userid);
				$data = (string) \Drupal::httpClient()->get($url)->getBody();
			}
			$response=json_decode($data);
			if(empty($response)) throw new \Exception(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \Exception($response->errmsg, $response->errcode);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Wrapper of QyWeixin's user/get function.
	 *
	 * @param string $userid
	 *   The userid to query from qyweixin's contact book.
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 * @return stdClass
	 *   The user object retured by Tencent qyweixin interface.
	 */
	public static function userGet($userid) {
		try {
			$response=new \stdClass();
			$access_token=self::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=%s&userid=%s', $access_token, $userid);
			$data = (string) \Drupal::httpClient()->get($url)->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \Exception(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \Exception($response->errmsg, $response->errcode);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		} finally {
			return $response;
		}
	}

	/**
	 * Wrapper of QyWeixin's user/getuserinfo function.
	 *
	 * @param string code
	 *   The code returned by Tecent server, which could be used to retreive the userid.
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 * @return stdClass
	 *   The user object retured by Tencent qyweixin interface.
	 */
	public static function userGetUserInfo($code) {
		try {
			$response=new \stdClass();
			$access_token=self::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=%s&code=%s', $access_token, $code);
			$data = (string) \Drupal::httpClient()->get($url)->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \Exception(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \Exception($response->errmsg, $response->errcode);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		} finally {
			return $response;
		}
	}

	/**
	 * Wrapper of QyWeixin's user/simplelist function.
	 *
	 * @param int $departmentid
	 *   The id of department you want to fetch.
	 * @param boolean $fetch_child
	 *   1 means you want the members of sub departments should be fetched also.
	 * @param int $status
	 *   Following numbers could be used:
	 *   0: Allo
	 *   1 means you want the members of sub departments should be fetched also.
	 *
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 * @return array of stdClass
	 *   The user objects retured by Tencent qyweixin interface.
	 */
	public static function userSimpleList($departmentid = 1, $fetch_child = FALSE, $status = USER_SUBSCRIBE_STATUS_UNSUBSCRIBED) {
		try {
			$userlist=[];
			$access_token=self::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=%s&department_id=%s&fetch_child=%s&status=%s',
			$access_token, $departmentid, (int)$fetch_child, (int)$status);
			$data = (string) \Drupal::httpClient()->get($url)->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \Exception(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \Exception($response->errmsg, $response->errcode);
			$userlist=$response->userlist;
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		} finally {
			return $userlist;
		}
	}

	/**
	 * Wrapper of QyWeixin's user/list function.
	 *
	 * @param int $departmentid
	 *   The id of department you want to fetch.
	 * @param boolean $fetch_child
	 *   1 means you want the members of sub departments should be fetched also.
	 * @param int $status
	 *   Following numbers could be used:
	 *   0: Allo
	 *   1 means you want the members of sub departments should be fetched also.
	 *
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 * @return array of stdClass
	 *   The user objects retured by Tencent qyweixin interface.
	 */
	public static function userList($departmentid = 1, $fetch_child = FALSE, $status = USER_SUBSCRIBE_STATUS_UNSUBSCRIBED) {
		try {
			$userlist=[];
			$access_token=self::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=%s&department_id=%s&fetch_child=%s&status=%s',
			$access_token, $departmentid, (int)$fetch_child, $status);
			$data = (string) \Drupal::httpClient()->get($url)->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \Exception(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \Exception($response->errmsg, $response->errcode);
			$userlist=$response->userlist;
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		} finally {
			return $userlist;
		}
	}

	/**
	 * Wrapper of QyWeixin's department/create function.
	 *
	 * @param stdClass $department
	 *   The department object you want to push qyweixin's database.
	 *
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 */
	public static function departmentCreate($department) {
		try {
			$access_token=self::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/department/create?access_token=%s', $access_token);
			$d=new \stdClass();
			$d->id=(int)$department->id;
			$d->name=$department->name;
			$d->order=$department->order;
			$d->parentid=$department->parentid;
			$data = (string) \Drupal::httpClient()->post($url, ['body'=>json_encode($d, JSON_UNESCAPED_UNICODE)])->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \Exception(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \Exception($response->errmsg, $response->errcode);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Wrapper of QyWeixin's department/update function.
	 *
	 * @param stdClass $department
	 *   The department object you want to push qyweixin's database.
	 *
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 */
	public static function departmentUpdate($department) {
		try {
			$access_token=self::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/department/update?access_token=%s', $access_token);
			$d=new \stdClass();
			$d->id=(int)$department->id;
			$d->name=$department->name;
			$d->order=$department->order;
			$d->parentid=$department->parentid;
			$data = (string) \Drupal::httpClient()->post($url, ['body'=>json_encode($d, JSON_UNESCAPED_UNICODE)])->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \Exception(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \Exception($response->errmsg, $response->errcode);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Wrapper of QyWeixin's department/delete function.
	 *
	 * @param stdClass $department
	 *   The department object you want to push qyweixin's database.
	 *
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 */
	public static function departmentDelete($departmentid) {
		try {
			$access_token=self::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/department/delete?access_token=%s&id=%s', $access_token, (int)$departmentid);
			$data = (string) \Drupal::httpClient()->get($url)->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \Exception(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \Exception($response->errmsg, $response->errcode);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * Wrapper of QyWeixin's agent/list function.
	 *
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 * @return array of stdClass
	 *   The agentlist objects retured by Tencent qyweixin interface.
	 */
	public static function agentList() {
		$ret=[];
		try {
			$access_token=self::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/agent/list?access_token=%s', $access_token);
			$data = (string) \Drupal::httpClient()->get($url)->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \Exception(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \Exception($response->errmsg, $response->errcode);
			$ret=$response->agentlist;
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		} finally {
			return $ret;
		}
	}
	
	public static function verifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr, $token, $encodingAesKey) {
		
	}
	
}

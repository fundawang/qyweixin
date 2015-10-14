<?php

/**
 * @file
 * Contains \Drupal\qyweixin\Entity\AgentBase.
 */

namespace Drupal\qyweixin\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\qyweixin\AgentInterface;

/**
 * Defines the qyweixin agent entity class.
 *
 * @ConfigEntityType(
 *   id = "qywexin_agent",
 *   label = @Translation("Agent"),
 *   admin_permission = "administer permissions",
 *   entity_keys = {
 *     "id" = "id",
 *     "agentid" = "agentid"
 *   },
 *   config_export = {
 *     "id",
 *   }
 * )
 */
Class AgentBase extends ConfigEntityBase implements AgentInterface {
	protected $agentid=NULL;
	protected $id=NULL;
	protected $token=NULL;
	protected $EncodingAesKey=NULL;
	
	public function getAgentId() {
		return $this->agentid;
	}
	public function setAgentId($agentId='') {
		$this->agentid=$agentId;
		return $this;
	}
	
	public function getToken() {
		return $this->token;
	}
	
	public function setToken($token='') {
		$this->token=$token;
		return $this;
	}
	
	public function getEncodingAesKey() {
		return $this->EncodingAesKey;
	}
	
	public function setEncodingAesKey($key='') {
		$this->EncodingAesKey=$key;
		return $this;
	}
	
	/**
	 * Retreive agent settings from qyweixin server
	 *
	 * 	Usage:
	 *	$a=AgentBase::Create(['agentid'=>14, 'id'=>'foo']);
	 *	var_dump($a->agentGet());
	 *
	 * @return stdClass or FALSE
	 *   The object returned by Tencent server or FALSE if error occured.
	 */
	public function agentGet() {
		if(empty($this->agentid)) return FALSE;
		$ret=FALSE;
		try {
			$access_token=\Drupal\qyweixin\Corp::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/agent/get?access_token=%s&agentid=%s', $access_token, $this->agentid);
			$data = (string) \Drupal::httpClient()->get($url)->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \Exception(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \Exception($response->errmsg, $response->errcode);
			$ret=$response;
			unset($ret->errcode);
			unset($ret->errmsg);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		} finally {
			return $ret;
		}
	}
	
	/**
	 * Retreive agent settings from qyweixin server
	 *
	 * @param stdClass agent
	 *    This agent object as what qyweixin requires, except that the agentid which will filled automatically.
	 *
	 * @return this
	 */
	public function agentSet($agent) {
		if(empty($this->agentid)) return FALSE;
		try {
			$access_token=\Drupal\qyweixin\Corp::getAccessToken();
			$agent->agentid=$this->agentid;
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/agent/set?access_token=%s', $access_token);
			$data = (string) \Drupal::httpClient()->post($url, ['body'=>json_encode($agent, JSON_UNESCAPED_UNICODE)])->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \Exception(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \Exception($response->errmsg, $response->errcode);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		} finally {
			return $this;
		}
	}
	
	/**
	 * Send private message to specific user
	 *
	 * 	Usage:
	 *	$a=AgentBase::create(['agentid'=>14, 'id'=>'foo']);
	 *	$body=new \stdClass();
	 *	$body->touser='1';
	 *	$body->msgtype='text';
	 *	$body->text=new \stdClass();
	 *	$body->text->content='Hello world';
	 *	$a->messageSend($body);
	 *
	 * @param stdClass body
	 *   body as what qyweixin requires, except that the agentid which will filled automatically.
	 * @return this
	 */
	public function messageSend($body) {
		try {
			$access_token=\Drupal\qyweixin\Corp::getAccessToken();
			$body->agentid=$this->getAgentId();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=%s', $access_token);
			$data = (string) \Drupal::httpClient()->post($url, ['body'=>json_encode($body, JSON_UNESCAPED_UNICODE)])->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \Exception(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \Exception($response->errmsg, $response->errcode);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		} finally {
			return $this;
		}
	}
}

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
 * Usage:
	$a=new AgentBase(['agentid'=>14, 'id'=>'foo']);
	$body=new \stdClass();
	$body->touser='1';
	$body->msgtype='text';
	$body->text=new \stdClass();
	$body->text->content='Hello world';
	$a->messageSend($body);
 *	
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
	
	public function getAgent() {
		return $this->get();
	}
	
	public function agentGet() {
		return $this->get();
	}
	
	public function setAgent($agent) {}
	public function agentSet($agent) {}
	
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
		}
	}
}

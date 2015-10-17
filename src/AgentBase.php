<?php

/**
 * @file
 * Contains \Drupal\qyweixn\AgentBase.
 */

namespace Drupal\qyweixin;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for QiyeWeixin Agent.
 *
 * @see plugin_api
 */
class AgentBase extends PluginBase implements AgentInterface {

	/**
	 * The qyweixin agent ID.
	 *
	 * @var string
	 */
	protected $agentId;
	protected $pluginId;
	protected $token;
	protected $encodingAesKey;
	
	/**
	* {@inheritdoc}
	*/
	public function getPluginId() {
		return $this->pluginId;
	}

	public function getConfiguration() {
		return array(
			'id' => $this->getPluginId(),
			'agentId' => $this->agentId,
			'token' => $this->token,
			'encodingAesKey' => $this->encodingAesKey,
			'data' => $this->configuration,
		);
	}
	
	/**
	* {@inheritdoc}
	*/
	public function __construct(array $configuration, $plugin_id, $plugin_definition) {
		parent::__construct($configuration, $plugin_id, $plugin_definition);

		$this->setConfiguration($configuration);
		$this->logger = $logger;
	}

	/**
	* {@inheritdoc}
	*/
	public function setConfiguration(array $configuration) {
		$configuration += array(
			'data' => array(),
			'agentId' => '',
			'token' => '',
			'encodingAesKey' => '',
		);
		
		$this->configuration = $configuration['data'] + $this->defaultConfiguration();
		$this->agentId = $configuration['agentId'];
		$this->token = $configuration['token'];
		$this->encodingAesKey = $configuration['encodingAesKey'];
		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function defaultConfiguration() {
		return array();
	}

	/**
	* {@inheritdoc}
	*/
	public function calculateDependencies() {
		return array();
	}

	/**
	 * Retreive agent settings from qyweixin server
	 *
	 * @return stdClass or FALSE
	 *   The object returned by Tencent server or FALSE if error occured.
	 */
	public function agentGet() {
		if(empty($this->agentId)) return FALSE;
		$ret=FALSE;
		try {
			$access_token=\Drupal\qyweixin\Corp::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/agent/get?access_token=%s&agentid=%s', $access_token, $this->agentId);
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
		if(empty($this->agentId)) return FALSE;
		try {
			$access_token=\Drupal\qyweixin\Corp::getAccessToken();
			$agent->agentid=$this->agentId;
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
	 * @param stdClass body
	 *   body as what qyweixin requires, except that the agentid which will be filled automatically.
	 * @return this
	 */
	public function messageSend($body) {
		try {
			if(empty($body) || !is_object($body)) return $this;
			$access_token=\Drupal\qyweixin\Corp::getAccessToken();
			$body->agentid=$this->agentId;
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

	/**
	 * Get list of materials of this agent
	 *
	 * @param const type
	 *
	 * @param int offset
	 *
	 * @param int count
	 *
	 * @return stdClass
	 */
	public function materialBatchGet($type=MATERIAL_TYPE_IMAGE, $offset=0, $count=10) {
		try {
			$ret=new \stdClass();
			$access_token=\Drupal\qyweixin\Corp::getAccessToken();
			$body->agentid=$this->agentId;
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/material/batchget?access_token=%s', $access_token);
			$data = (string) \Drupal::httpClient()->post($url, ['body'=>json_encode($body, JSON_UNESCAPED_UNICODE)])->getBody();
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

}

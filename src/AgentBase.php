<?php

/**
 * @file
 * Contains \Drupal\qyweixn\AgentBase.
 */

namespace Drupal\qyweixin;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\qyweixin\CorpBase;
use Drupal\qyweixin\MessageBase;

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
			'data' => $this->configuration
		);
	}
	
	/**
	* {@inheritdoc}
	*/
	public function __construct(array $configuration, $plugin_id, $plugin_definition) {
		$configuration+=['agentId'=>\Drupal::config('qyweixin.general')->get('plugin.'.$plugin_id.'.agentid')];
		$configuration+=array(
			'token'=>\Drupal::config('qyweixin.general')->get('agent.'.$configuration['agentId'].'.token'),
			'encodingAesKey'=>\Drupal::config('qyweixin.general')->get('agent.'.$configuration['agentId'].'.encodingaeskey')
		);
		parent::__construct($configuration, $plugin_id, $plugin_definition);
		$configuration['data']=$configuration;
		$this->setConfiguration($configuration);
	}

	/**
	* {@inheritdoc}
	*/
	public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
		return new static(
			$configuration,
			$plugin_id,
			$plugin_definition
		);
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
			$access_token=CorpBase::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/agent/get?access_token=%s&agentid=%s', $access_token, $this->agentId);
			$data = (string) \Drupal::httpClient()->get($url)->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \RuntimeException(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \InvalidArgumentException($response->errmsg, $response->errcode);
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
			$access_token=CorpBase::getAccessToken();
			$agent->agentid=$this->agentId;
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/agent/set?access_token=%s', $access_token);
			$data = (string) \Drupal::httpClient()->post($url, ['body'=>json_encode($agent, JSON_UNESCAPED_UNICODE)])->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \RuntimeException(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \InvalidArgumentException($response->errmsg, $response->errcode);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		} finally {
			return $this;
		}
	}
	
	/**
	 * Send private message to specific user
	 *
	 * @param stdClass/MessageBase message
	 *   body as what qyweixin requires, except that the agentid which will be filled automatically.
	 * @return this
	 */
	public function messageSend($message) {
		if(empty($message) || !is_object($message)) return $this;
		if($message instanceof MessageBase) {
			$body=new \stdClass();
			$body->touser=$message->getToUser();
			$body->msgtype=$message->getMsgType();
			switch($body->msgtype) {
				case MessageBase::MESSAGE_TYPE_TEXT:
					$body->text=new \stdClass();
					$body->text->content=$message->getContent();
			}
		} else
			$body=$message;

		try {
			$access_token=CorpBase::getAccessToken();
			$body->agentid=$this->agentId;
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=%s', $access_token);
			$data = (string) \Drupal::httpClient()->post($url, ['body'=>json_encode($body, JSON_UNESCAPED_UNICODE)])->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \RuntimeException(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \InvalidArgumentException($response->errmsg, $response->errcode);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		} finally {
			return $this;
		}
	}
	
	/**
	 * Upload permanent material that could be used in this agent
	 *
	 * @param FileInterface file
	 *   The file you want to upload
	 *
	 * @param cost type
	 *   The filetype you wan to upload
	 *
	 * @return
	 *   The media_id returned by Tencent QiyeWeixin Interface
	 */
	public function materialAddMaterial(FileInterface $file, $type=MATERIAL_TYPE_FILE) {
		if(empty($this->agentId)) return FALSE;
		$media_id='';
		try {
			$access_token=CorpBase::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/material/add_material?access_token=%s&type=%s&agentid=%s', $access_token, $type, $this->agentId);
			$handle=fopen($file->getFileUri(), 'r');
			$body=[[
				'name' => 'media',
				'filename' => $file->getFilename(),
				'filelength' => $file->getSize(),
				'content-type' => $file->getMimeType(),
				'contents' => $handle,
			]];
			$data = (string) \Drupal::httpClient()->post($url, ['multipart'=>$body])->getBody();
			fclose($handle);
			$response=json_decode($data);
			if(empty($response)) throw new \RuntimeException(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \InvalidArgumentException($response->errmsg, $response->errcode);
			$media_id=$response->media_id;
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		} finally {
			return $media_id;
		}
	}

	/**
	 * Retreive permanent material that could be used in this agent
	 *
	 * @param string media_id
	 *   The media_id you want to get
	 *
	 * @return GuzzleHttp\Psr7\ResponseInterface
	 *   The received response object for the caller to save.
	 */
	public function materialGet($media_id) {
		if(empty($this->agentId)) return FALSE;
		$response=new \GuzzleHttp\Psr7\Response;
		try {
			$access_token=CorpBase::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/material/get?access_token=%s&media_id=%s&agentid=%s', $access_token, $media_id, $this->agentId);
			$response = \Drupal::httpClient()->get($url);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		} finally {
			return $response;
		}
	}

	/**
	 * Delete permanent material that could be used in this agent
	 *
	 * @param string media_id
	 *   The media_id you want to delete
	 *
	 * @return this
	 */
	public function materialDel($media_id) {
		if(empty($this->agentId)) return FALSE;
		try {
			$access_token=CorpBase::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/material/del?access_token=%s&media_id=%s&agentid=%s', $access_token, $media_id, $this->agentId);
			$data = (string) \Drupal::httpClient()->get($url)->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \RuntimeException(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \InvalidArgumentException($response->errmsg, $response->errcode);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		} finally {
			return $this;
		}
	}

	/**
	 * Get Count of permanent materials that could be used in this agent
	 *
	 * @param const type
	 *   The type of materials you want to count
	 *
	 * @return int or array
	 *   The number of the type you specified, or array of all types
	 */
	public function materialGetCount($type='') {
		if(empty($this->agentId)) return FALSE;
		$ret='';
		try {
			$access_token=CorpBase::getAccessToken();
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/material/get_count?access_token=%s&agentid=%s', $access_token, $this->agentId);
			$data = (string) \Drupal::httpClient()->get($url)->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \RuntimeException(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \InvalidArgumentException($response->errmsg, $response->errcode);
			$ret=(array)$response;
			unset($ret['errcode']);
			unset($ret['errmsg']);
			if(!empty($type)) {
				$ret=$ret[$type.'_count'];
			}
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		} finally {
			return $ret;
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
	public function materialBatchGet($type=MATERIAL_TYPE_FILE, $offset=0, $count=10) {
		if(empty($this->agentId)) return FALSE;
		$ret='';
		try {
			$ret=new \stdClass();
			$access_token=CorpBase::getAccessToken();
			$body=new \stdClass();
			$body->type=$type;
			$body->agentid=$this->agentId;
			$body->offset=$offset;
			$body->count=$count;
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/material/batchget?access_token=%s', $access_token);
			$data = (string) \Drupal::httpClient()->post($url, ['body'=>json_encode($body, JSON_UNESCAPED_UNICODE)])->getBody();
			$response=json_decode($data);
			if(empty($response)) throw new \RuntimeException(json_last_error_msg(), json_last_error());
			if($response->errcode) throw new \InvalidArgumentException($response->errmsg, $response->errcode);
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

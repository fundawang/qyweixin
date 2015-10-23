<?php

/**
 * @file
 * Contains \Drupal\qyweixn\MessageBase.
 */

namespace Drupal\qyweixin;
use Drupal\qyweixin\AgentInterface;

/**
 * Provides a message base class for QiyeWeixin Agent.
 *
 */
class MessageBase {

	const MESSAGE_TYPE_TEXT='text';
	const MESSAGE_TYPE_MPNEWS='mpnews';
	const MESSAGE_TYPE_IMAGE='image';
	const MESSAGE_TYPE_VOICE='voice';
	const MESSAGE_TYPE_VIDEO='video';
	const MESSAGE_TYPE_FILE='file';
	
	protected $toUser;
	protected $msgType;
	protected $content;
	
	public function setMsgType($type=MESSAGE_TYPE_TEXT) {
		$this->msgType=$type;
		return $this;
	}
	
	public function setContent($content='') {
		$this->content=$content;
		return $this;
	}
	
	public function setToUser($user) {
		if(is_array($user)) $this->toUser=implode('|',$user);
		else $this->toUser=$user;
		return $this;
	}
	
	public function getMsgType() {
		return $this->msgType;
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function getToUser() {
		return $this->toUser;
	}
	
}

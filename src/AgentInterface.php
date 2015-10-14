<?php

/**
 * @file
 * Contains \Drupal\qyweixin\AgentInterface.
 */

namespace Drupal\qyweixin;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an app of qyweixin.
 *
 */
interface AgentInterface extends ConfigEntityInterface {
	public function getAgentId();
	public function setAgentId($agentId='');
	public function getToken();
	public function setToken($token='');
	public function getEncodingAesKey();
	public function setEncodingAesKey($key='');
	
	public function getAgent();
	public function agentGet();
	
	public function setAgent($agent);
	public function agentSet($agent);
	
	public function messageSend($body);
}

<?php

/**
 * @file
 * Contains \Drupal\qyweixin\AgentInterface.
 */

namespace Drupal\qyweixin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines the interface for QiyeWeixin Agents.
 *
 * @see plugin_api
 */
interface AgentInterface extends PluginInspectionInterface, ConfigurablePluginInterface {
	const MATERIAL_TYPE_MPNEWS='mpnews';
	const MATERIAL_TYPE_IMAGE='image';
	const MATERIAL_TYPE_VOICE='voice';
	const MATERIAL_TYPE_VIDEO='video';
	const MATERIAL_TYPE_FILE='file';
	
	public function agentGet();
	public function agentSet($agent);
	
	public function messageSend($body);
	
	public function materialAddMaterial(FileInterface $file, $type=MATERIAL_TYPE_FILE);
	public function materialGet($media_id);
	public function materialDel($media_id);
	public function materialGetCount($type='');
	public function materialBatchGet($type=MATERIAL_TYPE_FILE, $offset=0, $count=10);
}

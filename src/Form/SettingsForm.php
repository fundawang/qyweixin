<?php

/**
* @file
* Contains \Drupal\qyweixin\Form\SettingsForm.
*/

namespace Drupal\qyweixin\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\qyweixin\CorpBase;
use Drupal\qyweixin\AgentBase;

/**
* Configure settings of Qiye weixin 
*/
class SettingsForm extends ConfigFormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'qyweixin_settings';
	}
	
	/**
	* {@inheritdoc}
	*/
	protected function getEditableConfigNames() {
		return ['qyweixin.general'];
	}
	
	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {
		$config=$this->config('qyweixin.general');
		$form['corpid']=array(
			'#type' => 'textfield',
			'#title' => $this->t('CorpID for Qiye Weixin'),
			'#size' => 25,
			'#default_value' => empty($config->get('corpid'))?'':$config->get('corpid'),
			'#required' => TRUE,
		);
		$form['corpsecret']=array(
			'#type' => 'textfield',
			'#title' => $this->t('Corp Secret for Qiye Weixin'),
			'#size' => 90,
			'#description' => $this->t('Please note that, we only support one manage group per site now.'),
			'#default_value' => empty($config->get('corpsecret'))?'':$config->get('corpsecret'),
			'#required' => TRUE,
		);
		$form['users']=array(
			'#type' => 'details',
			'#title' => $this->t('User interchange between drupal and qyweixin'),
			'#open' => $config->get('autosync'),
			'#tree' => TRUE,
		);
		$form['users']['autosync']=array(
			'#type' => 'checkbox',
			'#title' => $this->t('Auto sync users to qyweixin contact book'),
			'#description' => $this->t('Automatically add/remove/modify users in qyweixin, according to local user database. Roles will become departments.'),
			'#default_value' => $config->get('autosync'),
		);
		
		$plugins=\Drupal::service('plugin.manager.qyweixin.agent')->getDefinitions();
		foreach($plugins as $plugin => $settings) {
			$plugins_select[$plugin]=sprintf('%s (%s)', $plugin, $settings['class']);
		}
		if(empty($plugins))
			drupal_set_message(t('Cannot perform settings for agents, as there is no plugins available.'), 'warning');

		$agents=\Drupal::state()->get('qyweixin.agents');

		$form['agents']=['#tree'=>TRUE, '#access'=>!empty($plugins)];
		foreach($agents as $agent) {
			$form['agents'][$agent->agentid]=array(
				'#type'=>'details',
				'#open' => $config->get('agent.'.$agent->agentid.'.enabled'),
				'#title' => $this->t('Settings for agent @agentname (agentid: @agentid)',['@agentname'=>$agent->name, '@agentid'=>$agent->agentid]),
				'#tree'=>TRUE,
			);
			$form['agents'][$agent->agentid]['enabled']=array(
				'#type'=>'checkbox',
				'#title' => t('This agent can be proceeded by @sitename', ['@sitename'=>\Drupal::config('system.site')->get('name')]),
				'#default_value' => $config->get('agent.'.$agent->agentid.'.enabled'),
			);
			$form['agents'][$agent->agentid]['entryclass']=array(
				'#type'=>'select',
				'#title' => t('Entry class'),
				'#options' => $plugins_select,
				'#default_value' => $config->get('agent.'.$agent->agentid.'.entryclass'),
				'#description' => t('Please note that only 1-to-1 mapping between entryclass and agentid is supported. So you cannot set ONE entry class for multiple agents.'),
				'#states' => array(
					'visible' => array(
						':input[name="agents['.$agent->agentid.'][enabled]"]' => array('checked' => TRUE),
					),
					'required' => array(
						':input[name="agents['.$agent->agentid.'][enabled]"]' => array('checked' => TRUE),
					),
				),
			);
			$form['agents'][$agent->agentid]['token']=array(
				'#type'=>'textfield',
				'#title' => t('Callback token'),
				'#default_value' => $config->get('agent.'.$agent->agentid.'.token'),
				'#states' => array(
					'visible' => array(
						':input[name="agents['.$agent->agentid.'][enabled]"]' => array('checked' => TRUE),
					),
					'required' => array(
						':input[name="agents['.$agent->agentid.'][enabled]"]' => array('checked' => TRUE),
					),
				),
			);
			$form['agents'][$agent->agentid]['encodingaeskey']=array(
				'#type'=>'textfield',
				'#title' => t('Callback EncodingAESKey'),
				'#default_value' => $config->get('agent.'.$agent->agentid.'.encodingaeskey'),
				'#states' => array(
					'visible' => array(
						':input[name="agents['.$agent->agentid.'][enabled]"]' => array('checked' => TRUE),
					),
					'required' => array(
						':input[name="agents['.$agent->agentid.'][enabled]"]' => array('checked' => TRUE),
					),
				),
			);
		}
		return parent::buildForm($form, $form_state);
	}

	/**
	* {@inheritdoc}
	*/
	public function validateForm(array &$form, FormStateInterface $form_state) {
		$client = \Drupal::httpClient();
		// Only do test if the settings are changed
		if($this->config('qyweixin.general')->get('corpid')!=$form_state->getValue('corpid') || $this->config('qyweixin.general')->get('corpsecret')!=$form_state->getValue('corpsecret')) {
			$url=sprintf('https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=%s&corpsecret=%s', $form_state->getValue('corpid'), $form_state->getValue('corpsecret'));
			try {
				$data = (string) \Drupal::httpClient()->get($url)->getBody();
				$r=json_decode($data);
				if(empty($r))
					throw new \RuntimeException(json_last_error_msg());
				if(!empty($r->errcode))
					throw new \InvalidArgumentException(sprintf('%s: %s', $r->errcode, $r->errmsg));
				if(empty($r->access_token))
					throw new \RuntimeException($this->t('Acess Token fetch error.'));
			} catch (\Exception $e) {
				$form_state->setErrorByName('corpid', $e->getMessage());
				$form_state->setErrorByName('corpsecret');
			}
		}
		
		// Only 1-to-1 entryclass vs agentis is allowed now.
		foreach($form_state->getValue('agents') as $agentid=>$settings) {
			foreach($form_state->getValue('agents') as $id=>$sets) {
				if($agentid<>$id && $settings['enabled'] && $sets['enabled'] && $settings['entryclass']==$sets['entryclass']) {
					$form_state->setErrorByName('agents]['.$agentid.'][entryclass', t('Entry class overlapped.'));
					$form_state->setErrorByName('agents]['.$id.'][entryclass');
				}
			}
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		// First save all the settings in conf
		$this->config('qyweixin.general')
			->set('corpid', $form_state->getValue('corpid'))
			->set('corpsecret', $form_state->getValue('corpsecret'))
			->set('autosync', $form_state->getValue(['users','autosync']))
			->save();
			
		// Then save the mapping of agentid and entryclass
		foreach($form_state->getValue('agents') as $agentid=>$settings) {
			$this->config('qyweixin.general')
				->clear('agent.'.$agentid)
				->set('agent.'.$agentid.'.enabled', $settings['enabled'])->save();
			if($settings['enabled'])
				$this->config('qyweixin.general')
					->set('agent.'.$agentid.'.entryclass', $settings['entryclass'])
					->set('agent.'.$agentid.'.token', $settings['encodingaeskey'])
					->set('agent.'.$agentid.'.encodingaeskey', $settings['encodingaeskey'])
					->clear('plugin.'.$settings['entryclass'])
					->set('plugin.'.$settings['entryclass'].'.agentid', $agentid)
				->save();
		}
		
		// Store agentLists in state for later process
		\Drupal::state()->set('qyweixin.agents', CorpBase::agentList());
		
		parent::submitForm($form, $form_state);
	}
}
?>

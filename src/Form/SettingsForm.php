<?php

/**
* @file
* Contains \Drupal\qyweixin\Form\SettingsForm.
*/

namespace Drupal\qyweixin\Form;

use Drupal\Core\Form\ConfigFormBase;
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
		$default_setting=$this->config('qyweixin.general');
		$form['corpid']=array(
			'#type' => 'textfield',
			'#title' => $this->t('CorpID for Qiye Weixin'),
			'#default_value' => empty($default_setting->get('corpid'))?'':$default_setting->get('corpid'),
			'#required' => TRUE,
		);
		$form['corpsecret']=array(
			'#type' => 'textfield',
			'#title' => $this->t('Corp Secret for Qiye Weixin'),
			'#description' => $this->t('Please note that, we only support one manage group per site now.'),
			'#default_value' => empty($default_setting->get('corpsecret'))?'':$default_setting->get('corpsecret'),
			'#required' => TRUE,
		);
		$form['users']=array(
			'#type' => 'fieldset',
			'#title' => $this->t('User interchange between drupal and qyweixin'),
			'#tree' => TRUE,
		);
		$form['users']['autosync']=array(
			'#type' => 'checkbox',
			'#title' => $this->t('Auto sync users to qyweixin contact book'),
			'#description' => $this->t('Automatically add/remove/modify users in qyweixin, according to local user database. Roles will become departments.'),
			'#default_value' => empty($default_setting->get('autosync'))?'':$default_setting->get('autosync'),
		);
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
					throw new \Exception(json_last_error_msg());
				if(!empty($r->errcode))
					throw new \Exception(sprintf('%s: %s', $r->errcode, $r->errmsg));
				if(empty($r->access_token))
					throw new \Exception($this->t('Acess Token fetch error.'));
			} catch (\Exception $e) {
				$form_state->setErrorByName('corpid', $e->getMessage());
				$form_state->setErrorByName('corpsecret');
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
	}
}
?>

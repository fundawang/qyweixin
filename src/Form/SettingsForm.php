<?php

/**
* @file
* Contains \Drupal\qyweixin\Form\SettingsForm.
*/

namespace Drupal\qyweixin\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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
		return ['qyweixin'];
	}
	
	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {
		$default_setting=$this->config('qyweixin');
		$form['corpid']=array(
			'#type' => 'textfield',
			'#title' => $this->t('CorpID for Qiye Weixin'),
			'#default_value' => empty($default_setting->get('corpid'))?'':$default_setting->get('corpid'),
			'#required' => TRUE,
		);
		return parent::buildForm($form, $form_state);
	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		$this->config('qyweixin')
			->set('corpid', $form_state->getValue('corpid'))
			->save();
		parent::submitForm($form, $form_state);
	}
}
?>

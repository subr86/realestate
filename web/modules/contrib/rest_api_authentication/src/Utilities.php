<?php
/**
 * @package    miniOrange
 * @subpackage Plugins
 * @license    GNU/GPLv3
 * @copyright  Copyright 2015 miniOrange. All Rights Reserved.
 *
 *
 * This file is part of miniOrange Drupal REST API module.
 *
 * miniOrange Drupal REST API modules is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * miniOrange Drupal REST API module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with miniOrange SAML plugin.  If not, see <http://www.gnu.org/licenses/>.
 */


namespace Drupal\rest_api_authentication;
use Drupal\Core\Form\FormStateInterface;
class Utilities {

  /**
   * Shows support block
   */
  public static function AddSupportButton(array &$form, FormStateInterface $form_state)
  {
    $form['markup_idp_attr_header_top_support_btn'] = array(
      '#markup' => '<div id="mosaml-feedback-form" class="mo_rest_api_table_layout_support_btn">',
    );

    $form['miniorange_rest_api_support_side_button'] = array(
      '#type' => 'button',
      '#value' => t('Support'),
      '#attributes' => array('style' => 'font-size: 15px;cursor: pointer;text-align: center;width: 150px;height: 35px;
                background: rgba(43, 141, 65, 0.93);color: #ffffff;border-radius: 3px;transform: rotate(90deg);text-shadow: none;
                position: relative;margin-left: -92px;top: 107px;'),
    );

    $form['markup_rest_api_attr_header_top_support'] = array(
      '#markup' => '<div id="Support_Section" class="mo_saml_table_layout_support_1">',
    );

    $form['markup_support_1'] = array(
      '#markup' => '<h3><b>Feature Request/Contact Us:</b></h3><div>Need any help? We can help you with configuring your module according toy our use case. Just send us a query and we will get back to you right away.</div>',
    );

    $form['rest_api_authentication_support_email_address'] = array(
      '#type' => 'textfield',
      '#attributes' => array('placeholder' => 'Enter your Email'),
      '#default_value' => \Drupal::config('rest_api_authentication.settings')->get('rest_api_authentication_customer_admin_email'),
    );

    $form['rest_api_authentication_support_phone_number'] = array(
      '#type' => 'textfield',
      '#attributes' => array('placeholder' => 'Enter your Phone Number'),
      '#default_value' => \Drupal::config('rest_api_authentication.settings')->get('rest_api_authentication_customer_admin_phone'),
    );

    $form['rest_api_authentication_support_query'] = array(
      '#type' => 'textarea',
      '#clos' => '10',
      '#rows' => '5',
      '#attributes' => array('placeholder' => 'Write your query here'),
    );

    $form['markup_div'] = array(
      '#markup' => '<div>'
    );

    $form['miniorange_oauth_client_support_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit Query'),
      '#submit' => array('::saved_support'),
      '#limit_validation_errors' => array(),
      '#attributes' => array('style' => 'background: #337ab7;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;box-shadow: 0 1px 0 #337ab7;border-color: #337ab7 #337ab7 #337ab7;display:block;float:left;'),
    );

    $form['markup_div_end'] = array(
      '#markup' => '</div>'
    );

    $form['miniorange_oauth_client_support_note'] = array(
      '#markup' => '<br><div><br/>If you want custom features in the module, just drop an email to <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a></div>'
    );

    $form['miniorange_oauth_client_div_end'] = array(
      '#markup' => '</div></div><div hidden id="mosaml-feedback-overlay"></div>'
    );

  }

	public static function generateRandom($length=30) {
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$charactersLength = strlen($characters);
		$randomString = '';

        for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public static function isCurlInstalled() {
		return in_array('curl', get_loaded_extensions());
	}

    /**
     * Sends support query
     */
    public static function send_support_query($email, $phone, $query, $type){
        if(empty($email)||empty($query)){
            \Drupal::messenger()->addMessage(t('The <b><u>Email</u></b> and <b><u>Query</u></b> fields are mandatory.'), 'error');
            return;
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            \Drupal::messenger()->addMessage(t('The email address <b><i>' . $email . '</i></b> is not valid.'), 'error');
            return;
        }
        $support = new MiniorangeApiAuthSupport($email, $phone, $query, $type);
        $support_response = $support->sendSupportQuery();
        if($support_response) {
            \Drupal::messenger()->addMessage(t('Support query successfully sent. We will get back to you shortly.'));
        }
        else {
            \Drupal::messenger()->addMessage(t('Error sending support query'), 'error');
        }
    }

    /**
	 * This function is used to get the timestamp value
	 */
	public static function get_timestamp() {
		$url = 'https://login.xecurify.com/moas/rest/mobile/get-timestamp';
		$ch  = curl_init( $url );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false ); // required for https urls
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_POST, true );
		$content = curl_exec( $ch );
		if ( curl_errno( $ch ) ) {
			echo 'Error in sending curl Request';
			exit ();
		}
		curl_close( $ch );
		if(empty( $content )){
			$currentTimeInMillis = round( microtime( true ) * 1000 );
			$currentTimeInMillis = number_format( $currentTimeInMillis, 0, '', '' );
		}
		return empty( $content ) ? $currentTimeInMillis : $content;
	}
}

<?php

namespace Drupal\rest_api_authentication;
use Drupal\rest_api_authentication\MiniorangeApiAuthConstants;
use Drupal\rest_api_authentication\Utilities;

/**
 * @file
 * This class represents support information for customer.
 */
/**
 * @file
 * Contains miniOrange Support class.
 */
class MiniorangeApiAuthSupport {
  public $email;
  public $phone;
  public $query;
  public $plan;

  public function __construct($email, $phone, $query, $plan = '') {
    $this->email = $email;
    $this->phone = $phone;
    $this->query = $query;
    $this->plan = $plan;

  }

  /**
	 * Send support query.
	 */
    public function sendSupportQuery()
    {
      $modules_info = \Drupal::service('extension.list.module')->getExtensionInfo('rest_api_authentication');
      $modules_version = $modules_info['version'];
        if ($this->plan == 'demo') {
            $url = MiniorangeApiAuthConstants::BASE_URL . '/moas/api/notify/send';
            $ch = curl_init($url);

            $subject = "Demo request for Drupal - ".\Drupal::VERSION." REST API Authentication Free Module ";
            $this->query = 'Demo required for - ' . $this->query;

            $customerKey = \Drupal::config('rest_api_authentication.settings')->get('rest_api_authentication_customer_id');
            $apikey = \Drupal::config('rest_api_authentication.settings')->get('rest_api_authentication_customer_api_key');
            if ($customerKey == '') {
                $customerKey = "16555";
                $apikey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
            }

            $currentTimeInMillis = Utilities::get_timestamp();
            $stringToHash = $customerKey . $currentTimeInMillis . $apikey;
            $hashValue = hash("sha512", $stringToHash);
            $customerKeyHeader = "Customer-Key: " . $customerKey;
            $timestampHeader = "Timestamp: " . $currentTimeInMillis;
            $authorizationHeader = "Authorization: " . $hashValue;

            $content = '<div >Hello, <br><br>Company :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>Phone Number:' . $this->phone . '<br><br>Email:<a href="mailto:' . $this->email . '" target="_blank">' . $this->email . '</a><br><br>Query:[Drupal- '.\Drupal::VERSION.' API Authentication Free | '.$modules_version.' ] ' . $this->query . '</div>';

            $fields = array(
                'customerKey' => $customerKey,
                'sendEmail' => true,
                'email' => array(
                    'customerKey' => $customerKey,
                    'fromEmail' => $this->email,
                    'fromName' => 'miniOrange',
                    'toEmail' => 'drupalsupport@xecurify.com',
                    'toName' => 'drupalsupport@xecurify.com',
                    'subject' => $subject,
                    'content' => $content
                ),
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $customerKeyHeader,
                $timestampHeader, $authorizationHeader));
        } elseif($this->plan == 'trial'){
          $url = MiniorangeApiAuthConstants::BASE_URL . '/moas/api/notify/send';
          $ch = curl_init($url);

          $subject = "Trial request for Drupal - ".\Drupal::VERSION." REST API Authentication Module ";
          $this->query = 'Demo required for: ' . $this->query;

          $customerKey = \Drupal::config('rest_api_authentication.settings')->get('rest_api_authentication_customer_id');
          $apikey = \Drupal::config('rest_api_authentication.settings')->get('rest_api_authentication_customer_api_key');
          if ($customerKey == '') {
            $customerKey = "16555";
            $apikey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
          }

          $currentTimeInMillis = Utilities::get_timestamp();
          $stringToHash = $customerKey . $currentTimeInMillis . $apikey;
          $hashValue = hash("sha512", $stringToHash);
          $customerKeyHeader = "Customer-Key: " . $customerKey;
          $timestampHeader = "Timestamp: " . $currentTimeInMillis;
          $authorizationHeader = "Authorization: " . $hashValue;

          $content = '<div >Hello, <br><br>Company :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>Phone Number:' . $this->phone . '<br><br>Email:<a href="mailto:' . $this->email . '" target="_blank">' . $this->email . '</a><br><br>Query:[Drupal- '.\Drupal::VERSION.' API Authentication Free | '.$modules_version.' ] ' . $this->query . '</div>';

          $fields = array(
            'customerKey' => $customerKey,
            'sendEmail' => true,
            'email' => array(
              'customerKey' => $customerKey,
              'fromEmail' => $this->email,
              'fromName' => 'miniOrange',
              'toEmail' => 'drupalsupport@xecurify.com',
              'toName' => 'drupalsupport@xecurify.com',
              'subject' => $subject,
              'content' => $content
            ),
          );
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $customerKeyHeader,
            $timestampHeader, $authorizationHeader));
        } else {

            $this->query = '[Drupal - '.\Drupal::VERSION.' REST API Authentication Free Module] <br>' . $this->query;
            $fields = array(
                'company' => $_SERVER['SERVER_NAME'],
                'email' => $this->email,
                'phone' => $this->phone,
                'ccEmail' => 'drupalsupport@xecurify.com',
                'query' => $this->query,
            );

            $url = MiniorangeApiAuthConstants::BASE_URL . '/moas/rest/customer/contact-us';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'charset: UTF-8',
                'Authorization: Basic'
            ));
        }

        $field_string = json_encode($fields);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = array(
                '%method' => 'sendSupportQuery',
                '%file' => 'miniorange_oauth_client_support.php',
                '%error' => curl_error($ch),
            );
            \Drupal::logger('rest_api_authentication')->notice($error);
            return FALSE;
        }
        curl_close($ch);
        return TRUE;
    }
}

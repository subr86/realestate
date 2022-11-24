<?php
/**
 * @file
 * Contains miniOrange Customer class.
 */

/**
 * @file
 * This class represents configuration for customer.
 */
namespace Drupal\rest_api_authentication;
use Drupal\rest_api_authentication\Utilities;

class MiniorangeRestAPICustomer {

  public $email;

  public $phone;

  public $customerKey;

  public $transactionId;

  public $password;

  public $otpToken;

  private $defaultCustomerId;

  private $defaultCustomerApiKey;

  /**
   * Constructor.
   */
  public function __construct($email, $password) {
    $this->email = $email;
    $this->password = $password;
    $this->defaultCustomerId = "16555";
    $this->defaultCustomerApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
  }
    function verifyLicense($code)
    {
        $url = MiniorangeApiAuthConstants::BASE_URL . '/moas/api/backupcode/verify';
        $ch = curl_init($url);

        $customerKey = \Drupal::config('rest_api_authentication.settings')->get('rest_api_authentication_customer_id');
        $apiKey = \Drupal::config('rest_api_authentication.settings')->get('rest_api_authentication_customer_api_key');
        global $base_url;

        /* Current time in milliseconds since midnight, January 1, 1970 UTC. */
        $currentTimeInMillis = round(microtime(true) * 1000);

        /* Creating the Hash using SHA-512 algorithm */
        $stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
        $hashValue = hash("sha512", $stringToHash);

        $customerKeyHeader = "Customer-Key: " . $customerKey;
        $timestampHeader = "Timestamp: " . number_format($currentTimeInMillis, 0, '', '');
        $authorizationHeader = "Authorization: " . $hashValue;

        $fields = '';

        $fields = array(
            'code' => $code,
            'customerKey' => $customerKey,
            'additionalFields' => array(
                'field1' => $base_url
            )
        );

        $field_string = json_encode($fields);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // required for https urls

        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            $customerKeyHeader,
            $timestampHeader,
            $authorizationHeader
        ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $content = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Request Error:' . curl_error($ch);
             exit ();
        }

        curl_close($ch);
        return $content;
    }

    /**
   * Check if customer exists.
   */
  public function checkCustomer() {
    if (!Utilities::isCurlInstalled()) {
      return json_encode(array(
        "status" => 'CURL_ERROR',
        "statusMessage" => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.',
      ));
    }

    $url = MiniorangeApiAuthConstants::BASE_URL . '/moas/rest/customer/check-if-exists';
    $ch = curl_init($url);
    $email = $this->email;

    $fields = array(
      'email' => $email,
    );
    $field_string = json_encode($fields);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_ENCODING, "");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json', 'charset: UTF - 8',
      'Authorization: Basic',
    ));
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
    $content = curl_exec($ch);
    if (curl_errno($ch)) {
      $error = array(
        '%method' => 'checkCustomer',
        '%file' => 'customer_setup.php',
        '%error' => curl_error($ch),
      );
        \Drupal::logger('rest_api_authentication')->notice('Error at %method of %file: %error', []);
    }
    curl_close($ch);
    return $content;
  }

    function updateStatus()
    {

      $url = MiniorangeApiAuthConstants::BASE_URL . '/moas/api/backupcode/updatestatus';

      $ch = curl_init($url);
      $customerKey = \Drupal::config('rest_api_authentication.settings')->get('rest_api_authentication_customer_id');
      $apiKey = \Drupal::config('rest_api_authentication.settings')->get('rest_api_authentication_customer_api_key');
      $currentTimeInMillis = round(microtime(true) * 1000);
      $stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
      $hashValue = hash("sha512", $stringToHash);
      $customerKeyHeader = "Customer-Key: " . $customerKey;
      $timestampHeader = "Timestamp: " . number_format($currentTimeInMillis, 0, '', '');
      $authorizationHeader = "Authorization: " . $hashValue;
      $code = \Drupal::config('rest_api_authentication.settings')->get('rest_api_authentication_license_key');
      $fields = array('code' => $code, 'customerKey' => $customerKey);
      $field_string = json_encode($fields);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_ENCODING, "");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_AUTOREFERER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // required for https urls

      curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              "Content-Type: application/json",
              $customerKeyHeader,
              $timestampHeader,
              $authorizationHeader
          )
      );
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
      curl_setopt($ch, CURLOPT_TIMEOUT, 20);
      $content = curl_exec($ch);

      if (curl_errno($ch)) {
          echo 'Request Error:' . curl_error($ch);
          exit ();
      }
      curl_close($ch);
      return $content;
    }

  /**
   * Get Customer Keys.
   */
  public function getCustomerKeys() {
    if (!Utilities::isCurlInstalled()) {
      return json_encode(array(
        "apiKey" => 'CURL_ERROR',
        "token" => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.',
      ));
    }

    $url = MiniorangeApiAuthConstants::BASE_URL . '/moas/rest/customer/key';
    $ch = curl_init($url);
    $email = $this->email;
    $password = $this->password;

    $fields = array(
      'email' => $email,
      'password' => $password,
    );
    $field_string = json_encode($fields);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_ENCODING, "");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'charset: UTF - 8',
      'Authorization: Basic',
    ));
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
    $content = curl_exec($ch);
    if (curl_errno($ch)) {
      $error = array(
        '%method' => 'getCustomerKeys',
        '%file' => 'customer_setup.php',
        '%error' => curl_error($ch),
      );
        \Drupal::logger('rest_api_authentication')->notice('Error at %method of %file: %error', []);
    }
    curl_close($ch);
    return $content;
  }
}
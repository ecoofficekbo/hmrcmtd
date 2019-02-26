<?php

/**
 * Author: James Hill
 *
 * Copyright (C) James Hill jameshilldev83@gmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace EcoMtd;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;

class Hmrc
{
    const RETURN_SUCCESS = 0;
    const RETURN_ERROR = 1;
    const RETURN_AUTH_UPDATE = 2;

    const AUTH_TYPE_NONE = 0;
    const AUTH_TYPE_USER = 1;
    const AUTH_TYPE_SERVER = 2;

    /**
     * Main URL for HMRC live service
     *
     * @var string
     */
    const URL = 'https://api.service.hmrc.gov.uk';

    /**
     * Test URL for HMRC sandbox
     *
     * @var string
     */
    const TESTURL = 'https://test-api.service.hmrc.gov.uk';

    /**
     * Client ID provided by HMRC after demonstration of app with the DRM team at drm.service@hmrc.gsi.gov.uk
     * Can be left as empty string and provided in the $credentials array to the constructor
     *
     * @var string
     */
    const CLIENT_ID = '';

    /**
     * Client Secret provided by HMRC after demonstration of app with the DRM team at drm.service@hmrc.gsi.gov.uk
     * Can be left as empty string and provided in the $credentials array to the constructor
     * 
     * @var string
     */
    const CLIENT_SECRET = '';

    /**
     * Server Token provided by HMRC after demonstration of app with the DRM team at drm.service@hmrc.gsi.gov.uk
     * Can be left as empty string and provided in the $credentials array to the constructor
     *
     * @var string
     */
    const SERVER_TOKEN = '';

    /**
     * Sandbox Client ID provided when registering application with HMRC Developer Hub, found in "Manage credentials"
     * Can be left as empty string and provided in the $credentials array to the constructor
     *
     * @var string
     */
    const TEST_CLIENT_ID = '';

    /**
     * Sandbox Client Secret provided when registering application with HMRC Developer Hub, found in "Manage credentials"
     * Can be left as empty string and provided in the $credentials array to the constructor
     *
     * @var string
     */
    const TEST_CLIENT_SECRET = '';

    /**
     * Sandbox Server Token provided when registering application with HMRC Developer Hub, found in "Manage credentials"
     * Can be left as empty string and provided in the $credentials array to the constructor
     *
     * @var string
     */
    const TEST_SERVER_TOKEN = '';

    /**
     * HMRC Client ID, set by constructor
     *
     * @var string
     */
    private $_clientId = '';

    /**
     * HMRC Client Secret, set by constructor
     *
     * @var string
     */
    private $_clientSecret = '';

    /**
     * HMRC Server Token, set by constructor
     *
     * @var string
     */
    private $_serverToken = '';

    /**
     * Headers that will be added to every request sent to HMRC
     *
     * @var array
     */
    private $_essentialHeaders = [ 'Accept' => 'application/vnd.hmrc.1.0+json' ];

    /**
     * URL of the HMRC service, either self::URL or self::TESTURL
     *
     * @var string
     */
    private $_url = '';

    /**
     * Access Token is provided when converting authorisation code into tokens, using function getToken()
     *
     * @var string
     */
    private $_accessToken = '';

    /**
     * Refresh Token is provided when converting authorisation code into tokens, using function getToken()
     *
     * @var string
     */
    private $_refreshToken = '';

    /**
     * Function supplied by user, that is called every time the tokens are refreshed.
     * Should contain code to store the tokens for next time this object is instantiated
     *
     * @var \Closure
     */
    private $_updateAuthFunction;

    /**
     * End Point is specific to the HMRC function being called
     * Provided by subclass, not user
     *
     * @var string
     */
    public $endPoint = '';

    /**
     * Method is specific to the HMRC function being called
     * Provided by subclass, not user
     *
     * @var string
     */
    public $method = 'GET';

    /**
     * Status code returned by most recent HMRC request
     *
     * @var int
     */
    public $statusCode = 0;

    /**
     * Set by user to an object that will be JSON encoded and supplied to HMRC in the body of a POST request
     *
     * @var object
     */
    public $requestBody = null;

    /**
     * Body of the response supplied by most recent HMRC request
     *
     * @var object
     */
    public $responseBody = '';

    /**
     * User supplied array of parameters to be passed to HMRC as query string
     *
     * @var array
     */
    public $query;

    /**
     * User supplied array of headers to be added to HMRC request
     * Not used yet!
     *
     * @var array
     */
    public $headers;

    /**
     * Response object provided by successful request to HMRC service
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    public $result;

    /**
     * Error object provided by failed request to HMRC service
     *
     * @var \GuzzleHttp\Exception\BadResponseException
     */
    public $error;

    /**
     * Set to value of most recent set of authentication tokens, when the refresh token is used in refreshAccessToken()
     *
     * @var object
     */
    public $updatedAuthentication;

    /**
     * During testing in the sandbox, use this header to specify the scenario we are testing
     * 
     * @var string
     */
    public $govTestScenario;

    /**
     * Unique id returned by API for operation tracking
     *
     * @var string
     */
    public $xCorrelationId;

    /**
     * Unique reference number returned by API for a submission
     *
     * @var string
     */
    public $receiptId;

    /**
     * The timestamp from the signature, in ISO8601 format
     *
     * @var string
     */
    public $receiptTimestamp;

    /**
     * True if this object has just refreshed the HMRC access tokens
     *
     * @var boolean
     */
    public $credentialsRefreshed;

    /**
     * True if this object has just refreshed the HMRC access tokens
     *
     * @var boolean
     */
    public $logFile;

    /**
     * Hmrc constructor.
     * 
     * @param string $accessToken  Access token provided by supplying authorisation code to getToken()
     * @param string $refreshToken  Refresh token provided by supplying authorisation code to getToken()
     * @param string $service  test or live
     * @param \Closure|null $updateAuthFunction  Function to call when authentication tokens have been refreshed by refreshAccessToken()
     * @param array $credentials   Array with the elements clientID, clientSecret, serverToken
     */
    public function __construct($accessToken = '', $refreshToken = '', $service = 'test', $refreshCredentialsIfNeeded, $updateAuthFunction = null, $credentials = null)
    {
        if (!is_array($credentials)) {
            $this->_clientId = ($service == 'test' ? self::TEST_CLIENT_ID : self::CLIENT_ID);
            $this->_clientSecret = ($service == 'test' ? self::TEST_CLIENT_SECRET : self::CLIENT_SECRET);
            $this->_serverToken = ($service == 'test' ? self::TEST_SERVER_TOKEN : self::SERVER_TOKEN);
        } else {
            $this->_clientId = $credentials['clientId'];
            $this->_clientSecret = $credentials['clientSecret'];
            $this->_serverToken = $credentials['serverToken'];
        }
        $this->_refreshCredentialsIfNeeded = $refreshCredentialsIfNeeded;
        $this->_url = ($service == 'test'? self::TESTURL : self::URL);
        $this->_accessToken = $accessToken;
        $this->_refreshToken = $refreshToken;
        $this->_updateAuthFunction = $updateAuthFunction;
    }

    /**
     * Perform GET request on prespecified end point
     *
     * @param bool $withAuth  Include authentication token (default true)
     * @return int  self::RETURN_AUTH_UPDATE|self::RETURN_SUCCESS|self::RETURN_ERROR
     * @throws \Exception
     */
    public function get($authType = self::AUTH_TYPE_USER) {
        $this->method = 'GET';
        return $this->_execute($authType);
    }

    /**
     * Perform POST request on prespecified end point
     *
     * @param bool $withAuth  Include authentication token (default true)
     * @return int  self::RETURN_AUTH_UPDATE|self::RETURN_SUCCESS|self::RETURN_ERROR
     * @throws \Exception
     */
    public function post($authType = self::AUTH_TYPE_USER) {
        $this->method = 'POST';
        return $this->_execute($authType);
    }

    /**
     * Request wrapper function, primarily to handle refreshing tokens when they have expired
     *
     * @param bool $withAuth  Include authentication token (default true)
     * @return int self::RETURN_AUTH_UPDATE|self::RETURN_SUCCESS|self::RETURN_ERROR
     * @throws \Exception
     */
    protected function _execute($authType = self::AUTH_TYPE_USER) {
        if ($this->endPoint == '') {
            throw new \Exception('Endpoint not specified');
        }
        
        $refreshData = null;
        $result = null;
        $error = null;

        // Attempt request with current accessToken
        try {
            $result = $this->_sendRequest($authType);
        } catch (BadResponseException $error) {
            if ($this->logFile) { error_log(Date('Y-m-d H:i:s').": Error: {$error->getCode()} - {$error->getResponse()}\n",3,$this->logFile); }

            // Error occurred, check code to see whether it was due to expired credentials
            if ($error->getCode() == 401) {
                // Token not valid
                $response = $error->getResponse();
                $body = json_decode($response->getBody());

                if ($body->code == 'INVALID_CREDENTIALS' && $this->_refreshCredentialsIfNeeded && !$this->credentialsRefreshed) {
                    // Need to refresh credentials
                    $this->updatedAuthentication = $this->refreshAccessToken();
                    
                    // Token has been refreshed, retry execution
                    return $this->_execute($authType);
                }
            }
        }
        $this->govTestScenario = null;
        if ($result) {
            // Make result data available to calling application
            $this->result = $result;
            $this->statusCode = $result->getStatusCode();
            $this->responseBody = json_decode($result->getBody());
            $h = $result->getHeader('X-CorrelationId');
            if (count($h)) {
                $this->xCorrelationId = $h[0];
            }

            // Return success or auth update
            if ($this->updatedAuthentication) {
                return self::RETURN_AUTH_UPDATE;
            } else {
                return self::RETURN_SUCCESS;
            }
        } else if ($error) {
            // Make error data available to calling application
            $this->error = $error;
            $this->statusCode = $error->getCode();
            $this->responseBody = json_decode((string) $error->getResponse()->getBody());

            // Return  error
            return self::RETURN_ERROR;
        }
    }

    /**
     * Perform actual request
     *
     * @param $withAuth
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function _sendRequest($authType) {
        $headers = $this->_essentialHeaders;
        if ($authType == self::AUTH_TYPE_USER) {
            $headers['Authorization'] = 'Bearer ' . $this->_accessToken;
        } else if ($authType == self::AUTH_TYPE_SERVER) {
            $headers['Authorization'] = 'Bearer ' . $this->_serverToken;
        }
        if ($this->govTestScenario != '') {
            $headers['Gov-Test-Scenario'] = $this->govTestScenario;
        }

        $options[RequestOptions::HEADERS] = $headers;
        $options[RequestOptions::QUERY] = $this->query;
        if ($this->method == 'POST' && $this->requestBody) {
            $options[RequestOptions::JSON] = $this->requestBody;
        }

        $client = new Client();
        if ($this->logFile) { error_log(Date('Y-m-d H:i:s').": {$this->endPoint} {$this->_accessToken}\n",3,$this->logFile); }
        $result = $client->request($this->method, $this->_url.'/'.$this->endPoint, $options);

        return $result;
    }

    /**
     * Exchanges expired authentication tokens for valid ones
     *
     * @return object
     */
    public function refreshAccessToken(){
        $client = new Client();
        $formParams = [
            'client_secret' => $this->_clientSecret,
            'client_id' => $this->_clientId,
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->_refreshToken
        ];
        if ($this->logFile) { error_log(Date('Y-m-d H:i:s').": /oauth/token {$this->_refreshToken}\n",3,$this->logFile); }

        $this->credentialsRefreshed = true;
        try {
            $result = $client->post($this->_url . '/oauth/token', ['form_params' => $formParams]);

            $data = json_decode($result->getBody());
            if ($this->_updateAuthFunction) { call_user_func($this->_updateAuthFunction,$data); }

            $this->_accessToken = $data->access_token;
            $this->_refreshToken = $data->refresh_token;
            $this->responseBody = $data;

            return self::RETURN_SUCCESS;
        } catch (BadResponseException $error) {
            $this->error = $error;
            $this->statusCode = $error->getCode();
            $this->responseBody = json_decode((string) $error->getResponse()->getBody());

            // Return  error
            return self::RETURN_ERROR;
        }
    }

    /**
     * Retrieves authentication tokens from HMRC based on authorisation code supplied by user authorization
     *
     * @param $code string  Authorisation code provided by HMRC when user authorises application using URL from getCodeUri()
     * @param $redirectUrl string  HMRC currently require this, even though it isn't used, can use the default value
     * @return object  Contains access token and refresh token
     */
    public function getToken($code, $redirectUrl = 'urn:ietf:wg:oauth:2.0:oob') {
        $client = new Client();
        $formParams = [
            'code'=> $code,
            'client_secret' => $this->_clientSecret,
            'client_id' => $this->_clientId,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUrl
        ];
        try {
            $result = $client->post($this->_url . '/oauth/token', ['form_params' => $formParams]);

            $data = json_decode($result->getBody());

            $this->_accessToken = $data->access_token;
            $this->_refreshToken = $data->refresh_token;
            $this->responseBody = $data;

            return self::RETURN_SUCCESS;
        } catch (BadResponseException $error) {
            $this->error = $error;
            $this->statusCode = $error->getCode();
            $this->responseBody = json_decode((string) $error->getResponse()->getBody());

            // Return  error
            return self::RETURN_ERROR;
        }
    }

    /**
     * Reset all input and output values ready for a new API request
     */
    public function clearPreviousCall() {
        $this->endPoint = '';
        $this->credentialsRefreshed = false;
        $this->method = 'GET';
        $this->statusCode = 0;
        $this->requestBody = null;
        $this->responseBody = '';
        $this->query = null;
        $this->headers = null;
        $this->result = null;
        $this->error = null;
        $this->updatedAuthentication = null;
        $this->xCorrelationId = null;
        $this->receiptId = null;
        $this->receiptTimestamp = null;
    }

    /**
     * Helper function providing the URI for a user to authorise the application to submit to HMRC on the users behalf
     *
     * @param $state string  HMRC will pass this back to the $redirectUrl so that you can identify the original request
     * @param $redirectUrl string  URI that HMRC will call when the authorisation is completed
     * @return string  URI for user to visit to authorise application
     */
    public function getCodeUri($state, $redirectUrl, $scope) {
        return $this->_url."/oauth/authorize?response_type=code&client_id=".$this->_clientId."&scope=$scope&state=$state&redirect_uri=$redirectUrl";
    }

    /**
     * Helper function to create a test business user to use this MTD component in the HMRC sandbox
     *
     * @param array $serviceNames
     * @return int  self::RETURN_AUTH_UPDATE|self::RETURN_SUCCESS|self::RETURN_ERROR
     */
    public function createTestUser(Array $serviceNames) {
        $this->endPoint = 'create-test-user/organisations';

        $body = new \stdClass();
        $body->serviceNames = $serviceNames;
        $this->requestBody = $body;

        return $this->post(self::AUTH_TYPE_SERVER);
    }
}
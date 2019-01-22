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

namespace Tests;

use EcoMtd\Hmrc;
use EcoMtd\HmrcVat;

class AuthTestCase extends BaseTestCase
{
    protected $redirectUrl = 'urn:ietf:wg:oauth:2.0:oob';
    protected $authorisationCode = '';

    public function setUp()
    {
        parent::setUp();

        $this->authorisationCode = getenv('AUTHORISATION_CODE');
    }

    public function testGetCode()
    {
        $accessToken = ''; $refreshToken = ''; $auth = '';

        if (file_exists('tests\auth')) {
            // If auth file exists, get $accessToken and $refreshToken
            $f = fopen('tests\auth', 'r');
            $auth = fread($f, filesize('tests\auth'));
            fclose($f);
            if ($auth != '') { $auth = json_decode($auth); $accessToken = $auth->access_token; $refreshToken = $auth->refresh_token; }
        }

        if ($this->authorisationCode == '' && $accessToken == '' && $refreshToken == '') {
            // No code is specified, and auth file doesn't exist
            // output URI to user for granting authority at HMRC website
            $vat = new HmrcVat('', '', '', 'test', false, null, $this->credentials);
            //$hmrc = new Hmrc();
            $vat->createTestUser();
            $vrn = $vat->responseBody->vrn;
            $userId = $vat->responseBody->userId;
            $password = $vat->responseBody->password;
            $data = $vat->getCodeUri('test-state',$this->redirectUrl);
            $msg = "In your .env file, set the VAT_REGISTRATION_NUMBER to $vrn\n\n";
            $msg .= "Please authenticate your test user with userId $userId and password $password at:\n\n$data\n\nThen enter the supplied authorisation code in your .env file in AUTHORISATION_CODE=\"paste-your-code-here\"\n\nThen you can run this test again\n";
            fwrite(STDOUT, $msg . "\n");
            exit();
        } else {
            $this->assertTrue(($this->authorisationCode != '' || $accessToken != ''));
            return $auth;
        }
    }
    /**
     * @depends testGetCode
     */
    public function testGetToken($auth)
    {
        if ($this->authorisationCode != '' && $auth == '') {
            $hmrc = new Hmrc('', '', 'test', false, null, $this->credentials);
            $hmrc->getToken($this->authorisationCode, $this->redirectUrl);
            $data = $hmrc->responseBody;
            $this->assertTrue($data->access_token != '' && $data->refresh_token != '');
            $f = fopen('tests\auth','w');
            fwrite($f, json_encode($data));
            fclose($f);
            return $hmrc->responseBody;
        } else {
            $accessToken = $auth->access_token; $refreshToken = $auth->refresh_token;
            $this->assertTrue($accessToken != '' && $refreshToken != '');
            if ($accessToken != '' && $refreshToken != '') {
                return $auth;
            }
        }
        exit();
    }

    /**
     * @depends testGetToken
     */
    public function testRefreshToken($auth)
    {
        $accessToken = $auth->access_token; $refreshToken = $auth->refresh_token;

        $hmrc = new Hmrc($accessToken, substr($refreshToken,0,15), 'test', false, null, $this->credentials);
        $ret = $hmrc->refreshAccessToken();
        $this->assertEquals($ret, Hmrc::RETURN_ERROR);
        $this->assertEquals(json_encode($hmrc->responseBody), '{"error":"invalid_grant","error_description":"refresh_token is invalid"}');

        $hmrc = new Hmrc($accessToken, $refreshToken, 'test', false, null, $this->credentials);
        $ret = $hmrc->refreshAccessToken();
        if ($ret != Hmrc::RETURN_SUCCESS) {
            echo json_encode($hmrc->responseBody);
        }
        $this->assertEquals(true, $hmrc->credentialsRefreshed);
        $this->assertEquals($ret, Hmrc::RETURN_SUCCESS);


        $data = $hmrc->responseBody;
        $this->assertTrue($data->access_token != '' && $data->refresh_token != '');
        $f = fopen('tests\auth','w');
        fwrite($f, json_encode($data));
        fclose($f);
        return $data;
    }
}
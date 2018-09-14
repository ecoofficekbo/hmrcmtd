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

class AuthTestCase extends BaseTestCase
{
    protected $redirectUrl = 'urn:ietf:wg:oauth:2.0:oob';
    protected $authorisationCode = '';

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
            $hmrc = new Hmrc();
            $data = $hmrc->getCodeUri('test-state',$this->redirectUrl);
            $msg = "Please authenticate your test user at:\n\n$data\n\nThen enter the supplied authorisation code at the top of AuthTestCase.php (protected \$code = 'paste-your-code-here';)\n\nThen you can run this test again\n";
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
            $hmrc = new Hmrc();
            $data = $hmrc->getToken($this->authorisationCode, $this->redirectUrl);
            $this->assertTrue($data->access_token != '' && $data->refresh_token != '');
            $f = fopen('tests\auth','w');
            fwrite($f, json_encode($data));
            fclose($f);
            return $data;
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

        $hmrc = new Hmrc($accessToken, $refreshToken);
        $data = $hmrc->refreshAccessToken();
        $this->assertTrue($data->access_token != '' && $data->refresh_token != '');
        $f = fopen('tests\auth','w');
        fwrite($f, json_encode($data));
        fclose($f);
        return $data;
    }
}
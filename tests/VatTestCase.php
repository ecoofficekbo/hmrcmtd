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

use EcoMtd\VatReturn;
use EcoMtd\HmrcVat;

class VatTestCase extends BaseTestCase
{
    protected $updateAuthFunction;
    protected $accessToken;
    protected $refreshToken;
    protected $vrn = '';

    protected $defaultVatObligations = '{"obligations":[{"start":"2017-01-01","end":"2017-03-31","due":"2017-05-07","status":"F","periodKey":"18A1","received":"2017-05-06"},{"start":"2017-04-01","end":"2017-06-30","due":"2017-08-07","status":"O","periodKey":"18A2"}]}';
    protected $vatReturn ;

    public function setUp() {
        if (file_exists('tests\auth')) {
            // If auth file exists, get $accessToken and $refreshToken
            $f = fopen('tests\auth', 'r');
            $auth = fread($f, filesize('tests\auth'));
            fclose($f);
            if ($auth != '') { $auth = json_decode($auth); $this->accessToken = $auth->access_token; $this->refreshToken = $auth->refresh_token; }
        }
        $this->updateAuthFunction = function($auth) {
            $f = fopen('tests\auth','w');
            fwrite($f, json_encode($auth));
            fclose($f);
            fwrite(STDOUT, "Refreshed Authentication Token\n");
        };
        $this->vatReturn = new VatReturn('18A2', 1000, 0, 1000, 500, 500, 5000, 2500, 0, 0, true);
    }

    public function testGetVatObligations()
    {
        $vat = new HmrcVat($this->vrn, $this->accessToken, $this->refreshToken, 'test', $this->updateAuthFunction);
        $return = $vat->getVatObligations('2017-06-01','2017-12-31');

        $this->assertEquals(200, $vat->statusCode);
        $this->assertEquals($this->defaultVatObligations,  json_encode($vat->responseBody));
        //var_dump( $vat->responseBody);
    }
    public function testPostVatReturn() {
        $vat = new HmrcVat($this->vrn, $this->accessToken, $this->refreshToken, 'test', $this->updateAuthFunction);
        $return = $vat->postVatReturn($this->vatReturn);
        $this->assertEquals(201, $vat->statusCode);
        $array = json_decode(json_encode($vat->responseBody),true);
        $this->assertArrayHasKey('processingDate', $array);
        $this->assertArrayHasKey('paymentIndicator', $array);
        $this->assertArrayHasKey('formBundleNumber', $array);
        $this->assertArrayHasKey('chargeRefNumber', $array);
        //var_dump($vat->responseBody);
    }
   public function testGetVatReturn()
    {
        $vat = new HmrcVat($this->vrn, $this->accessToken, $this->refreshToken, 'test', $this->updateAuthFunction);
        $return = $vat->getVatReturn('18A2');

        //var_dump($vat->responseBody);
        $this->assertEquals(200, $vat->statusCode);

        $expectedArray = json_decode(json_encode($this->vatReturn),true);
        unset($expectedArray['finalised']);
        $this->assertArraySubset($expectedArray, json_decode(json_encode($vat->responseBody),true));
    }
    public function testGetVatLiabilities()
    {
        $vat = new HmrcVat($this->vrn, $this->accessToken, $this->refreshToken, 'test', $this->updateAuthFunction);
        $return = $vat->getVatLiabilities('2016-12-01','2017-06-30');

        var_dump( $vat->responseBody);
        $this->assertEquals(200, $vat->statusCode);
        //$this->assertEquals($this->defaultVatObligations,  json_encode($vat->responseBody));
    }
    public function testGetVatPayments()
    {
        $vat = new HmrcVat($this->vrn, $this->accessToken, $this->refreshToken, 'test', $this->updateAuthFunction);
        $return = $vat->getVatPayments('2016-12-01','2017-06-30');

        var_dump( $vat->responseBody);
        $this->assertEquals(200, $vat->statusCode);
        //$this->assertEquals($this->defaultVatObligations,  json_encode($vat->responseBody));
    }
}
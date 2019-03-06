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

use EcoMtd\FraudPrevention;
use EcoMtd\Hmrc;
use EcoMtd\VatReturn;
use EcoMtd\HmrcVat;

class VatTestCase extends BaseTestCase
{
    protected $updateAuthFunction;
    protected $accessToken;
    protected $refreshToken;
    protected $vrn = '';

    protected $defaultVatObligations = '{"obligations":[{"start":"2017-01-01","end":"2017-03-31","due":"2017-05-07","status":"F","periodKey":"18A1","received":"2017-05-06"},{"start":"2017-04-01","end":"2017-06-30","due":"2017-08-07","status":"O","periodKey":"18A2"}]}';
    protected $quarterlyNoneMetVatObligations = '{"obligations":[{"start":"2017-01-01","end":"2017-03-31","due":"2017-05-07","status":"O","periodKey":"18A1"}]}';
    protected $quarterlyOneMetVatObligations = '{"obligations":[{"start":"2017-01-01","end":"2017-03-31","due":"2017-05-07","status":"F","periodKey":"18A1","received":"2017-05-06"},{"start":"2017-04-01","end":"2017-06-30","due":"2017-08-07","status":"O","periodKey":"18A2"}]}';
    protected $quarterlyTwoMetVatObligations = '{"obligations":[{"start":"2017-01-01","end":"2017-03-31","due":"2017-05-07","status":"F","periodKey":"18A1","received":"2017-05-06"},{"start":"2017-04-01","end":"2017-06-30","due":"2017-08-07","status":"F","periodKey":"18A2","received":"2017-08-06"},{"start":"2017-07-01","end":"2017-09-30","due":"2017-11-07","status":"O","periodKey":"18A3"}]}';
    protected $quarterlyThreeMetVatObligations = '{"obligations":[{"start":"2017-01-01","end":"2017-03-31","due":"2017-05-07","status":"F","periodKey":"18A1","received":"2017-05-06"},{"start":"2017-04-01","end":"2017-06-30","due":"2017-08-07","status":"F","periodKey":"18A2","received":"2017-08-06"},{"start":"2017-07-01","end":"2017-09-30","due":"2017-11-07","status":"F","periodKey":"18A3","received":"2017-11-06"},{"start":"2017-10-01","end":"2017-12-31","due":"2018-02-07","status":"O","periodKey":"18A4"}]}';
    protected $quarterlyFourMetVatObligations = '{"obligations":[{"start":"2017-01-01","end":"2017-03-31","due":"2017-05-07","status":"F","periodKey":"18A1","received":"2017-05-06"},{"start":"2017-04-01","end":"2017-06-30","due":"2017-08-07","status":"F","periodKey":"18A2","received":"2017-08-06"},{"start":"2017-07-01","end":"2017-09-30","due":"2017-11-07","status":"F","periodKey":"18A3","received":"2017-11-06"},{"start":"2017-10-01","end":"2017-12-31","due":"2018-02-07","status":"F","periodKey":"18A4","received":"2018-02-06"}]}';
    protected $monthlyNoneMetVatObligations = '{"obligations":[{"start":"2017-01-01","end":"2017-01-31","due":"2017-03-07","status":"O","periodKey":"18AD"}]}';
    protected $monthlyOneMetVatObligations = '{"obligations":[{"start":"2017-01-01","end":"2017-01-31","due":"2017-03-07","status":"F","periodKey":"18AD","received":"2017-03-06"},{"start":"2017-02-01","end":"2017-02-28","due":"2017-04-07","status":"O","periodKey":"18AE"}]}';
    protected $monthlyTwoMetVatObligations = '{"obligations":[{"start":"2017-01-01","end":"2017-01-31","due":"2017-03-07","status":"F","periodKey":"18AD","received":"2017-03-06"},{"start":"2017-02-01","end":"2017-02-28","due":"2017-04-07","status":"F","periodKey":"18AE","received":"2017-04-06"},{"start":"2017-03-01","end":"2017-03-31","due":"2017-05-07","status":"O","periodKey":"18AF"}]}';
    protected $monthlyThreeMetVatObligations = '{"obligations":[{"start":"2017-01-01","end":"2017-01-31","due":"2017-03-07","status":"F","periodKey":"18AD","received":"2017-03-06"},{"start":"2017-02-01","end":"2017-02-28","due":"2017-04-07","status":"F","periodKey":"18AE","received":"2017-04-06"},{"start":"2017-03-01","end":"2017-03-31","due":"2017-05-07","status":"F","periodKey":"18AF","received":"2017-05-06"},{"start":"2017-04-01","end":"2017-04-30","due":"2017-06-07","status":"O","periodKey":"18AG"}]}';
    protected $singleLiability = '{"liabilities":[{"taxPeriod":{"from":"2017-01-01","to":"2017-02-01"},"type":"VAT Return Debit Charge","originalAmount":463872,"outstandingAmount":463872,"due":"2017-03-08"}]}';
    protected $multipleLiabilities = '{"liabilities":[{"taxPeriod":{"from":"2017-01-01","to":"2017-04-05"},"type":"VAT Return Debit Charge","originalAmount":463872,"outstandingAmount":463872,"due":"2017-05-12"},{"taxPeriod":{"from":"2017-04-01","to":"2017-04-30"},"type":"VAT Return Debit Charge","originalAmount":15,"outstandingAmount":0,"due":"2017-06-09"},{"taxPeriod":{"from":"2017-08-01","to":"2017-08-31"},"type":"VAT CA Charge","originalAmount":8493.38,"outstandingAmount":7493.38,"due":"2017-10-07"},{"taxPeriod":{"from":"2017-10-01","to":"2017-12-01"},"type":"VAT OA Debit Charge","originalAmount":3000,"outstandingAmount":2845,"due":"2017-12-31"}]}';
    protected $singlePayment = '{"payments":[{"amount":1534.65,"received":"2017-02-12"}]}';
    protected $multiplePayments = '{"payments":[{"amount":5,"received":"2017-02-11"},{"amount":50,"received":"2017-03-11"},{"amount":1000,"received":"2017-03-12"},{"amount":321,"received":"2017-08-05"},{"amount":91},{"amount":5,"received":"2017-09-12"}]}';
    protected $vatReturn ;
    protected $invalidVatReturn ;
    protected $invalidVatReturn1 ;

    public function setUp() {
        parent::setUp();
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
        $this->invalidVatReturn = new VatReturn('18A2', 1000, 0, 1000, 500, 500, 5000.25, 2500.75, 0, 0, true);
        $this->invalidVatReturn1 = new VatReturn('18A2', 1000, 0, 1000, 1500, -500, 5000, 7500, 0, 0, true);
        $this->vrn = getenv('VAT_REGISTRATION_NUMBER');
    }

    public function testGetVatObligations()
    {
        $vat = new HmrcVat($this->vrn, $this->accessToken, $this->refreshToken, 'test', false, $this->updateAuthFunction, $this->credentials);
        $return = $vat->getVatObligations('2017-01-01','2017-06-30', '');
        $this->assertEquals(200, $vat->statusCode);
        $this->assertEquals($this->defaultVatObligations,  json_encode($vat->responseBody));

        $vat->govTestScenario = 'QUARTERLY_NONE_MET';
        $return = $vat->getVatObligations('2017-01-01','2017-06-30', '');
        $this->assertEquals(200, $vat->statusCode);
        $this->assertEquals($this->quarterlyNoneMetVatObligations,  json_encode($vat->responseBody));

        $vat->govTestScenario = 'QUARTERLY_ONE_MET';
        $return = $vat->getVatObligations('2017-01-01','2017-06-30', '');
        $this->assertEquals(200, $vat->statusCode);
        $this->assertEquals($this->quarterlyOneMetVatObligations,  json_encode($vat->responseBody));

        $vat->govTestScenario = 'QUARTERLY_TWO_MET';
        $return = $vat->getVatObligations('2017-01-01','2017-06-30', '');
        $this->assertEquals(200, $vat->statusCode);
        $this->assertEquals($this->quarterlyTwoMetVatObligations,  json_encode($vat->responseBody));

        $vat->govTestScenario = 'QUARTERLY_THREE_MET';
        $return = $vat->getVatObligations('2017-01-01','2017-06-30', '');
        $this->assertEquals(200, $vat->statusCode);
        $this->assertEquals($this->quarterlyThreeMetVatObligations,  json_encode($vat->responseBody));

        $vat->govTestScenario = 'QUARTERLY_FOUR_MET';
        $return = $vat->getVatObligations('2017-01-01','2017-06-30', '');
        $this->assertEquals(200, $vat->statusCode);
        $this->assertEquals($this->quarterlyFourMetVatObligations,  json_encode($vat->responseBody));

        $vat->govTestScenario = 'MONTHLY_NONE_MET';
        $return = $vat->getVatObligations('2017-01-01','2017-06-30', '');
        $this->assertEquals(200, $vat->statusCode);
        $this->assertEquals($this->monthlyNoneMetVatObligations,  json_encode($vat->responseBody));

        $vat->govTestScenario = 'MONTHLY_ONE_MET';
        $return = $vat->getVatObligations('2017-01-01','2017-06-30', '');
        $this->assertEquals(200, $vat->statusCode);
        $this->assertEquals($this->monthlyOneMetVatObligations,  json_encode($vat->responseBody));

        $vat->govTestScenario = 'MONTHLY_TWO_MET';
        $return = $vat->getVatObligations('2017-01-01','2017-06-30', '');
        $this->assertEquals(200, $vat->statusCode);
        $this->assertEquals($this->monthlyTwoMetVatObligations,  json_encode($vat->responseBody));

        $vat->govTestScenario = 'MONTHLY_THREE_MET';
        $return = $vat->getVatObligations('2017-01-01','2017-06-30', '');
        $this->assertEquals(200, $vat->statusCode);
        $this->assertEquals($this->monthlyThreeMetVatObligations,  json_encode($vat->responseBody));

        $vat->govTestScenario = 'NOT_FOUND';
        $return = $vat->getVatObligations('2017-01-01','2017-06-30', '');
        $this->assertEquals(404, $vat->statusCode);
    }
    public function testPostVatReturn() {
        // THIS TEST WILL ONLY SUCCEED THE FIRST TIME IT'S RUN
        // SUBSEQUENT RUNS WILL RESULT IN A 403 DUPLICATE SUBMISSION ERROR
        $vat = new HmrcVat($this->vrn, $this->accessToken, $this->refreshToken, 'test', false, $this->updateAuthFunction, $this->credentials);
        $return = $vat->postVatReturn($this->invalidVatReturn);
        $this->assertEquals(Hmrc::RETURN_ERROR, $return);
        $this->assertEquals(400, $vat->statusCode);
        $array = json_decode(json_encode($vat->responseBody), true);
        $this->assertEquals('INVALID_REQUEST', $array['code']);
        $this->assertEquals('INVALID_MONETARY_AMOUNT', $array['errors'][0]['code']);
        $this->assertEquals('INVALID_MONETARY_AMOUNT', $array['errors'][1]['code']);

        $return = $vat->postVatReturn($this->invalidVatReturn1);
        $this->assertEquals(Hmrc::RETURN_ERROR, $return);
        $this->assertEquals(400, $vat->statusCode);
        $array = json_decode(json_encode($vat->responseBody), true);
        $this->assertEquals('INVALID_REQUEST', $array['code']);
        $this->assertEquals('INVALID_MONETARY_AMOUNT', $array['errors'][0]['code']);

        $return = $vat->postVatReturn($this->vatReturn);
        if ($return == Hmrc::RETURN_SUCCESS || $return == Hmrc::RETURN_AUTH_UPDATE) {
            $this->assertEquals(201, $vat->statusCode);
            $array = json_decode(json_encode($vat->responseBody), true);
            $this->assertArrayHasKey('processingDate', $array);
            $this->assertArrayHasKey('formBundleNumber', $array);
            $this->assertRegExp('/\d\d\d\d-\d\d-\d\dT\d\d:\d\d:\d\dZ/', $vat->receiptTimestamp);
            $this->assertRegExp('/[0-9a-z\-]{36}/', $vat->receiptId);
        } else {
            $this->assertEquals(403, $vat->statusCode);
            $array = json_decode(json_encode($vat->responseBody), true);
            $this->assertEquals('BUSINESS_ERROR', $array['code']);
            $this->assertEquals('DUPLICATE_SUBMISSION', $array['errors'][0]['code']);
        }

        $vat->govTestScenario = 'INVALID_VRN';
        $return = $vat->postVatReturn($this->vatReturn);
        $this->assertEquals(400, $vat->statusCode);
        $array = json_decode(json_encode($vat->responseBody), true);
        $this->assertEquals('VRN_INVALID', $array['code']);

        $vat->govTestScenario = 'INVALID_PERIODKEY';
        $return = $vat->postVatReturn($this->vatReturn);
        $this->assertEquals(400, $vat->statusCode);
        $array = json_decode(json_encode($vat->responseBody), true);
        $this->assertEquals('PERIOD_KEY_INVALID', $array['code']);

        $vat->govTestScenario = 'INVALID_PAYLOAD';
        $return = $vat->postVatReturn($this->vatReturn);
        $this->assertEquals(400, $vat->statusCode);
        $array = json_decode(json_encode($vat->responseBody), true);
        $this->assertEquals('INVALID_REQUEST', $array['code']);

        /* THIS TEST CURRENTLY RETURNS A 500 ERROR
         *
         * $vat->govTestScenario = 'INVALID_ARN';
        $return = $vat->postVatReturn($this->vatReturn);
        var_dump($vat->responseBody);
        $this->assertEquals(400, $vat->statusCode);
        $array = json_decode(json_encode($vat->responseBody), true);
        $this->assertEquals('ARN_INVALID', $array['code']);*/

        $vat->govTestScenario = 'DUPLICATE_SUBMISSION';
        $return = $vat->postVatReturn($this->vatReturn);
        $this->assertEquals(403, $vat->statusCode);
        $array = json_decode(json_encode($vat->responseBody), true);
        $this->assertEquals('BUSINESS_ERROR', $array['code']);
        $this->assertEquals('DUPLICATE_SUBMISSION', $array['errors'][0]['code']);
    }
    public function testGetVatReturn()
     {
         $fraudPrevention = new FraudPrevention();
         $fraudPrevention->setClientConnectionMethod(FraudPrevention::WEB_APP_VIA_SERVER);
         $fraudPrevention->setClientPublicIp('1.1.1.1');
         $fraudPrevention->setClientMacAddresses(['ea:43:1a:5d:21:45','10:12:cc:fa:aa:32']);

         $vat = new HmrcVat($this->vrn, $this->accessToken, $this->refreshToken, 'test', false, $this->updateAuthFunction, $this->credentials);
         $vat->fraudPrevention = $fraudPrevention;
         $return = $vat->getVatReturn('18A2');
         $this->assertEquals(200, $vat->statusCode);
         $expectedArray = json_decode(json_encode($this->vatReturn),true);
         unset($expectedArray['finalised']);
         $this->assertArraySubset($expectedArray, json_decode(json_encode($vat->responseBody),true));

         $vat->govTestScenario = 'DATE_RANGE_TOO_LARGE';
         $return = $vat->getVatReturn('18A2');
         $this->assertEquals(403, $vat->statusCode);
         $array = json_decode(json_encode($vat->responseBody), true);
         $this->assertEquals('BUSINESS_ERROR', $array['code']);
         $this->assertEquals('DATE_RANGE_TOO_LARGE', $array['errors'][0]['code']);
     }
     public function testGetVatLiabilities()
     {
         $vat = new HmrcVat($this->vrn, $this->accessToken, $this->refreshToken, 'test', false, $this->updateAuthFunction, $this->credentials);
         $return = $vat->getVatLiabilities('2017-01-25','2017-06-30');
         $this->assertEquals(404, $vat->statusCode); // Test no results

         $vat->govTestScenario = 'SINGLE_LIABILITY';
         $return = $vat->getVatLiabilities('2017-01-02','2017-02-02');
         $this->assertEquals(200, $vat->statusCode); // Test single result
         $this->assertEquals($this->singleLiability, json_encode($vat->responseBody));

         $vat->govTestScenario = 'MULTIPLE_LIABILITIES';
         $return = $vat->getVatLiabilities('2017-04-05','2017-12-21');
         $this->assertEquals(200, $vat->statusCode); // Test multiple results
         $this->assertEquals($this->multipleLiabilities, json_encode($vat->responseBody));

     }
    public function testGetVatPayments()
    {
        $vat = new HmrcVat($this->vrn, $this->accessToken, $this->refreshToken, 'test', false, $this->updateAuthFunction, $this->credentials);
        $return = $vat->getVatPayments('2016-12-01','2017-06-30');
        $this->assertEquals(404, $vat->statusCode); // Test no results

        $vat->govTestScenario = 'SINGLE_PAYMENT';
        $return = $vat->getVatPayments('2017-01-01','2017-02-02');
        $this->assertEquals(200, $vat->statusCode); // Test single result
        $this->assertEquals($this->singlePayment, json_encode($vat->responseBody));

        $vat->govTestScenario = 'MULTIPLE_PAYMENTS';
        $return = $vat->getVatPayments('2017-02-27','2017-12-21');
        $this->assertEquals(200, $vat->statusCode); // Test multiple result
        $this->assertEquals($this->multiplePayments, json_encode($vat->responseBody));

    }
}
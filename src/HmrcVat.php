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

class HmrcVat extends Hmrc
{
    /**
     * Users VAT Return Number. For testing in the HMRC sandbox this is returned by a call to createTestUser()
     * @var string
     */
    public $vrn = '';

    /**
     * HmrcVat constructor.
     *
     * @param string $vrn  Users VAT Return Number
     * @param string $accessToken  Access token provided by supplying authorisation code to getToken()
     * @param string $refreshToken  Refresh token provided by supplying authorisation code to getToken()
     * @param string $service  test or live
     * @param \Closure|null $updateAuthFunction  Function to call when authentication tokens have been refreshed by refreshAccessToken()
     * @param array $credentials   Array with the elements clientID, clientSecret, serverToken
     */
    public function __construct($vrn = '', $accessToken = '', $refreshToken = '', $service='test', $refreshCredentialsIfNeeded, $updateAuthFunction=null, $credentials=null)
    {
        parent::__construct($accessToken, $refreshToken, $service, $refreshCredentialsIfNeeded, $updateAuthFunction, $credentials);
        $this->vrn = $vrn;
    }

    /**
     * @param $dateFrom
     * @param $dateTo
     * @param string $status
     * @return int
     */
    public function getVatObligations($dateFrom, $dateTo, $status = '') {
        $this->clearPreviousCall();
        $this->endPoint = 'organisations/vat/' . $this->vrn . '/obligations';
        $this->query = [
            'from' => $dateFrom,
            'to' => $dateTo
        ];
        if ($status != '') { $this->query['status'] = $status; }
        return $this->get();
    }

    public function postVatReturn(VatReturn $vatReturn) {
        $this->clearPreviousCall();
        $this->endPoint = 'organisations/vat/' . $this->vrn . '/returns';
        $this->requestBody = $vatReturn;
        $result = $this->post();
        if ($result != Hmrc::RETURN_ERROR) {
            $this->receiptId = $this->result->getHeader('Receipt-ID')[0];
            $this->receiptTimestamp = $this->result->getHeader('Receipt-Timestamp')[0];
        }
        return $result;
    }

    public function getVatReturn($periodKey) {
        $this->clearPreviousCall();
        $this->endPoint = 'organisations/vat/' . $this->vrn . '/returns/' . $periodKey;
        return $this->get();
    }

    public function getVatLiabilities($dateFrom, $dateTo) {
        $this->clearPreviousCall();
        $this->endPoint = 'organisations/vat/' . $this->vrn . '/liabilities';
        $this->query = [
            'from' => $dateFrom,
            'to' => $dateTo
        ];
        return $this->get();
    }

    public function getVatPayments($dateFrom, $dateTo) {
        $this->clearPreviousCall();
        $this->endPoint = 'organisations/vat/' . $this->vrn . '/payments';
        $this->query = [
            'from' => $dateFrom,
            'to' => $dateTo
        ];
        return $this->get();
    }

    public function createTestUser(Array $serviceNames = null) {
        return parent::createTestUser(($serviceNames ? $serviceNames : [ 'mtd-vat' ]));
    }
    public function getCodeUri($state, $redirectUrl, $scope='')
    {
        if ($scope == '') { $scope = 'read:vat%20write:vat'; }
        return parent::getCodeUri($state, $redirectUrl, $scope);
    }
}
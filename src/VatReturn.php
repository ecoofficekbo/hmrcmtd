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

/**
 * Class VatReturn
 *
 * Class to encapsulate 9 Box VAT return data, along with the HMRC periodKey
 *
 * @package EcoMtd
 */
class VatReturn
{
    /**
     * Period Key is retrieved from HMRC online service
     *
     * @var string
     */
    public $periodKey;

    /**
     * Box 1 on VAT Return
     * Two decimal point float between -9999999999999.99 and 9999999999999.99
     *
     * @var float
     */
    public $vatDueSales;

    /**
     * Box 2 on VAT Return
     * Two decimal point float between -9999999999999.99 and 9999999999999.99
     *
     * @var float
     */
    public $vatDueAcquisitions;

    /**
     * Box 3 on VAT Return
     * Two decimal point float between -9999999999999.99 and 9999999999999.99
     *
     * @var float
     */
    public $totalVatDue;

    /**
     * Box 4 on VAT Return
     * Two decimal point float between -9999999999999.99 and 9999999999999.99
     *
     * @var float
     */
    public $vatReclaimedCurrPeriod;

    /**
     * Box 5 on VAT Return
     * Two decimal point float between 0 and 9999999999999.99
     * Difference between the largest and smallest values of $totalVatDue and $vatReclaimedCurrPeriod
     *
     * @var float
     */
    public $netVatDue;

    /**
     * Box 6 on VAT Return
     * Two decimal point float between -9999999999999.99 and 9999999999999.99
     *
     * @var float
     */
    public $totalValueSalesExVAT;

    /**
     * Box 7 on VAT Return
     * Two decimal point float between -9999999999999.99 and 9999999999999.99
     *
     * @var float
     */
    public $totalValuePurchasesExVAT;

    /**
     * Box 8 on VAT Return
     * Two decimal point float between -9999999999999.99 and 9999999999999.99
     *
     * @var float
     */
    public $totalValueGoodsSuppliedExVAT;

    /**
     * Box 9 on VAT Return
     * Two decimal point float between -9999999999999.99 and 9999999999999.99
     *
     * @var float
     */
    public $totalAcquisitionsExVAT;

    /**
     * Must be set to true, to acknowledge that the client has declared that this VAT return is finalised
     *
     * @var boolean
     */
    public $finalised = false;

    /**
     * VatReturn constructor. Parameters as described above
     *
     * @param $periodKey string
     * @param $vatDueSales float
     * @param $vatDueAcquisitions float
     * @param $totalVatDue float
     * @param $vatReclaimedCurrPeriod float
     * @param $netVatDue float
     * @param $totalValueSalesExVAT float
     * @param $totalValuePurchasesExVAT float
     * @param $totalValueGoodsSuppliedExVAT float
     * @param $totalAcquisitionsExVAT float
     * @param $finalised boolean
     */
    public function __construct($periodKey, $vatDueSales, $vatDueAcquisitions, $totalVatDue, $vatReclaimedCurrPeriod, $netVatDue, $totalValueSalesExVAT, $totalValuePurchasesExVAT,
                                $totalValueGoodsSuppliedExVAT, $totalAcquisitionsExVAT, $finalised)
    {
        $this->periodKey = $periodKey;
        $this->vatDueSales = $vatDueSales;
        $this->vatDueAcquisitions = $vatDueAcquisitions;
        $this->totalVatDue = $totalVatDue;
        $this->vatReclaimedCurrPeriod = $vatReclaimedCurrPeriod;
        $this->netVatDue = $netVatDue;
        $this->totalValueSalesExVAT = $totalValueSalesExVAT;
        $this->totalValuePurchasesExVAT = $totalValuePurchasesExVAT;
        $this->totalValueGoodsSuppliedExVAT = $totalValueGoodsSuppliedExVAT;
        $this->totalAcquisitionsExVAT = $totalAcquisitionsExVAT;
        $this->finalised = $finalised;
    }
    
}
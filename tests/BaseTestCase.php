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

use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

abstract class BaseTestCase extends PHPUnit_Framework_TestCase
{
    protected $credentials;

    public function setUp()
    {
        $this->credentials = [
            'clientId' => getenv('CLIENT_ID'),
            'clientSecret' => getenv('CLIENT_SECRET'),
            'serverToken' => getenv('SERVER_TOKEN')
        ];
    }

    public function assertArrayContainsArray($expected, $actual)
    {
        for ($i = 0; $i < count($expected); $i++) {
            $a = json_decode(json_encode($actual[$i]), true);
            PHPUnit::assertArraySubset(
                $expected[$i], $a, false, 'Unable to find JSON: '.PHP_EOL.PHP_EOL.
                "[".json_encode($expected[$i])."]".PHP_EOL.PHP_EOL.
                'within response JSON:'.PHP_EOL.PHP_EOL.
                "[".json_encode($a)."]".PHP_EOL.PHP_EOL
            );
        }


        return $this;
    }
}

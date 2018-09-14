# HmrcMtd
PHP component for accessing the new HMRC MTD API. Focused initially on VAT which is currently in private beta, but will be mandatory to use from April 2019 for businesses over the VAT threshold

## Running The Tests

To begin, first ensure you have created a test user:
```
$vat = new HmrcVat();
$vat->createTestUser();
```
Enter the returned vrn value into the `$vrn` variable at the top of tests/VatTestCase.php. Save the userId and password for the next steps
```
protected $vrn = 'paste-vrn-here';
```
Execute tests/AuthTestCase.php
```
vendor\bin\phpunit tests/AuthTestCase.php
```
The output will give you a URI to copy and paste into your browser. Then you will need to log into the HMRC test platform with the userId and password from the previous step.
At the end of the process, it will give you an authorisation code to copy and paste into tests/AuthTestCase.php
```
protected $authorisationCode = 'paste-authorisation-code-here';
```
Finally you can run all the tests. This process will create a file called tests/auth to save the `access_token` and `refresh_token` for future use
```
vendor\bin\phpunit
```

## License
This project is licensed under the GNU GPLv3 License - see the [License](License) file for more details
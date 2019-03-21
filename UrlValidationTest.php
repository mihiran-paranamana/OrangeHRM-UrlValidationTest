<?php
/**
 * OrangeHRM Enterprise is a closed sourced comprehensive Human Resource Management (HRM)
 * System that captures all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM Inc is the owner of the patent, copyright, trade secrets, trademarks and any
 * other intellectual property rights which subsist in the Licensed Materials. OrangeHRM Inc
 * is the owner of the media / downloaded OrangeHRM Enterprise software files on which the
 * Licensed Materials are received. Title to the Licensed Materials and media shall remain
 * vested in OrangeHRM Inc. For the avoidance of doubt title and all intellectual property
 * rights to any design, new software, new protocol, new interface, enhancement, update,
 * derivative works, revised screen text or any other items that OrangeHRM Inc creates for
 * Customer shall remain vested in OrangeHRM Inc. Any rights not expressly granted herein are
 * reserved to OrangeHRM Inc.
 *
 * Please refer http://www.orangehrm.com/Files/OrangeHRM_Commercial_License.pdf for the license which includes terms and conditions on using this software.
 *
 */

class UrlValidationTest extends PHPUnit_Framework_TestCase {

    private $basecodeUrlArray;
    private $invalidUrls;

    /**
     * Set up method
     */
    protected function setUp() {
        $this->basecodeUrlArray = array();
        $this->invalidUrls = array();
    }

    private function getInvalidUrlsDetails() {
        $invalidUrlsDetailsArray = array();
        $invalidUrlArray = array_filter($this->basecodeUrlArray, function ($value) {
            return in_array($value['url'], $this->invalidUrls);
        });

        foreach ($invalidUrlArray as $fileLineUrlArray) {
            array_push($invalidUrlsDetailsArray, $fileLineUrlArray['url'] . ':' . $fileLineUrlArray['file'] . ':' . $fileLineUrlArray['line']);
        }
        return $invalidUrlsDetailsArray;
    }

    public function testUrlValidation() {
        $pluginDir = sfConfig::get('sf_plugins_dir');
        $command = 'cd "' . $pluginDir . '" && grep -R -n --include=*.php --include=*.js  --exclude-dir={doctrine,test,vendor,saml} -Eoi "(http|https)://[a-zA-Z0-9./?=_-]*" *';
        $outputs = "";
        $result = exec($command, $outputs);

        foreach ($outputs as $output) {
            $fileLineUrlOutput = explode(':', $output, 3);

            $fileLineUrlArray = array();
            $fileLineUrlArray['file'] = $fileLineUrlOutput[0];
            $fileLineUrlArray['line'] = $fileLineUrlOutput[1];
            $fileLineUrlArray['url'] = $fileLineUrlOutput[2];

            array_push($this->basecodeUrlArray, $fileLineUrlArray);
        }

        // Write to the urlData.txt file
//        $urls = array_column($basecodeUrlArray, 'url');
//        $urlsUniq = array_unique($urls);
//        file_put_contents('urlData.txt', '');
//        foreach ($urlsUniq as $urlUniq) {
//            file_put_contents('urlData.txt', $urlUniq . PHP_EOL, FILE_APPEND);
//        }

        // Validate with urlData.txt file
        $urls = array_column($this->basecodeUrlArray, 'url');
        $urlsUniq = array_unique($urls);
        $validUrls = file("urlData.txt", FILE_IGNORE_NEW_LINES);
        $this->invalidUrls = array_diff($urlsUniq, $validUrls);

        $this->assertEquals(0, count($this->invalidUrls),
            "Following Urls are invalid:\n" . implode("\n", $this->getInvalidUrlsDetails()));
    }

}
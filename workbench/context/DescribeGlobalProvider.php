<?php
require_once 'context/CacheableValueProvider.php';

class DescribeGlobalProvider implements CacheableValueProvider {

    function isSerializable() {
        return true;
    }

    function isCacheable() {
        return getConfig('cacheDescribeGlobal');
    }

    function load($args) {
        $describeGlobalResponse = WorkbenchContext::get()->getPartnerConnection()->describeGlobal();

        //Change to pre-17.0 format
        if (isset($describeGlobalResponse->sobjects) && !isset($describeGlobalResponse->types)) {
            $describeGlobalResponse->types = array(); //create the array
            foreach ($describeGlobalResponse->sobjects as $sobject) {
                $describeGlobalResponse->types[] = $sobject->name; //migrate to pre 17.0 format
                $describeGlobalResponse->attributeMap["$sobject->name"] = $sobject; //recreate into a map for faster lookup later
            }
            unset($describeGlobalResponse->sobjects); //remove from array, since not needed
        }

        return $describeGlobalResponse;
    }
}
?>
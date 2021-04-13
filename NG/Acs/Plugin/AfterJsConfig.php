<?php
namespace NG\Acs\Plugin;

use Magento\Checkout\Block\Onepage;
use Magento\Framework\Serialize\Serializer\Json;

class AfterJsConfig
{
    /**
     * @var Json
     */
    protected $serializer;

    /**
     * AfterJsConfig constructor.
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @param Onepage $subject
     * @param $result
     * @return mixed
     */
    public function afterGetJsLayout(Onepage $subject, $result) {
        try {
            if ($result != "") {
                $jsonLayoutArray = $this->serializer->unserialize($result);

                // you can add more modification here.

                $jsonLayoutArray['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['shipping-address-fieldset']
                ['children']['country_id']['sortOrder'] = 61;

                return $this->serializer->serialize($jsonLayoutArray);
            }
        } catch (\Exception $e) {
        }

        return $result;
    }
}
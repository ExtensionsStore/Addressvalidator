<?php

/**
 * Validator search results model
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Model;

use Magento\Framework\Api\SearchResults;
use ExtensionsStore\Addressvalidator\Api\Data\ValidatorSearchResultsInterface;

class ValidatorSearchResults extends SearchResults implements ValidatorSearchResultsInterface {
}

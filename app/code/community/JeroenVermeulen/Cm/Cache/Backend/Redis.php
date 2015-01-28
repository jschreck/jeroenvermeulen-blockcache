<?php
/**
 * JeroenVermeulen_BlockCache
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future.
 *
 * @category     JeroenVermeulen
 * @package      JeroenVermeulen_BlockCache
 * @copyright    Copyright (c) 2015 Jeroen Vermeulen (http://www.jeroenvermeulen.eu)
 */

/**
 * Class JeroenVermeulen_Cm_Cache_Backend_File
 * This class adds some functionality to Cm_Cache_Backend_Redis, mainly events.
 *
 * {@inheritdoc}
 */
class JeroenVermeulen_Cm_Cache_Backend_Redis extends Cm_Cache_Backend_Redis
{
    const ADMIN_READ_TIMEOUT = 7200;

    /**
     * This constructor is executed in a very early stage, during @see Mage_Core_Model_App->initCache()
     * Only few things of the Magento framework will be initialized at this time.
     *
     * {@inheritdoc}
     */
    public function __construct( $options ) {
        if ( JeroenVermeulen_BlockCache_Helper_Data::isAdmin() ) {
            if ( empty($options['read_timeout']) || $options['read_timeout'] < self::ADMIN_READ_TIMEOUT ) {
                $options['read_timeout'] = self::ADMIN_READ_TIMEOUT;
            }
        }
        parent::__construct( $options );
    }

    /**
     * This function will dispatch the event 'jv_clean_backend_cache'. Event listeners can change the tags array.
     *
     * {@inheritdoc}
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {
        $transportObject = new Varien_Object;
        $transportObject->setTags( $tags );
        Mage::dispatchEvent( 'jv_clean_backend_cache', array( 'transport' => $transportObject ) );
        $tags = $transportObject->getTags();
        parent::clean($mode, $tags);
    }

    /**
     * This function will dispatch the event 'jv_cache_miss_jv' when a cache key miss occurs loading a key
     * from JeroenVermeulen_BlockCache.
     *
     * {@inheritdoc}
     */
    public function load($id, $doNotTestCacheValidity = false) {
        $result = parent::load($id, $doNotTestCacheValidity);
        if ( false === $result && false !== strpos($id,'_JV_') ) {
            Mage::dispatchEvent('jv_cache_miss_jv', array('id' => $id));
        }
        return $result;
    }

}

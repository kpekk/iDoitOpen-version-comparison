<?php

/**
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.6
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_cache_keyvalue extends isys_cache implements isys_cache_keyvaluable
{

    /**
     * Autogenerated Namespace
     *
     * @var string
     */
    protected $m_ns = '';

    /**
     * Namespace separator
     *
     * @var string
     */
    protected $m_ns_separator = ':';

    /**
     * Prepare and use a namespace
     *
     * @param string $p_namespace
     *
     * @return  isys_cache_keyvalue
     */
    public function ns($p_namespace)
    {
        $this->m_ns = $this->get('caching_namespace:' . $p_namespace);

        if (!$this->m_ns) {
            $namespace = md5(microtime());
            $this->set('caching_namespace:' . $p_namespace, $namespace);
            $this->m_ns = $namespace;
        }

        return $this;
    }

    /**
     * Invalidates a namespace
     *
     * @param $p_namespace
     *
     * @return isys_cache_keyvalue
     */
    public function ns_invalidate($p_namespace)
    {
        $this->set('caching_namespace:' . $p_namespace, false);

        return $this->ns($p_namespace);
    }

    /**
     * Prepend a namespace, if one exists
     *
     * @param $p_key
     *
     * @return $this
     */
    protected function prepend_ns(&$p_key)
    {
        if ($this->m_ns) {
            $p_key = $this->m_ns . $this->m_ns_separator . $p_key;
        }

        return $this;
    }

}
<?php
/**
 * User: matthias
 * Date: 02.11.13
 * Time: 16:50
 */
namespace at\foundation;

use at\foundation\plugin\IService;

require_once('IService.php');

class ServiceProvider {
    /**
     * @param String $name
     * @return IService
     */
    public function getPlugin($name) {
        require_once(dirname(__FILE__) . '/../../services/'.str_replace('\\', '/', $name).'.php');

        $return = 'at\\foundation\\plugin\\'.$name;

        return new $return;

    }
}
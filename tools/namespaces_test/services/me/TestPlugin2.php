<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 02.11.13
 * Time: 16:45
 */
namespace at\foundation\plugin\me;
use at\foundation\core as core;
use at\foundation\plugin as plugin;

class TestPlugin2 implements plugin\IService {

    /**
     * @return string
     */
    public function view()
    {
        return "me/TestPlugin2::view()";
    }
}
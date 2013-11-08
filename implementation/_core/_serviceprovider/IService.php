<?php
/**
 * Interface IService
 * Interface defining a service
 *
 * every service has to implement IService and
 */
interface IService {
        /**
         *  args: array('_action'=>$action, //page, list, etc
         *              'argumente...  //GET, POST
         *              );
         */

    	/**
    	 * Renders a view of the Service results
    	 * @param array $args An array containing arguments
    	 * @return string
    	 */
        public function view($args);

    }
?>
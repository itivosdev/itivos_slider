<?php
/**
 * Name: Ajax Controllers
 * Author: Bernardo Fuentes Ch.
 * Twitter: bfu3ntes
 * Date: 10/10/2021
 */
require_once($_SERVER['DOCUMENT_ROOT']."/config/ini.php");
require_once($_SERVER['DOCUMENT_ROOT']."/modules/itivos_slider/classes/itivosSlider.php");
class sliderAjaxItivos extends ModulesBackControllers
{
	function __construct()
    {
        parent::__construct();
        $this->is_logged = true;
        $this->ajax_anabled = true;
        $this->initialize_controller();
    }
}


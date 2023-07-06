<?php 
/**
 * Name: Content Controller for itivos Slider
 * Author: Bernardo Fuentes Ch.
 * Twitter: bfu3ntes
 * Date: 22/11/2021
 */
require_once($_SERVER['DOCUMENT_ROOT']."/config/ini.php");
class sliderController extends ModulesFrontControllers
{
	public $view;
	public $view_search;
	function __construct()
	{
		parent::__construct();
		$this->view->assign('page', "content" );
		$this->ajax_anabled = true;
		$this->initialize_controller();

	}
	public function frontViewInit($param)
    {  
    	if (is_numeric($param)) {
    		$param = (int) $param;
			$page_data = ItivosCmsClass::getPage($param);
        }else {
			$page_data = ItivosCmsClass::getPageByName($param, $this->lang);
        }
		if ( empty($page_data) ) {
			header("Location: ".__ERROR__."404");
			die();
		}
		if (!empty($page_data['content'])) {
            $page_data['content'] = base64_decode($page_data['content']); 
        }
		$this->view->assign('page_data', $page_data );
    	$this->view->display($this->template_dir_front."page_view.tpl");
    }
}

$sliderController = new sliderController();
switch ($sliderController->view_search) {
	case 'show':
		$sliderController->frontViewInit($sliderController->params);
		break;
}
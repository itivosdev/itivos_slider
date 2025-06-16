<?php
/**
 * Copyright since 2021 Itivos SA and Contributors
 * Itivos is an International Registered Trademark & Property of Itivos SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Itivos.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Itivos B2BSoft to newer
 * versions in the future. If you wish to customize Itivos for your
 * needs please refer to https://devdocs.itivos.com/ for more information.
 *
 * @author    Itivos SA and Contributors <contact@itivos.com>
 * @copyright Since 2021 Itivos SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

require_once($_SERVER['DOCUMENT_ROOT']."/modules/itivos_slider/classes/itivos_slider_pages.php");
require_once($_SERVER['DOCUMENT_ROOT']."/modules/itivos_slider/classes/itivos_slider_pages_lang.php");
class ItivosSlider extends Modules
{	
    public $html;
	public function __construct()
	{
		$this->name ='itivos_slider';
		$this->displayName = $this->l('Slider');
        $this->description = $this->l('Carrusel de imagenes responsivo.');
		$this->category  ='front_office_features';
		$this->version ='1.0.2';
		$this->author ='Bernardo Fuentes';
		$this->versions_compliancy = array('min'=>'1.0', 'max'=> __SYSTEM_VERSION__);
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');
		$this->template_dir = __DIR_MODULES__."itivos_slider/views/back/";
		$this->template_dir_front = __DIR_MODULES__."itivos_slider/views/front/";
		parent::__construct();
		$this->key_module = "29d9469815377bb33ea3a002b9284e8b";
        $this->html = "";
	}
	public function install()
	{	
		if(!$this->registerHook("displayFrontHead") ||
		   !$this->registerHook("displayFrontAfterNav") ||
           !$this->registerHook("displayFrontBottom") ||
           !$this->registerHook("displayBottom") ||
		   !$this->registerHook("displayHead") ||
           !$this->defaultData() ||
           !$this->installTab("ItivosSlider", "Sliders", "ItivosSlider", null, "sliders", "aspect_ratio") ||
           !$this->installDb()
		   ) {
			return false;
		}		
		return true;
	}
	public function installDb()
	{
		$return = true;
        $return &= connect::execute('
                CREATE TABLE IF NOT EXISTS `'.__DB_PREFIX__.'itivos_slider_pages` (
				  `id` INT(11) NOT NULL AUTO_INCREMENT,
				  `created_by` varchar(450) NULL,
                  `created_date` datetime NULL DEFAULT CURRENT_TIMESTAMP,
                  `position` INT(5) NOT NULL,
                  `status` set("enabled", "disabled", "deleted") DEFAULT "enabled",
				  PRIMARY KEY (id)
				) ENGINE ='.__MYSQL_ENGINE__.' DEFAULT CHARSET=utf8 ;'
        	);
        $return &= connect::execute('
                CREATE TABLE IF NOT EXISTS `'.__DB_PREFIX__.'itivos_slider_pages_lang` (
				  `id` INT(11) NOT NULL AUTO_INCREMENT,
				  `slider_link` INT(11) NOT NULL,
				  `language_link` INT(5) NOT NULL,
				  `background` varchar(250) NULL,
				  `title` varchar(350) NULL,
				  `description` longtext NULL,
                  `call_to_action` varchar(250) DEFAULT NULL,
                  `new_windows` set("yes", "no") DEFAULT "no",
                  `text_position` set("left", "right", "center", "none") DEFAULT "none",
				  PRIMARY KEY (id)
				) ENGINE ='.__MYSQL_ENGINE__.' DEFAULT CHARSET=utf8 ;'
        	);
        return $return;
	}
	public function uninstallDB($drop_table = false)
    {   
        $return = true;
        if ($drop_table) {
            $return &= connect::execute("DELETE FROM ".__DB_PREFIX__. "configuration WHERE module = '".$this->name."'");
            $return &= connect::execute('DROP TABLE IF EXISTS ' . __DB_PREFIX__. 'itivos_slider_pages')
                    && connect::execute('DROP TABLE IF EXISTS ' . __DB_PREFIX__. 'itivos_slider_pages_lang');
        }
        return $return;
    }
	public function uninstall()
    {
        if(!$this->uninstallDB(true)) {
			return false;
		}
		return true;
    }
    public function defaultData()
    {
        Configuration::updateValue('slider_mode', 
                                   "slider",
                                   'itivos_slider');
        Configuration::updateValue('slider_speed', 
                                   "500",
                                   'itivos_slider');
        return true;
    }
    public function getConfig()
    {
    	$config_data = array();
        $current_link = $_SERVER["REQUEST_URI"];
        $params = explode("/", $current_link);
    	
        if (isset($_POST['submit_action'])) {
            self::updateConfig();
            $_SESSION['type_message'] = "success";
            $_SESSION['message'] = $this->l("Configuracion actualizada correctamente");
            header("Location: ".$current_link."");
        }
        $this->view->assign("module_name", $this->displayName);
        $this->view->assign("now", date("Y-m-d H:i:s"));
        if (isIsset('btnAdd')) {
            $helper = new HelperForm();
            $helper->tpl_vars = array(
                'fields_values' => array(),
                'languages' => language::getLangs(),
                'back_link' => array("label" => "Volver al listado", 
                                     "link" => "modules/config/".$this->name.""),
            );
            $helper->submit_action = "addAction";
            return $this->html .= $helper->renderForm(self::generateForm());
        }
        $helper = new HelperForm();
        $helper->tpl_vars = array(
            'fields_values' => self::getdataConfig(),
            'languages' => language::getLangs(),
        );
        $helper->submit_action = "saveConfig";
        $this->html .= $helper->renderForm(self::generateFormConfig());
    }
    public function getdataConfig()
    {
        $data_return = array('slider_mode' => Configuration::getValue('slider_mode'),
                             'slider_speed' => Configuration::getValue('slider_speed'),
                        );
        return $data_return;
    }
    public function updateConfig()
    {
        if (isIsset('slider_mode')) {
            Configuration::updateValue('slider_mode', 
                                       getValue('slider_mode'),
                                       'itivos_slider');
        }else {
            Configuration::updateValue('slider_mode', 
                                       "carousel",
                                       'itivos_slider');
        }
        Configuration::updateValue('slider_speed', 
                                   getValue('slider_speed'),
                                   'itivos_slider');
    }
    public function generateFormConfig()
    {
        $form = array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Configuración'),
                        'icon' => 'icon-cogs',
                    ),
                    'inputs' => array(
                        array(
                            'type' => 'switch',
                            'name' => 'slider_mode',
                            'desc' => $this->l('Cambia el diseño a Slider o Diapositivas'),
                            'values' => array(
                                array(
                                    'id' => 'active_off',
                                    'value' => "carousel",
                                    'label' => $this->l('Carousel')
                                ),
                                array(
                                    'id' => 'active_on',
                                    'value' => "slider",
                                    'label' => $this->l('Slider')
                                )
                            ),
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->l('VELOCIDAD'),
                            'desc' => $this->l("Velocidad entre imagenes (MILISEGUNDOS)"),
                            'name' => 'slider_speed',
                            'required' => true,
                        )
                    ),
                    'submit' => array(
                        'title' => $this->l('Guardar configuración'),
                    ),
                ),
            );
        return $form;
    }
    public function hookDisplayFrontAfterNav($params = null)
    {
        $sliders = Itivos_slider_pages::getSlidersByLang($this->lang);
        $this->view->assign(array("sliders" => $sliders,
                                  "config_sliders" => self::getdataConfig()
                                 )
        );
    	$this->view->display($this->template_dir_front."hookDisplayFrontAfterNav.tpl");
    }
    public function hookDisplayHead($params = null)
    {
        $this->addCSS($this->template_dir."css/itivos_slider.css");
    }
    public function hookDisplayFrontHead($params = null)
    {
    	$this->addCSS($this->template_dir_front."css/itivos_slider_front.css");
    }
    public function hookDisplayFrontBottom($params = null)
    {
    	$this->addJS($this->template_dir_front."js/itivos_slider_front.js");
    }
    public function hookDisplayBottom($params = null)
    {
        $this->addJS($this->template_dir."js/itivos_slider.js");
    }
}
<?php
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
		$this->version ='1.0.1';
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
        //$sliders = ItivosSliderClass::getSlidersByLang("es");
    	if (isset($_POST['action'])) {
            if ($_POST['action'] == "ajax") {
                switch ($_POST['resource']) {
                    case 'update_order':
                        Itivos_slider_pages::reOrderSlider($_POST['order']);
                        break;
                    case 'del':
                        Itivos_slider_pages::delSlider($_POST['id']);
                        $response = array('error' =>  false,
                                          'message' => "Slider borrado con exito.");
                        response($response);
                        die();
                        break;
                    default:
                        die();
                        break;
                }
            }
    	}else {
            if (isset($_POST['submit_action'])) {
                if ($_POST['submit_action'] != "saveConfig") {
                    self::postSave($_POST);
                    if ($_POST['submit_action'] == "updateAction") {
                        $this->html .= $this->displayConfirmation($this->l("Slider actualizado correctamente"));
                    }else {
                        $this->html .= $this->displayConfirmation($this->l("Slider agregado correctamente"));
                    }
                }
                if ($_POST['submit_action'] == "saveConfig") {
                    self::updateConfig();
                }
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
            if (!isIsset("id_slider")) {
                $helper = new HelperForm();
                $helper->tpl_vars = array(
                    'fields_values' => self::getdataConfig(),
                    'languages' => language::getLangs(),
                );
                $helper->submit_action = "saveConfig";
                $this->html .= $helper->renderForm(self::generateFormConfig());
                $this->view->assign("sliders", Itivos_slider_pages::getSlidersByLang());
            	$this->view->display($this->template_dir."list.tpl");
            }else {
                $slider_data = array();
                foreach ($this->languages as $key => $lang) {
                    $slider_data['langs'][$lang['id']] = itivos_slider_pages::getSlider(getValue('id_slider'), $lang['id']);
                }
                $helper = new HelperForm();
                $helper->tpl_vars = array(
                    'fields_values' => $slider_data,
                    'languages' => language::getLangs(),
                    'back_link' => array("label" => "Volver al listado", 
                                         "link" => "modules/config/".$this->name.""),
                );
                $helper->submit_action = "updateAction";
                return $this->html .= $helper->renderForm(self::generateForm());
            }
        }
    }
    public function postSave($params)
    {
        $slider_obj = New itivos_slider_pages();
        $langs = array();
        foreach ($this->languages as $key => $language) {
            $lang = $language['id'];
            $langs[$lang]['language_link'] = $lang;
            if (isset($params['title_'.$lang])) {
                $langs[$lang]['title'] = $params['title_'.$lang];
            }
            if (isset($params['description_'.$lang])) {
                $langs[$lang]['description'] = $params['description_'.$lang];
            }
            if (isset($params['call_to_action_'.$lang])) {
                $langs[$lang]['call_to_action'] = $params['call_to_action_'.$lang];
            }
            if (isset($params['new_windows'])) {
                $langs[$lang]['new_windows'] = $params['new_windows'];
            }
            if (isset($params['text_position_'.$lang])) {
                $langs[$lang]['text_position'] = $params['text_position_'.$lang];
            }

            $uri = array();
            if (isset($_FILES["background_".$lang]["name"])) {
                if ( !empty($_FILES["background_".$lang]["tmp_name"]) )  {
                    $upload = uploadFile($_FILES["background_".$lang]);
                    if ($upload['errors']==0) {
                        array_push($uri, $upload['url']);
                    }
                }
            }
            if (!empty($uri)) {
                $langs[$lang]['background'] = $uri[0];
            }
        }
        if (isIsset('id_slider')) {
            $slider_obj->id = getValue('id_slider');
        }
        $slider_obj->langs = $langs;
        $slider_obj->save();
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
    public function generateForm()
    {
        if (isIsset('id_slider')) {
            $title = $this->l('Actualizar carousel');
        }else {
            $title = $this->l('Agregar nuevo carousel');
        }
        $form = array(
                'form' => array(
                    'legend' => array(
                    'title' => $title,
                    'icon' => 'icon-cogs',
                    ),
                    'inputs' => array(
                        array(
                            'type' => 'file',
                            'label' => $this->l('IMAGEN'),
                            'name' => 'background',
                            'lang' => true,
                            'required' => true,
                            'desc' => $this->l("Archivo JPG, PNG Peso max:".$this->upload_max_size),
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->l('ENCABEZADO'),
                            'name' => 'title',
                            'lang' => true,
                            'required' => true,
                            'desc' => $this->l("Titulo par el slider"),
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->l('DESCRIPCIÓN'),
                            'name' => 'description',
                            'lang' => true,
                            'required' => true,
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->l('ENLACE'),
                            'name' => 'call_to_action',
                            'lang' => true,
                            'required' => true,
                            'desc' => $this->l("Destino del boton (Call to action)"),
                        ),
                        array(
                            'label' => $this->l('ABRE EN NUEVA VENTANA'),
                            'type' => 'switch',
                            'name' => 'new_windows',
                            'desc' => $this->l('Al dar clic en el call to action se abrirá en una nueva ventana'),
                            'values' => array(
                                array(
                                    'id' => 'active_off',
                                    'value' => "no",
                                    'label' => $this->l('no')
                                ),
                                array(
                                    'id' => 'active_on',
                                    'value' => "yes",
                                    'label' => $this->l('si')
                                )
                            ),
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Save'),
                    ),
                ),
            );
        if (isIsset("id_slider")) {
            unset($form['form']['inputs'][0]['required']);
        }
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
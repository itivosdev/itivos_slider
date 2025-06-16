<?php 
/**
 * @author Bernardo Fuentes
 * @since 24/04/2025
 */

class slidersController extends ModulesBackControllers
{
	function __construct()
	{
		$this->is_logged = true;
		$this->type_controller = "backend";
		parent::__construct();

		$this->view->assign('page', "Sliders");
	}
	public function index()
	{
		$this->html = '
		<div class="main_app_trans">
			<h3 class="h3_div">
				Listado de sliders
				<a href="'.__URI__.__ADMIN__.'/module/itivos_slider/sliders/show?btnAddSlider=1" class="right loading_full_screen_enable">
					<i class="material-icons edit_menu">
						add
					</i>
				</a>
			</h3>
		</div>
		';
        $this->view->assign(
        	array(
        		"sliders" => Itivos_slider_pages::getSlidersByLang(),
        	), 
        );
        $this->html .= $this->view->fetch($this->template_dir."list.tpl");
        $this->renderHTML("back");
	}
	public function show()
	{
		$this->html = 
        "
        <div class='menu_app'>
            <nav>
                <ul>
                    <li>
                        <a href='".__URI__.__ADMIN__."/module/itivos_slider/sliders' class='loading_full_screen_enable'>
                           <i class='material-icons'>arrow_left</i>
                            Volver atrás
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        ";
		if (isset($_POST['saveSlider'])) {
			self::postSave($_POST);
			if (isIsset('id_slider')) {
				header("Location: ".__URI__.__ADMIN__."/module/itivos_slider/sliders/show?id=".getValue('id_slider'));
			}else {
				header("Location: ".__URI__.__ADMIN__."/module/itivos_slider/sliders");
			}
		}
		$data = array();
		if (isIsset('id_slider')) {
	        foreach ($this->languages as $key => $lang) {
	            $data['langs'][$lang['id']] = itivos_slider_pages::getSlider(getValue('id_slider'), $lang['id']);
	        }
		}
		self::generateFormBasic($data);
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
                $langs[$lang]['background'] = imagenCreateWebp($uri[0]);
            }
        }
        if (isIsset('id_slider')) {
            $slider_obj->id = getValue('id_slider');
        }
        $slider_obj->langs = $langs;
        $slider_obj->save();
    }
	public function generateFormBasic($data)
    {
        if (isIsset('id_slider')) {
            $title = $this->l('Actualizar carousel');
        }else {
            $title = $this->l('Agregar nuevo carousel');
        }
        $this->form = array(
        	'form' => array(
        		"type" => "inline",
        		"method" => "POST",
        		"legend" => array(
        			"title" => $title,
        			"icon" => "icon-cogs",
        		),
        		"inputs" => array(
        			array(
        				"type" => "file",
        				"label" => $this->l("Imagen"),
        				"name" => "background",
        				"lang" => true,
        				"required" => true,
        				"desc" => $this->l("Archivo JPG, PNG Peso max:".$this->upload_max_size),
        				"files_type" => array("images"),
        			),
        			array(
        				"type" => "text",
        				"label" => $this->l("Title"),
        				"name" => "title",
        				"lang" => true,
        				"required" => true,
        				"desc" => $this->l("Title for the slider"),
        			),
        			array(
        				"type" => "textarea",
        				"label" => $this->l("Descriptión"),
        				"name" => "description",
        				"lang" => true,
        				"required" => true,
        				"rows" => 6,
        			),
        			array(
        				"type" => "text",
        				"label" => $this->l("Link"),
        				"name" => "call_to_action",
        				"lang" => true,
        				"required" => true,
        				"desc" => $this->l("Target (Call to action)"),
        			),
        			array(
        				"label" => $this->l("Open in a new window"),
        				"type" => "switch",
        				"name" => "new_windows",
        				"values" => array(
        					array(
        						"id" => "active_off",
        						"value" => "no",
        						"label" => $this->l("no")
        					),
        					array(
        						"id" => "active_on",
        						"value" => "yes",
        						"label" => $this->l("si")
        					)
        				),
        			),
        		),
        		"submit" => array(
        			"title" => $this->l("Guardar cambios"),
        			"action" => "saveSlider"
        		),
                "values" => $data,
        	),
        );
        if (isIsset("id_slider")) {
            unset($this->form['form']['inputs'][0]['required']);
        }
        $this->renderForm();
    }
    public function delete_slider()
    {
    	 if (isIsset('id_slider')) {
    	 	$id_slider = getValue('id_slider');
    	 }
    	 if (empty($id_slider)) {
    	 	$_SESSION['message'] = array(
    	 		"type_message" => "danger",
    	 		"message" => "No se ha recibido el id del slider",
    	 	);
    	 	header("Location: ".__URI__.__ADMIN__."/module/itivos_slider/sliders");
    	 	die();
    	 }
    	 if(Itivos_slider_pages::delSlider($id_slider)) {
    	 	$_SESSION['message'] = array(
    	 		"type_message" => "success",
    	 		"message" => "Se ha eliminado el slider correctamente",
    	 	);
    	 }else{
    	 	$_SESSION['message'] = array(
    	 		"type_message" => "danger",
    	 		"message" => "No se pudo borrar el slider",
    	 	);
    	 }
    	 header("Location: ".__URI__.__ADMIN__."/module/itivos_slider/sliders");
    	 die();
    }
    public function ajax()
    {
    	$resourse = getValue('resource'); 
        switch ($resourse) {
            case 'update_order':
                Itivos_slider_pages::reOrderSlider($_POST['order']);
                $response = array(
                	'error' =>  false,
                	'message' => "Orden de slider actualizado correctamente"
                );
                response($response);
                break;
            case 'del':
                die();
                break;
            default:
                die();
                break;
        }
    }
}
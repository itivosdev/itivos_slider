<?php
/**
 * 
 */
class itivos_slider_pages extends Model
{
	public $id;
	public $created_by;
	public $created_date;
	public $status;
	public $position;
	public $langs;

	function __construct($id = null)
	{
		if (isset($id) && $id !="-") {
			$data = self::getSlider($id);
			if (!empty($data)) {
				self::loadPropertyValues($data);
			}
		}
	}
	public static function getSlider($id, $id_lang = 1)
	{
		$query = "SELECT slider.id, slider.created_by, slider.created_date, 
						 slider_lang.language_link, slider_lang.background, 
						 slider_lang.title, slider_lang.description, 
						 slider_lang.call_to_action, slider_lang.new_windows, 
						 slider_lang.text_position
					FROM ".__DB_PREFIX__."itivos_slider_pages slider,	
						 ".__DB_PREFIX__."itivos_slider_pages_lang slider_lang
					WHERE slider.id = ".$id." AND 
						  slider_lang.slider_link = ".$id." AND 
						  slider_lang.language_link = ".$id_lang."
						  ";
		return connect::execute($query, "select", true);
	}
	public static function getSlidersByLang($iso_code = "es")
	{
		$id_lang = language::getIdLang($iso_code);
		/*
		$query = "SELECT slider.id, slider_lang.language_link, slider_lang.background,
       					 slider_lang.title, slider_lang.description, slider_lang.call_to_action,
       					 slider_lang.new_windows, slider_lang.text_position
					FROM ".__DB_PREFIX__."itivos_slider_pages slider,	
						 ".__DB_PREFIX__."itivos_slider_pages_lang slider_lang
					WHERE slider.id = slider_lang.slider_link  AND 
						  slider_lang.language_link = ".$id_lang." AND 
						  slider.status != 'deleted'
						  GROUP By slider.id
						  ";
						  */
		$query = "SELECT slider.id, slider_lang.language_link, slider_lang.background,
					     slider_lang.title, slider_lang.description, slider_lang.call_to_action,
					     slider_lang.new_windows, slider_lang.text_position
					FROM ".__DB_PREFIX__."itivos_slider_pages AS slider
					JOIN ".__DB_PREFIX__."itivos_slider_pages_lang AS slider_lang
					    ON slider.id = slider_lang.slider_link
					WHERE slider_lang.language_link = ".$id_lang."
					  AND slider.status != 'deleted'
					GROUP BY slider.id, slider_lang.language_link, slider_lang.background,
					         slider_lang.title, slider_lang.description, slider_lang.call_to_action,
					         slider_lang.new_windows, slider_lang.text_position
					ORDER by slider.position asc ";
		return connect::execute($query, "select");
	}
	public static function reOrderSlider($params)
    {
        foreach ($params as $key => $param) {
            self::setOrderSlider($param, $key);
        }
    }
    public static function setOrderSlider($id, $position)
    {
        $query = "UPDATE ".__DB_PREFIX__."itivos_slider_pages 
        				SET `position` = ".$position." 
        			WHERE id ='".$id."' ";
        return connect::execute($query);
    }
    public static function getNextPosition()
    {
        $data_return  = 1;
        $query = "SELECT count(id) as current_value FROM ".__DB_PREFIX__."itivos_slider_pages";
        $data_return = connect::execute($query, "select", true);
        if (!empty($data_return)) {
            $data_return =  (int) $data_return['current_value']+1;
        } 
        return $data_return;
    }
	public static function delSlider($id)
	{
		$query = "UPDATE ".__DB_PREFIX__."itivos_slider_pages 
					SET status = 'deleted' 
				  WHERE id = ".$id."";
		return connect::execute($query);
	}
	public function save()
	{
		if (empty($this->id)) {
			$langs = $this->langs;
			$this->langs = "";
			unset($this->langs);
			$this->position = self::getNextPosition();
			$full_name = $_SESSION['data_login']['data_login_employee']['firstname'];
		    $full_name .= " " .$_SESSION['data_login']['data_login_employee']['lastname'];
			$this->created_by = $full_name;

			$query = $this->makeQueryAdd();
			$id_slider = connect::execute($query, "insert");
			$this->id = $id_slider;
			foreach ($langs as $key => $lang) {
				$itivos_slider_pages_lang_obj = new itivos_slider_pages_lang();
				$itivos_slider_pages_lang_obj->loadPropertyValues($lang);
				$itivos_slider_pages_lang_obj->slider_link = $this->id;
				$itivos_slider_pages_lang_obj->save();
			}
			return $id_slider;
		}else {
			foreach ($this->langs as $key => $lang) {
				$itivos_slider_pages_lang_obj = new itivos_slider_pages_lang($this->id, $lang['language_link']);
				$itivos_slider_pages_lang_obj->loadPropertyValues($lang);
				$itivos_slider_pages_lang_obj->save();
			}
		}
	}
}
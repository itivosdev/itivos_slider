<?php 
/**
 * 
 */
class itivos_slider_pages_lang extends Model
{
	public $id; 
	public $slider_link;
	public $language_link;
	public $background;
	public $title;
	public $description;
	public $call_to_action;
	public $new_windows;
	public $text_position;
	function __construct($id = null, $id_lang = 1)
	{
		if (isset($id) && $id !="-") {
			$data = self::getSliderLangsByIdSlider($id, $id_lang);
			if (!empty($data)) {
				self::loadPropertyValues($data);
			}
		}
	}
	public static function getId($slider_link, $id_lang)
	{
		$query = "SELECT * 
					FROM ".__DB_PREFIX__."itivos_slider_pages_lang 
					WHERE slider_link = ".$slider_link." AND
						  language_link = ".$id_lang." 
					";
		$data = connect::execute($query, "select", true);
		if (!empty($data)) {
			return (int) $data['id'];
		}
		return false;
	}
	public static function getSliderLangsByIdSlider($id, $id_lang = 1)
	{
		$query = "SELECT * 
					FROM ".__DB_PREFIX__."itivos_slider_pages_lang 
					WHERE slider_link = ".$id." AND 
					      language_link = ".$id_lang."";
		return connect::execute($query, "select", true);
	}
}
<?php
/**
 * Created: Bernardo Fuentes Ch.
 * Description: Class for slider
 * Email: bfuentes@itivos.com
 * Twitter: @bfu3ntes
 */
class ItivosSliderClass
{
    public static function addSlider($params)
    {
        $firstname = $_SESSION['data_login']['data_login_employee']['firstname'];
        $lastname = $_SESSION['data_login']['data_login_employee']['lastname'];
        $created_by = $firstname ." ".$lastname;
        $query = "INSERT INTO ".__DB_PREFIX__."itivos_slider_pages(`created_by`) 
                                                          VALUES ('".$created_by."'
                                                                    )";
        $id = connect::execute($query,"insert");
        foreach ($params as $key => $param) {
            $params[$key]['slider_link'] = $id;
        }
        self::addOrder($id);
        self::addSliderContent($params);
        return $id;
    }
    public static function addSliderContent($params)
    {
        foreach ($params as $key => $param) {
            $query = "INSERT INTO ".__DB_PREFIX__."itivos_slider_pages_lang 
                            (`slider_link`, `language_link`, 
                             `background`, `title`, `description`, 
                             `call_to_action`, `status`) VALUES

                             (".$param['slider_link'].", ".$param['language_link'].",
                             '".$param['background']."', '".$param['title']."', '".$param['description']."'
                             '".$param['call_to_action']."', '".$param['status']."')";
                             echo $query;
                             die();
            connect::execute($query, "insert");
        }
        return true;
    }
    public static function updateSlider($params)
    {
        foreach ($params as $key => $param) {
            $where = " id = ".$param['id']."";
            unset($param['id']);
            unset($param['language_link']);
            $table_name = __DB_PREFIX__."itivos_slider_pages_lang";
            $query = self::makeQuerySlider($param, $where);
            connect::execute($query);
        }
    }
    public static function makeQuerySlider($params, $where)
    {
        $table_name = __DB_PREFIX__."itivos_slider_pages_lang";
        $query = "UPDATE $table_name SET ";
        $data_base_name = connect::getDataBaseName();
        $query_name = "SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE 
                                            `TABLE_SCHEMA`= '".$data_base_name."'  AND 
                                            `TABLE_NAME`='".$table_name."'";
        $field_name = connect::execute($query_name, "select");

        foreach ($field_name as $key => $field) {
            if (!array_key_exists($field['COLUMN_NAME'], $params)) {
                unset($field_name[$key]);
            }
        }

        $count = count($params);
        $i = 1;
        foreach ($field_name as $key => $field) {
            if (array_key_exists($field['COLUMN_NAME'], $params)) {
                $query = $query." {$field['COLUMN_NAME']} =";
                if (is_numeric($params[$field['COLUMN_NAME']])) {
                    if ($count == $i) {
                        $query = $query .$params[$field['COLUMN_NAME']]. "";
                    }else {
                        $query = $query .$params[$field['COLUMN_NAME']]. ", ";
                    }
                }else {
                    if ($count == $i) {
                        $query = $query." '{$params[$field['COLUMN_NAME']]}' ";
                    }else {
                        $query = $query." '{$params[$field['COLUMN_NAME']]}', ";
                    }
                }
            }
            $i = $i +1;
        }
        $query = $query . " WHERE " .$where;
        return $query;
    }
    public static function getSliders()
    {
        $sliders = array();
        $langs = language::getLangs();
        foreach ($langs as $key_lang => $lang) {
            $slider = self::getSliderByLang($lang["iso_language"]);
            if ( !empty($slider) ) {
                if (!array_key_exists($lang['id_language'], $sliders)) {
                    $sliders[$lang['id_language']] = array();
                    $sliders[$lang['id_language']] = $slider;
                }
            }
        }
        return $sliders;
    }
    public static function getSlider($slider_link)
    {   
        $sliders = array();
        $langs = language::getLangs();
        $query = "SELECT * FROM ".__DB_PREFIX__."itivos_slider_pages_lang WHERE 
                                    slider_link = ".$slider_link." GROUP by language_link";
        $consult = connect::execute($query,"select");
        if (!empty($consult)) {
             $langs = language::getLangs();
             foreach ($langs as $key_lang => $lang) {
                foreach ($consult as $key => $slider) {
                    if ($slider['language_link'] == $lang['id_language']) {
                        $sliders[$lang['id_language']] = array();
                        $sliders[$lang['id_language']] = $slider;
                    }
                }
             }
        }
        return $sliders;
    }
    public static function getSliderByLang($iso_code)
    {
        $id_lang = language::getIdLang($iso_code);
        $query = "SELECT * FROM ".__DB_PREFIX__."itivos_slider_pages_lang WHERE language_link = ".$id_lang."";
        $sliders = connect::execute($query, "select");
        $orders = self::getSliderOrder();
        $order_array = array();
        if (!empty($sliders)) {
            foreach ($orders as $key => $order) {
                foreach ($sliders as $key => $slider) {
                    if ($slider['slider_link'] == $order['slider_link']) {
                        $order_array[] = $slider;
                    }
                }
            }
        }
        if (!empty($order_array)) {
            $sliders = $order_array;
        }

        return $sliders;
    }
    public static function getNextPosition()
    {
        $data_return  = 0;
        $query = "SELECT count(id) as current_value from ".__DB_PREFIX__."itivos_slider_pages_order";
        $data_return = connect::execute($query, "select", true);
        if (!empty($data_return)) {
            $data_return =  (int) $data_return['current_value'];
        }
        return $data_return;
    }
    public static function reOrderSlider($params)
    {
        foreach ($params as $key => $param) {
            self::setOrderSlider($param, $key);
        }
    }
    public static function getSliderOrder()
    {
        $query = "SELECT * FROM ".__DB_PREFIX__."itivos_slider_pages_order ORDER BY ".__DB_PREFIX__."itivos_slider_pages_order.order ASC";
        return connect::execute($query);
    }
    public static function setOrderSlider($slider_link, $order)
    {
        $query = "UPDATE ".__DB_PREFIX__."itivos_slider_pages_order SET `order` = ".$order." WHERE slider_link ='".$slider_link."' ";
        return connect::execute($query);
    }
    public static function addOrder($slider_link)
    {
        $order = self::getNextPosition();
        $query = "INSERT INTO ".__DB_PREFIX__."itivos_slider_pages_order(`slider_link`, `order`) 
                                                          VALUES (".$slider_link.", ".$order." 
                                                                    )";
        return connect::execute($query);
    }
    public static function delSlider($id)
    {
        $query = "DELETE FROM ".__DB_PREFIX__."itivos_slider_pages WHERE id = ".$id."";
        connect::execute($query);
        $query = "DELETE FROM ".__DB_PREFIX__."itivos_slider_pages_lang WHERE slider_link = ".$id."";
        connect::execute($query);
        $query = "DELETE FROM ".__DB_PREFIX__."itivos_slider_pages_order WHERE slider_link = ".$id."";
        connect::execute($query);
        return true;
    }
}
<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * a:28:{i:0;b:0;s:4:"type";s:4:"flex";s:5:"width";s:4:"1185";s:6:"height";s:3:"400";s:6:"effect";s:4:"fade";s:5:"theme";s:7:"default";s:5:"links";s:4:"true";s:10:"navigation";s:4:"true";s:8:"autoPlay";s:4:"true";s:9:"smartCrop";s:4:"true";s:14:"carouselMargin";s:1:"5";s:10:"hoverPause";s:4:"true";s:5:"delay";s:4:"3000";s:14:"animationSpeed";s:3:"600";s:9:"direction";s:10:"horizontal";s:6:"easing";s:6:"linear";s:8:"prevText";s:1:"<";s:8:"nextText";s:1:">";s:8:"cssClass";s:0:"";s:8:"printCss";s:4:"true";s:7:"printJs";s:4:"true";s:10:"noConflict";s:4:"true";s:9:"fullWidth";s:5:"false";s:7:"reverse";s:5:"false";s:6:"random";s:5:"false";s:12:"smoothHeight";s:5:"false";s:6:"center";s:5:"false";s:12:"carouselMode";s:5:"false";}
 * a:28:{i:0;b:0;s:4:"type";s:4:"flex";s:5:"width";s:4:"1185";s:6:"height";s:3:"400";s:6:"effect";s:4:"fade";s:5:"theme";s:7:"default";s:5:"links";s:4:"true";s:10:"navigation";s:4:"true";s:8:"autoPlay";s:4:"true";s:9:"smartCrop";s:4:"true";s:14:"carouselMargin";s:1:"5";s:10:"hoverPause";s:4:"true";s:5:"delay";s:4:"3000";s:14:"animationSpeed";s:3:"600";s:9:"direction";s:10:"horizontal";s:6:"easing";s:6:"linear";s:8:"prevText";s:1:"<";s:8:"nextText";s:1:">";s:8:"cssClass";s:0:"";s:8:"printCss";s:4:"true";s:7:"printJs";s:4:"true";s:10:"noConflict";s:4:"true";s:9:"fullWidth";s:5:"false";s:7:"reverse";s:5:"false";s:6:"random";s:5:"false";s:12:"smoothHeight";s:5:"false";s:6:"center";s:5:"false";s:12:"carouselMode";s:5:"false";}
 */

/**
 * Description of MetaSlider
 *
 * @author Morettic LTDA
 */
class MetaSlider {

    //put your code here
    public static function makeSliders($property) {
        DB::debugMode();
        echo $property->idPropertyDB . "\n";
        $images = $property->property->images;
        //var_dump($images);
        $sliderMeta = new stdClass();
       //  $sliderMeta->sliderId = []
        //TOtal of images that the listing contais
        $totalImagens = count($images);
        //Get '_listing_slider' association between post and Slider
        $query = "select post_id from wp_postmeta where meta_key = '_listing_slider' and meta_value = " . $property->idPropertyDB;
        list($listingSliderID) = DB::queryFirstList($query);
        //Does not exist it.//First time import
        if (empty($listingSliderID)) {
            //Create Post and Post Meta
            //Create Term 
            //Create Term Taxonomy
            $date = date("Y-m-d H:m:s");
            DB::insert('wp_posts', array(
                'post_author' => 1, //default for all
                'post_date' => $date, //Just now its new
                'post_date_gmt' => $date, //just now its new
                'post_content' => ($property->property->nmPropertySite), //Get as String UTF 8
                'post_title' => ($property->property->nmPropertySite), //Get as String UTF 8
                'post_name' => ($property->property->nmPropertySite), //Get as String UTF 8
                'post_excerpt' => ($property->property->nmPropertySite), //Default Empty
                'post_status' => 'publish', //Publish online / Trash offline
                'comment_status' => 'closed', //Comment closed for all default
                'ping_status' => 'closed', //Ping status closed default for all
                'post_password' => '', //Post password empty 
                'to_ping' => '', //No need for it
                'pinged' => '', //No need for it
                'post_modified' => $date, //Just now
                'post_modified_gmt' => $date, //Just now
                'post_content_filtered' => '', //No need for 
                'post_parent' => $property->idPropertyDB, //Parent one
                'guid' => "https://ruteimoveis.com/?post_type=ml-slider&#038;p=" . $property->idPropertyDB, //Guid Url for Property
                'menu_order' => '0', //Default no need
                'post_type' => "ml-slider", //Slider Parent
                'post_mime_type' => "", //Default no need
                'comment_count' => $totalImagens                                          //Default no need
            ));
            $sliderMeta->sliderId = DB::insertId();
            //Insert term for IT
            DB::insert('wp_terms', array(
                'name' => $sliderMeta->sliderId, // auto incrementing column
                'slug' => $sliderMeta->sliderId,
                'term_group' => 0
            ));
            //Get ID
            $sliderMeta->sliderTermID = DB::insertId(); // which id did it choose?!? tell me!!
            //Insert Term Taxonomy
            DB::insert('wp_term_taxonomy', array(
                'term_id' => $sliderMeta->sliderTermID, // auto incrementing column
                'taxonomy' => 'ml-slider',
                'parent' => 0,
                'description' => 'Slider do post' . ($property->property->nmPropertySite),
                'count' => $totalImagens
            ));

            $sliderMeta->sliderTermTaxID = DB::insertId(); // which id did it choose?!? tell me!!
            //Insert Term Taxonomy
            DB::insert('wp_postmeta', array(
                'post_id' => $sliderMeta->sliderId, // auto incrementing column
                'meta_key' => '_listing_slider',
                'meta_value' => $property->idPropertyDB
            ));
            $sliderMeta->sliderPostMeta = DB::insertId(); // which id did it choose?!? tell me!!
            //Insert Slider COnfiguration
            DB::insert('wp_postmeta', array(
                'post_id' => $sliderMeta->sliderId, // auto incrementing column
                'meta_key' => 'ml-slider_settings',
                'meta_value' => 'a:35:{s:4:"type";s:4:"flex";s:6:"random";s:5:"false";s:8:"cssClass";s:0:"";s:8:"printCss";s:4:"true";s:7:"printJs";s:4:"true";s:5:"width";s:4:"1185";s:6:"height";s:3:"400";s:3:"spw";s:1:"7";s:3:"sph";s:1:"5";s:5:"delay";s:4:"3000";s:6:"sDelay";s:2:"30";s:7:"opacity";s:1:"0";s:10:"titleSpeed";s:3:"500";s:6:"effect";s:4:"fade";s:10:"navigation";s:4:"true";s:5:"links";s:4:"true";s:10:"hoverPause";s:4:"true";s:5:"theme";s:7:"default";s:9:"direction";s:10:"horizontal";s:7:"reverse";s:5:"false";s:14:"animationSpeed";s:3:"600";s:8:"prevText";s:1:"<";s:8:"nextText";s:1:">";s:6:"slices";s:2:"15";s:6:"center";s:4:"true";s:9:"smartCrop";s:4:"true";s:12:"carouselMode";s:5:"false";s:14:"carouselMargin";s:1:"5";s:6:"easing";s:6:"linear";s:8:"autoPlay";s:5:"true";s:11:"thumb_width";i:150;s:12:"thumb_height";i:100;s:9:"fullWidth";s:5:"true";s:10:"noConflict";s:5:"false";s:12:"smoothHeight";s:5:"false";}'
            ));
            //
            $sliderMeta->postMeta = array();
            $hasSpot = false;
            $sliderMeta->imgIds = [];
            $pos = 0;
            $sliderMeta->slidesRelation = [];
            foreach ($images as $img) {

                $imageNameForLike = str_replace("[", "", $img->nmFileName);
                $imageNameForLike = str_replace("]", "", $imageNameForLike);
             
                list($id) = DB::queryFirstList("SELECT ID FROM wp_posts where post_type = 'attachment' and guid like '%" . $imageNameForLike . "%'");

                if (!empty($id)) {
                    DB::insert('wp_posts', array(
                        'post_author' => 1, //default for all
                        'post_date' => $date, //Just now its new
                        'post_date_gmt' => $date, //just now its new
                        'post_content' => "", //Get as String UTF 8
                        'post_title' => ($property->property->nmPropertySite), //Get as String UTF 8
                        'post_name' =>  ($img->nmFileName), //Get as String UTF 8
                        'post_excerpt' => "", //Default Empty
                        'post_status' => 'publish', //Publish online / Trash offline
                        'comment_status' => 'closed', //Comment closed for all default
                        'ping_status' => 'closed', //Ping status closed default for all
                        'post_password' => '', //Post password empty 
                        'to_ping' => '', //No need for it
                        'pinged' => '', //No need for it
                        'post_modified' => $date, //Just now
                        'post_modified_gmt' => $date, //Just now
                        'post_content_filtered' => '', //No need for 
                        'post_parent' => $id, //Parent one
                        'guid' => "https://ruteimoveis.com/?post_type=ml-slider&#038;p=" . $property->idPropertyDB, //Guid Url for Property
                        'menu_order' => $pos++, //Default no need
                        'post_type' => "ml-slide", //Slider Parent
                        'post_mime_type' => "", //Default no need
                        'comment_count' => '0'                                          //Default no need
                    ));
                    $idSlide = DB::insertId();
                    $sliderMeta->slidesRelation[] = array(
                        'object_id' => $idSlide,
                        'term_taxonomy_id' => $sliderMeta->sliderTermTaxID,
                        'term_order' => 0
                    );
                    $sliderMeta->postMeta[] = array(
                        'post_id' => $idSlide,
                        'meta_key' => '_thumbnail_id',
                        'meta_value' => $id
                    );
                    $sliderMeta->postMeta[] = array(
                        'post_id' => $idSlide,
                        'meta_key' => '_wp_attachment_image_alt',
                        'meta_value' => ($property->property->nmPropertySite)
                    );
                    $sliderMeta->postMeta[] = array(
                        'post_id' => $idSlide,
                        'meta_key' => 'ml-slider_type',
                        'meta_value' => 'image'
                    );
                    $sliderMeta->postMeta[] = array(
                        'post_id' => $idSlide,
                        'meta_key' => 'ml-slider_crop_position',
                        'meta_value' => 'center-center'
                    );
                } else {
                    echo "Exists";
                }
            }
            DB::insert('wp_postmeta', $sliderMeta->postMeta);
            DB::insert('wp_term_relationships', $sliderMeta->slidesRelation);
        }




        /**

         * 
         *          */
        //copy('http://www.google.co.in/intl/en_com/images/srpr/logo1w.png', PATH_UPLOAD.'file.jpeg');
    }

}

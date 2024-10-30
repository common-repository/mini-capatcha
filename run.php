<?php
/*
Plugin Name: mini-capatcha
Plugin URI: http://www.asura.im/aaas/mini-capatcha
Description: mini-capatcha will protect your blog away from spam.
Version: 0.1
Author: walleve
Author URI: http://www.asura.im
*/

if(!class_exists('mini_capatcha')) {
    class mini_capatcha {
        function __construct() {
            add_action('comment_form', array(& $this, 'comment_capatcha'));
            add_filter('preprocess_comment', array(& $this, 'preprocess_comment'));
        }
        
        function plugin_url(){
            return get_option('home').'/wp-content/plugins/mini-capatcha';
        }

        function is_loggin(){
            return is_user_logged_in();
        }
                
        function comment_capatcha() {
            if(!$this->is_loggin()) {
                $post_rand = substr(md5(mt_rand(0,99999)),0,4);
                
                $str = '<div id="mini_capatcha">';
                $str .= '<script>function update_capatcha(){var capatcha = document.getElementById("capatcha-img");
                        var imgurl = "'.$this->plugin_url().'/mini-capatcha.php?id='.get_the_ID().'&p='.$post_rand.'";
                        capatcha.style.display="";
                        capatcha.src=imgurl;}</script>'
                    ;
                $str .= '<label for="mini-capatcha">Capatcha</label>';
                $str .= '<input type="hidden" value="'.$post_rand.'" name="sechash"/>';
                $str .= '<input type="text" name="capatchas" id="capatcha"  size="5" maxlength="4" tabindex="4" onfocus="update_capatcha();this.onfocus = null;" />';
                $str .= '<img id="capatcha-img" style="display:none" src="ss" alt="click to change" border="0" onclick="this.src=\''.$this->plugin_url().'/mini-capatcha.php?id='.get_the_ID().'&p='.$post_rand.'&update=\' + Math.random()" />';
                $str .= '</div>';
                
                echo $str;
            }
        }
        
        function preprocess_comment($commentdata) {
            if(!$this->is_loggin()){
                session_start();
                $_POST['comment_post_ID']?'':$_POST['comment_post_ID']=0;
                $post_rand = $_POST['sechash'];
                
                if (strtolower($_POST['capatchas']) != $_SESSION['capatcha_'.$_POST['comment_post_ID']][$post_rand] || empty($_SESSION['capatcha_'.$_POST['comment_post_ID']][$post_rand]) || empty($_POST['capatchas'])) {
                    wp_die( __('Error: Please enter valid capatcha word.') );
                }
                unset($_SESSION['capatcha_'.$_POST['comment_post_ID']][$post_rand]);
            }
            return $commentdata;
        }
        
        function sign_id($id){
            return md5(substr(md5($id),10,20));
        }
    }

}

if( !isset($mini_capatcha) ) {
	$mini_capatcha =& new mini_capatcha();
}
//end
?>

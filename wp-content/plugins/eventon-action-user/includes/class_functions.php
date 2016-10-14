<?php
/**
 * ActionUser front and back end functions
 * @version  1.9.1
 */
class evoau_functions{
	public function __construct($optau){
		global $eventon_au;
		$this->auopt = $optau;
	}

	// event manager related
		// Can a event be editable
			function can_edit_event($eventid, $epmv=''){
				if(!empty($epmv)){
					$editable = (!empty($epmv['evoau_disableEditing']) && $epmv['evoau_disableEditing'][0]=='yes')? false:true;
				}else{
					$editable = get_post_meta($eventid, 'evoau_disableEditing', true);
					$editable = (!empty($editable) && $editable=='yes')? false:true;
				}

				if($editable){ // if event says editable then check settings
					return (!empty($this->auopt['evo_auem_editing']) && $this->auopt['evo_auem_editing']=='yes')? true:false;
				}else{// if event says not editable
					return false;
				}
			}
		// Can a event be deletable
			function can_delete_event(){
				return (!empty($this->auopt['evo_auem_deleting']) && $this->auopt['evo_auem_deleting']=='yes')? true:false;
			}
		// trash event
			function trash_event($eid){
				return wp_trash_post($eid);
			}
		// get url with variables added
			public function get_custom_url($baseurl, $args){
				$str = '';
				foreach($args as $f=>$v){ $str .= $f.'='.$v. '&'; }
				if(strpos($baseurl, '?')!== false){
					return $baseurl.'&'.$str;
				}else{
					return $baseurl.'?'.$str;
				}
			}
		// language
			function get_lang($text, $lang='L1'){
				global $eventon_au;
				$lang = !empty($eventon_au->frontend->lang)?$eventon_au->frontend->lang: $lang ;
				return evo_lang($text, $lang, $eventon_au->frontend->evoau_opt_2);
			}
}
<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
class PurCategory {
	
	function __construct(){
		$this->options = get_option('pur_options');
		//keep for when implementing custom taxonomies.
		//$taxonomies = isset( $this->options['cpur_taxonomies'] ) && is_array($this->options['cpur_taxonomies']) ? $this->options['cpur_taxonomies'] : array('category') ;
		$taxonomies = array('category');
		if( is_array($taxonomies) && count($taxonomies)>0){
			//add_action('edit_category_form_fields',array(&$this,'edit_category_form_fields'));
			add_action('edit_term',array(&$this,'edit_term'),10,3);//change to better hook when available.		
			
			foreach( $taxonomies as $taxonomy ){
				add_action( $taxonomy . '_edit_form_fields', 	array(&$this,'edit_category_form_fields'), 10, 2);
			}
		}
	}
	
	function edit_term($term_id, $tt_id=null, $taxonomy=null){
		if(is_null($taxonomy))
			return;
		
		$pur_roles = get_option('pur-category-roles');
		$pur_roles = is_array($pur_roles)?$pur_roles:array();
		$pur_roles[$taxonomy][$term_id] = isset($_POST['category_roles'])&&is_array($_POST['category_roles'])?$_POST['category_roles']:array();
		update_option('pur-category-roles',$pur_roles);
	}
	
	function edit_category_form_fields( $o, $taxonomy='category' ){
		global $wp_roles;
		
		$pur_roles = get_option('pur-category-roles');
		$pur_roles = is_array($pur_roles)?$pur_roles:array();
		
		$roles = $wp_roles->get_names();	
		echo '<input type="hidden" name="pur-cat-nonce" id="pur-cat-nonce" value="' . wp_create_nonce( 'pur-cat-nonce' ) . '" />';
?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="pur_category_role"><?php _e('Restricted to user role'); ?></label></th>
			<td>
			<ul>
			<?php foreach($roles as $value => $label):$checked = isset($pur_roles[$o->taxonomy])&&isset($pur_roles[$o->taxonomy][$o->term_id])&&in_array($value,$pur_roles[$o->taxonomy][$o->term_id])?'checked="checked"':'';?>
			<li><span><input style="width:10px;" type="checkbox" <?php echo $checked ?> name="category_roles[]" value="<?php echo $value ?>" />&nbsp;<?php echo $label ?></span></li>
			<?php endforeach; ?>
			</ul>
			<br />
			<span class="description"><?php _e('<p>Leave blank in order <strong>not</strong> to restrict access to this part.<br />By checking one or more User Roles only the checked will have access to this part.</p>','pur'); ?></span></td>
		</tr>
<?php
	}
}

?>
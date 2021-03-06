<?php
/**
 * Menu Editing Module
 * This class will contain all the functionality of this module
 */

// Check if Better dashboard is activated
if (!class_exists('BDMenuEditing')) {

class BDMenuEditing extends MaterialWPModule {
  
  /**
   * Runs this module functionality
   */
  public function run() {
    
    // Add the edit, save and restore buttons to the admin menu
    add_action('adminmenu', array($this, 'addActionButtons'));
    
    // Add default order to the menu items
    add_filter('add_menu_classes', array($this, 'addMenuOrder'));
    
    // Adds our ajax action that serves
    add_action('wp_ajax_save_custom_menu_order', array($this, 'saveMenu'));
    
    // Restore our custom menu order
    add_action('wp_ajax_restore_menu_order', array($this, 'restoreMenu'));
    
    // Tell WordPress we're changing the menu order
    add_filter('custom_menu_order', '__return_true');

    // After everything is added by other plugins, change our order
    add_filter('menu_order', array($this, 'reorderMenus'), 99999999);
    
  } // end run;
  
  /**
   * Saves our menus via ajax
   */
  public function saveMenu() {

    // Get our passing variables so we can filter everything
    $menus = $_GET;
    
    // Unset the action param
    unset($menus['action']);
    
    // Save this new order to the users options
    $save = update_user_meta(get_current_user_id(), 'wpbd_menu', $menus['wpbd-menu']);
    
    // Send our results
    echo json_encode($save);
    
    // Kill execution
    exit;
  
  } // end saveMenu;
  
  /**
   * Deletes the saved menu setup for this user
   */
  public function restoreMenu() {
    
    // Lets remove the menu
    $delete = delete_user_meta(get_current_user_id(), 'wpbd_menu');
    
    // Send our results
    echo json_encode($delete);
    
    // Kill execution
    exit;
    
  } // end restoreMenu;
  
  /**
   * The function bellow handles the actual reordering of the menus for that user
   */
  public function reorderMenus($menu) {
    
    // We need to get our user save menu options
    $userMenu = get_user_meta(get_current_user_id(), 'wpbd_menu');
    
    // if it has nothing set, we just stop here
    if (!is_array($userMenu) || empty($userMenu)) return $menu;
    
    //var_dump($userMenu);
    return $userMenu[0];
    
  } // end reorderMenus;
  
  /**
   * Adds our actions menus
   */
  public function addActionButtons() {

    // render the view block
    $this->f->render('../modules/menu-editing/views/actions', array(
      'module' => $this,
    ));
    
  } // end addActionButtons;
  
  /**
   * Adds our actions menus
   */
  public function addMenuOrder($menu) {

    // Loop menus adding position
    foreach ($menu as $order => &$menuItem) {
      $menuItem[4] .= " wpbd-id-$menuItem[2]";
    } // end foreach;
    
    // return menu
    return $menu;
    
  } // end addActionButtons;
  
} // end BDMenuEditing;

/**
 * Actually turns our module on
 */
$BDMenuEditing = new BDMenuEditing($this);
  
}
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
    
    // var_dump($menus); die;
    
    // Save this new order to the users options
    $save  = update_user_meta(get_current_user_id(), 'wpbd_menu', $menus['wpbd-menu']);
    $save2 = update_user_meta(get_current_user_id(), 'wpbd_menu_edits', $menus['wpbd-menus-edits']);
    
    // Send our results
    echo json_encode($save && $save2);
    
    // Kill execution
    exit;
  
  } // end saveMenu;
  
  /**
   * Deletes the saved menu setup for this user
   */
  public function restoreMenu() {
    
    // Lets remove the menu
    $delete  = delete_user_meta(get_current_user_id(), 'wpbd_menu');
    $delete2 = delete_user_meta(get_current_user_id(), 'wpbd_menu_edits');
    
    // Send our results
    echo json_encode($delete && $delete);
    
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
    
    // Get additional info saved from our menus
    $menuInfos = get_user_meta(get_current_user_id(), 'wpbd_menu_edits');
    if (isset($menuInfos[0])) $menuInfos = $menuInfos[0];

    // Loop menus adding position
    foreach ($menu as $order => &$menuItem) {
      
      // Add control classes
      $menuItem[4] .= " wpbd-menu-item";
      $menuItem[4] .= " wpbd-id-$menuItem[2]";
      
      // var_dump($menuItem);
      // var_dump($menuInfos);
      
      // If the item is hidden, add a thir class
      if (isset($menuInfos[$menuItem[2]]) && $menuInfos[$menuItem[2]]['hidden'] == '1') {
        $menuItem[4] .= " wpbd-menu-hidden";
      }
      
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
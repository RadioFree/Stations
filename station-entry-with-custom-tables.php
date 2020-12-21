<?php
/*
Plugin Name: Station Entry With Custom Tables
Description: A plugin heavily based on Custom List Table With Db Example by Prashant Baldha that enables you to post stations and display them.
Version:     1.1.3
Author:      Vasileios Kechagias
Author URI:  https://kechablog.ml
License:     GPL2
Station Entry With Custom Tables is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Station Entry With Custom Tables is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Custom List Table With Database Example. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * PART 1. Defining Custom Database Table
 * ============================================================================
 *
 * In this part you are going to define custom database table,
 * create it, update, and fill with some dummy data
 *
 * http://codex.wordpress.org/Creating_Tables_with_Plugins
 *
 * In case your are developing and want to check plugin use:
 *
 * DROP TABLE IF EXISTS wp_cte;
 * DELETE FROM wp_options WHERE option_name = 'station_example_install_data';
 *
 * to drop table and option
 */
 function wpse_load_plugin_css() {
    $plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style( 'style1', $plugin_url . 'includes/css/style.css' );
}
add_action( 'wp_enqueue_scripts', 'wpse_load_plugin_css' );
 function wpse_load_plugin_scripts() {
    $plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_script( 'script1', $plugin_url . 'includes/js/scripts.js' );
	//wp_enqueue_script('jq','http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
}
add_action( 'wp_enqueue_scripts', 'wpse_load_plugin_scripts' );

/**
 * $station_example_db_version - holds current database version
 * and used on plugin update to sync database tables
 */
global $station_example_db_version;
$station_example_db_version = '1.1'; // version changed from 1.0 to 1.1

/**
 * register_activation_hook implementation
 *
 * will be called when user activates plugin first time
 * must create needed database tables
 */
function station_example_install()
{
    global $wpdb;
    global $station_example_db_version;

    $table_name = $wpdb->prefix . 'stations'; // do not forget about tables prefix

    // sql to create your table
    // NOTICE that:
    // 1. each field MUST be in separate line
    // 2. There must be two spaces between PRIMARY KEY and its name
    //    Like this: PRIMARY KEY[space][space](id)
    // otherwise dbDelta will not work
    $sql = "CREATE TABLE " . $table_name . " (
      id int(11) NOT NULL AUTO_INCREMENT,
      url tinytext NOT NULL,
      name VARCHAR(100) NOT NULL,
	  location VARCHAR(100) NOT NULL,
      PRIMARY KEY  (id)
    );";

    // we do not execute sql directly
    // we are calling dbDelta which cant migrate database
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // save current database version for later use (on upgrade)
    add_option('station_example_db_version', $station_example_db_version);

    /**
     * [OPTIONAL] Example of updating to 1.1 version
     *
     * If you develop new version of plugin
     * just increment $station_example_db_version variable
     * and add following block of code
     *
     * must be repeated for each new version
     * in version 1.1 we change email field
     * to contain 200 chars rather 100 in version 1.0
     * and again we are not executing sql
     * we are using dbDelta to migrate table changes
     */
    $installed_ver = get_option('station_example_db_version');
    if ($installed_ver != $station_example_db_version) {
        $sql = "CREATE TABLE " . $table_name . " (
          id int(11) NOT NULL AUTO_INCREMENT,
          url tinytext NOT NULL,
          name VARCHAR(200) NOT NULL,
		  location VARCHAR(100) NOT NULL,
          PRIMARY KEY  (id)
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // notice that we are updating option, rather than adding it
        update_option('station_example_db_version', $station_example_db_version);
    }
}

register_activation_hook(__FILE__, 'station_example_install');

/**
 * register_activation_hook implementation
 *
 * [OPTIONAL]
 * additional implementation of register_activation_hook
 * to insert some dummy data
 */
function station_example_install_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'stations'; // do not forget about tables prefix

    $wpdb->insert($table_name, array(
        'url' => 'test',
        'name' => 'test',
		'location' => 'test'
    ));
}

register_activation_hook(__FILE__, 'station_example_install_data');

/**
 * Trick to update plugin database, see docs
 */
function station_example_update_db_check()
{
    global $station_example_db_version;
    if (get_site_option('station_example_db_version') != $station_example_db_version) {
        station_example_install();
    }
}

add_action('plugins_loaded', 'station_example_update_db_check');

/**
 * PART 2. Defining Custom Table List
 * ============================================================================
 *
 * In this part you are going to define custom table list class,
 * that will display your database records in nice looking table
 *
 * http://codex.wordpress.org/Class_Reference/WP_List_Table
 * http://wordpress.org/extend/plugins/custom-list-table-example/
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Station_List_Table class that will display our custom table
 * records in nice table
 */
class Station_List_Table extends WP_List_Table
{
    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'station',
            'plural' => 'stations',
        ));
    }

    /**
     * [REQUIRED] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    /**
     * [OPTIONAL] this is example, how to render specific column
     *
     * method name must be like this: "column_[column_name]"
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_age($item)
    {
        return '<em>' . $item['age'] . '</em>';
    }

    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_name($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = array(
            'edit' => sprintf('<a href="?page=stations_form&id=%s">%s</a>', $item['id'], __('Edit', 'station_example')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'station_example')),
        );

        return sprintf('%s %s',
            $item['name'],
            $this->row_actions($actions)
        );
    }

    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     */
    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'name' => __('Station Name', 'station_example'),
            'location' => __('Station Location', 'station_example'),
        );
        return $columns;
    }

    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name' => array('name', true),
            'location' => array('location', false),
        );
        return $sortable_columns;
    }

    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part
     */
    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'stations'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'stations'; // do not forget about tables prefix

        $per_page = 5; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();
		
        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] - 1) * $per_page) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);
		
        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}

/**
 * PART 3. Admin page
 * ============================================================================
 *
 * In this part you are going to add admin page for custom table
 *
 * http://codex.wordpress.org/Administration_Menus
 */

/**
 * admin_menu hook implementation, will add pages to list persons and to add new one
 */
function station_example_admin_menu()
{
    //add_menu_page(__('Stations', 'station_example'), __('Stations', 'station_example'), 'activate_plugins', 'stations', 'station_example_stations_page_handler');
    add_submenu_page('radio-free', __('Stations', 'station_example'), __('Stations', 'station_example'), 'activate_plugins', 'stations', 'station_example_stations_page_handler');
    // add new will be described in next part
    add_submenu_page('radio-free', __('Add new Station', 'station_example'), __('Add new Station', 'station_example'), 'activate_plugins', 'stations_form', 'station_example_stations_form_page_handler');
}

add_action('admin_menu', 'station_example_admin_menu');

/**
 * List page handler
 *
 * This function renders our custom table
 * Notice how we display message about successfull deletion
 * Actualy this is very easy, and you can add as many features
 * as you want.
 *
 * Look into /wp-admin/includes/class-wp-*-list-table.php for examples
 */
function station_example_stations_page_handler()
{
    global $wpdb;

    $table = new Station_List_Table();
    $table->prepare_items();
    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted', 'station_example')) . '</p></div>';
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Stations', 'station_example')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=stations_form');?>"><?php _e('Add new', 'station_example')?></a>
    </h2>
    <?php echo $message; ?>

    <form id="persons-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}

/**
 * PART 4. Form for adding andor editing row
 * ============================================================================
 *
 * In this part you are going to add admin page for adding andor editing items
 * You cant put all form into this function, but in this example form will
 * be placed into meta box, and if you want you can split your form into
 * as many meta boxes as you want
 *
 * http://codex.wordpress.org/Data_Validation
 * http://codex.wordpress.org/Function_Reference/selected
 */

/**
 * Form page handler checks is there some data posted and tries to save it
 * Also it renders basic wrapper in which we are callin meta box render
 */
function station_example_stations_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'stations'; // do not forget about tables prefix

    $message = '';
    $notice = '';

    // this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'url' => '',
        'name' => '',
		'location' => '',
    );

    // here we are verifying does this request is post back and have correct nonce
    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = station_example_validate_person($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'station_example');
                } else {
                    $notice = __('There was an error while saving item', 'station_example');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'station_example');
                } else {
                    $notice = __('There was an error while updating item', 'station_example');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    }
    else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'station_example');
            }
        }
    }

    // here we adding our custom meta box
    add_meta_box('stations_form_meta_box', 'Station Data', 'station_example_stations_form_meta_box_handler', 'station', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Station', 'station_example')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=stations');?>"><?php _e('Back to list', 'station_example')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    <?php /* And here we call our custom meta box */ ?>
                    <?php do_meta_boxes('station', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'station_example')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}

/**
 * This function renders our custom meta box
 * $item is row
 *
 * @param $item
 */
function station_example_stations_form_meta_box_handler($item)
{
    ?>

<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="url"><?php _e('Station URL', 'station_example')?></label>
        </th>
        <td>
            <input id="url" name="url" type="text" style="width: 95%" value="<?php echo esc_attr($item['url'])?>"
                   size="50" class="code" placeholder="<?php _e('Station URL', 'station_example')?>" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="name"><?php _e('Station Name', 'station_example')?></label>
        </th>
        <td>
            <input id="name" name="name" type="text" style="width: 95%" value="<?php echo esc_attr($item['name'])?>"
                   size="50" class="code" placeholder="<?php _e('Station Name', 'station_example')?>" required>
        </td>
    </tr>
	<tr class="form-field">
        <th valign="top" scope="row">
            <label for="location"><?php _e('Station Location', 'station_example')?></label>
        </th>
        <td>
            <input id="location" name="location" type="text" style="width: 95%" value="<?php echo esc_attr($item['location'])?>"
                   size="50" class="code" placeholder="<?php _e('Station Location', 'station_example')?>" required>
        </td>
    </tr>
    </tbody>
</table>
<?php
}

/**
 * Simple function that validates data and retrieve bool on success
 * and error message(s) on error
 *
 * @param $item
 * @return bool|string
 */
function station_example_validate_person($item)
{
    $messages = array();

    if (empty($item['url'])) $messages[] = __('Station URL is required', 'station_example');
    if (empty($item['name'])) $messages[] = __('Station Name is required', 'station_example');
	if (empty($item['location'])) $messages[] = __('Station Location is required', 'station_example');
    //if(!empty($item['age']) && !absint(intval($item['age'])))  $messages[] = __('Age can not be less than zero');
    //if(!empty($item['age']) && !preg_match('/[0-9]+/', $item['age'])) $messages[] = __('Age must be number');
    //...

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}

/**
 * Do not forget about translating your plugin, use __('english string', 'your_uniq_plugin_name') to retrieve translated string
 * and _e('english string', 'your_uniq_plugin_name') to echo it
 * in this example plugin your_uniq_plugin_name == station_example
 *
 * to create translation file, use poedit FileNew catalog...
 * Fill name of project, add "." to path (ENSURE that it was added - must be in list)
 * and on last tab add "__" and "_e"
 *
 * Name your file like this: [my_plugin]-[ru_RU].po
 *
 * http://codex.wordpress.org/Writing_a_Plugin#Internationalizing_Your_Plugin
 * http://codex.wordpress.org/I18n_for_WordPress_Developers
 */
function station_example_languages()
{
    load_plugin_textdomain('station_example', false, dirname(plugin_basename(__FILE__)));
}

add_action('init', 'station_example_languages');

//Adds the shortcode
function station_shortcode() {
	ob_start();
	?>
	<div id="player">
		<div id="player_btn" onclick="play()" name="">
		</div>
		<div id="player_name">
		</div>
		
	</div>
	<?php
	global $wpdb;
	
	//If a station is already selected
	if(isset($_GET['station'])){
		$slug = $_GET['station'];
		$table_name = $wpdb->prefix . 'stations';
		$result = $result = $wpdb->get_results(
			$wpdb->prepare ( "
				SELECT * 
				FROM $table_name
					WHERE name = %d
			", $slug )
		);
		foreach ( $result as $station )
		{
			if($station->name == $slug){
			?>
				<div class="station_row">
					<div id="<?php echo $station->name; ?>_btn" class="play_btn" onclick="loadplayer('<?php echo $station->url;?>','<?php echo $station->name; ?>', '<?php echo $station->location; ?>')">
					</div>
					<div id="<?php echo $station->name; ?>_name" class="station_name">
						<p><?php echo $station->name; ?></p>
					</div>
					<div id="<?php echo $station->name; ?>_locat" class="station_location">
						<p><?php echo $station->location; ?></p>
					</div>
				</div>
			<?php
			}
		}
	}
	$table_name = $wpdb->prefix . 'stations';
		$result2 = $result = $wpdb->get_results(
			$wpdb->prepare ( "
				SELECT * 
				FROM $table_name" )
		);
		foreach ( $result2 as $station )
		{
			?>
			<div class="station_row">
				<div id="<?php echo $station->name; ?>_url" name="<?php echo $station->url; ?>"></div>
				<div id="<?php echo $station->name; ?>_btn" class="play_btn" onclick="loadplayer('<?php echo $station->url;?>','<?php echo $station->name; ?>', '<?php echo $station->location; ?>')">
				</div>
				<div id="<?php echo $station->name; ?>_name" class="station_name" name="<?php echo $station->name; ?>">
					<p><?php echo $station->name; ?></p>
				</div>
				<div id="<?php echo $station->name; ?>_locat" class="station_location" name="<?php echo $station->location; ?>">
					<p><?php echo $station->location; ?></p>
				</div>
			</div>
			<?php
		}
   //Render the page
	$content = ob_get_contents();
    ob_end_clean();
    return $content;
}
add_shortcode( 'station_list', 'station_shortcode' );
?>
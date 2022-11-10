<?php
/**
*Plugin Name: CMS Developer Test
*Description: Create a Plugin as a CMS Developer Test
*Version: 1.0.0
*License: GPL2
*Text Domain: test-project
*project Prefix: tp_
*/


add_action('init', 'tp_create_custom_posttypes'); // create custom post type of name Events of type "tp_events" on init hook
add_action( 'add_meta_boxes', 'tp_add_meta_boxes' ); // add metabox against my custom posttype
add_shortcode('Show_Events', 'tp_show_events_callback'); // create shortcode for showing events
add_action('admin_menu', 'tp_import_events_page'); // add menu page where import button is placed
add_action('save_post', 'tp_post_update_button'); // save additional information on update post
add_action('rest_api_init', function() { //create REST-API url which returns all upcoming events in json format 

    register_rest_route('tp', 'events', [
        'methods' => 'GET',
        'callback' => 'tp_all_events',
    ]);
    // json_api_url = 'yoursiteurl/wp-json/tp/events'
}); 




// callback function of init hook
function tp_create_custom_posttypes() {
    $event_defination_array = array(

        'name' => __('Events', 'Post Type General Name', 'test-project'),
        'singular_name' => __('Events', 'Post Type Singular Name', 'test-project'),
        'menu_name' => __('Events', 'test-project'),
        'name_admin_bar' => __('Admin Name', 'test-project'),
        'archives' => __('Archives', 'test-project'),
        'all_items' => __('All Events', 'test-project')

    );

    $event_assigning_array = array(

        'label' => __('Custom Post','test-project'),
        'description' => __('This is Custom Post type','test-project'),
        'labels' => $event_defination_array,
        'menu_icon' => 'dashicons-admin-site',
        'supports' => array('title','revisions','author'),
        'public' => true,
        'show_url' => true,
    );

    register_post_type('tp_events',$event_assigning_array);
}


// callback function of add meta box function
function tp_add_meta_boxes() {
    add_meta_box(
        'tp_event_entries_meta',
        __( 'Additional Information About Event', 'test-project' ),
        'tp_event_entries_callback',
        'tp_events'
    );
} 

//callback function of admin_menu hook
function tp_import_events_page() {
    add_menu_page('Import Events', 'Import Events', 'manage_options', 'import-events', 'tp_Import_Events_callback');
}

//callback function of add_menu_page function
function tp_Import_Events_callback() {
    if (isset($_POST['import_events'])) {
        $filepath = plugin_dir_url(__FILE__).'dataset.json';
        $getting_data_from_file = file_get_contents($filepath);
        $dataset = json_decode($getting_data_from_file, true);
        $update = 0;
        $insert = 0;
        for ($i=0; $i < sizeof($dataset) ; $i++) { 
            $check_array = array(
                'post_type' => 'tp_events',
                'post_parent' => $dataset[$i]['id'],
                'post_status' => 'draft',
                'fields' => 'ids'
            );
            $checking_whether_it_is_already_saved_or_not = get_posts($check_array);
            if (!empty($checking_whether_it_is_already_saved_or_not)) {
                $update++;
                $update_event = array(
                    'ID' => $checking_whether_it_is_already_saved_or_not[0],
                    'post_parent' => $dataset[$i]['id'],
                    'post_title' => $dataset[$i]['title'],
                    'post_excerpt' => $dataset[$i]['about'],
                    'post_date' => $dataset[$i]['timestamp']
                );
                wp_update_post( $update_event );
                update_post_meta($checking_whether_it_is_already_saved_or_not[0],'organizer',$dataset[$i]['organizer']);
                update_post_meta($checking_whether_it_is_already_saved_or_not[0],'email',$dataset[$i]['email']);
                update_post_meta($checking_whether_it_is_already_saved_or_not[0],'address',$dataset[$i]['address']);
                update_post_meta($checking_whether_it_is_already_saved_or_not[0],'latitude',$dataset[$i]['latitude']);
                update_post_meta($checking_whether_it_is_already_saved_or_not[0],'longitude',$dataset[$i]['longitude']);
            } else {
                $insert++;
                $import_event_array = array(
                    'post_type' => 'tp_events',
                    'post_parent' => $dataset[$i]['id'],
                    'post_title' => $dataset[$i]['title'],
                    'post_excerpt' => $dataset[$i]['about'],
                    'post_date' => $dataset[$i]['timestamp']
                );
                $event_id = wp_insert_post($import_event_array);
                update_post_meta($event_id,'organizer',$dataset[$i]['organizer']);
                update_post_meta($event_id,'email',$dataset[$i]['email']);
                update_post_meta($event_id,'address',$dataset[$i]['address']);
                update_post_meta($event_id,'latitude',$dataset[$i]['latitude']);
                update_post_meta($event_id,'longitude',$dataset[$i]['longitude']);
                wp_set_post_tags( $event_id, $dataset[$i]['tags']);
            }
        }
        $to      = 'logging@agentur-loop.com';
        $subject = 'Update About Import Events';
        $body    = '<h2>Update About Import Events</h2>';
        $body    .= '<p><b>Total Event Insert : </b>'.$insert.'</p>';
        $body    .= '<p><b>Total Event Update : </b>'.$update.'</p>';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail( $to, $subject, $body, $headers );
    }
    ?>
    <h2>Import Events</h2>
    <form method="post" action="">
        <input type="submit" name="import_events" id="import_events" value="Import Events">
    </form>
    <?php
}

// callback function of add_meta_box hook
function tp_event_entries_callback(){
    global $post;
    ?>
    <div id="tp_form_entries_div">
        <div> 
            <span class="ed_label">Organizer: </span>
            <input style="width: 70%;" type="text" name="tp_organizer" readonly="readonly" value="<?php echo get_post_meta($post->ID, 'organizer', true); ?>">
            <br>
        </div><br>
        <div> 
            <span class="ed_label">Email: </span>
            <input style="width: 70%;" type="text" name="tp_email" readonly="readonly" value="<?php echo get_post_meta($post->ID, 'email', true); ?>">
            <br>
        </div><br>
        <div> 
            <span class="ed_label">Address: </span>
            <input style="width: 70%;" type="text" name="tp_address" readonly="readonly" value="<?php echo get_post_meta($post->ID, 'address', true); ?>">
            <br>
        </div><br>
        <div> 
            <span class="ed_label">Latitude: </span>
            <input style="width: 70%;" type="text" name="tp_latitude" readonly="readonly" value="<?php echo get_post_meta($post->ID, 'latitude', true); ?>">
            <br>
        </div><br>
        <div> 
            <span class="ed_label">Longitude: </span>
            <input style="width: 70%;" type="text" name="tp_longitude" readonly="readonly" value="<?php echo get_post_meta($post->ID, 'longitude', true); ?>">
            <br>
        </div><br>
    </div>
    <?php
}

//callback of save post hook to save meta Fields
function tp_post_update_button($post_id) {
   
    $tp_organizer = $_POST['tp_organizer'];
    $tp_email = $_POST['tp_email'];
    $tp_address = $_POST['tp_address'];
    $tp_latitude = $_POST['tp_latitude'];
    $tp_longitude = $_POST['tp_longitude'];
    
    update_post_meta($post_id,'organizer',$tp_organizer);
    update_post_meta($post_id,'email',$tp_email);
    update_post_meta($post_id,'address',$tp_address);
    update_post_meta($post_id,'latitude',$tp_latitude);
    update_post_meta($post_id,'longitude',$tp_longitude);
}


//callback function of add_shortcode
function tp_show_events_callback() {

    $define_array = array(
        'posts_per_page' => -1,
        'post_type' => 'tp_events',
        'post_status' => 'draft',
        'orderby'   => 'post_date',
        'order' => 'ASC'
    );
    $getting_events = get_posts($define_array);
    ?>
    <h2>UpComing Events</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>About</th>
                <th>Event Date</th>
                <th>Time Left in event</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($getting_events as $event) {
                $date1=date_create(date('Y/m/d'));
                $date2=date_create($event->post_date);
                $diff=date_diff($date1,$date2);
                $find_future_days_left = $diff->format("%R");
                if ($find_future_days_left == '+') {
                    ?>
                    <tr>
                        <td><?php echo $event->post_parent; ?></td>
                        <td><?php echo $event->post_title; ?></td>
                        <td><?php echo $event->post_excerpt; ?></td>
                        <td><?php echo $event->post_date; ?></td>
                        <td><?php echo $diff->format("%a days"); ?></td>
                    </tr>
                    <?php
                }

            }

            ?>
        </tbody>
    </table>
    <?php
    
}

//callback of rest-api 
function tp_all_events() {
    $define_array = array(
        'posts_per_page' => -1,
        'post_type' => 'tp_events',
        'post_status' => 'draft',
        'orderby'   => 'post_date',
        'order' => 'ASC'
    );
    $getting_events = get_posts($define_array);

    $data = [];
    $i = 0;
    foreach($getting_events as $event) {
        $date1=date_create(date('Y/m/d'));
        $date2=date_create($event->post_date);
        $diff=date_diff($date1,$date2);
        $find_future_days_left = $diff->format("%R");
        if ($find_future_days_left == '+') {
            $data[$i]['id'] = $event->post_parent;
            $data[$i]['title'] = $event->post_title;
            $data[$i]['about'] = $event->post_excerpt;
            $data[$i]['timestamp'] = $event->post_date;
            $data[$i]['organizer'] = get_post_meta($event->ID,'organizer',true);
            $data[$i]['email'] = get_post_meta($event->ID,'email',true);
            $data[$i]['address'] = get_post_meta($event->ID,'address',true);
            $data[$i]['latitude'] = get_post_meta($event->ID,'latitude',true);
            $data[$i]['longitude'] = get_post_meta($event->ID,'longitude',true);
            $i++;
        }
    }
    return $data;
}

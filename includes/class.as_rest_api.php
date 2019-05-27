<?php
/**
 * The file that defines the rest api plugin class
 *
 * @since      1.0.0
 * @package    Test_Login
 */

class AS_REST_API
{
    /**
     * The namespace of the REST API endpoint.
     *
     * @since 1.0.0
     * @var string
     */
    var $my_namespace = 'accordion-slider/v';

    /**
     * The version of the REST API endpoint.
     *
     * @since 1.0.0
     * @access private
     * @var string
     */
    var $my_version   = '1';

    /**
     * Initialize the class and call action on REST API init.
     *
     * @since    1.0.0
     */
    public function __construct(){
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register route for login.
     *
     * @since    1.0.0
     */
    public function register_routes() {
        // Initialize the namespace that we're going to use for the REST API endpoint
        $namespace = $this->my_namespace . $this->my_version;

        // A route with a GET method to see all sliders
        register_rest_route( $namespace, '/all-sliders', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'all_sliders' ) ,
        ) );

        // A route with a POST method to add new slider
        register_rest_route( $namespace, '/add', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'add_slider' ) ,
        ) );

        // A route with a GET, POST and DELETE method to see, edit or delete a slider
        register_rest_route( $namespace, '/s/(?P<id>\d+)', array(
            'methods' => array(
                WP_REST_Server::READABLE,
                WP_REST_Server::CREATABLE,
                WP_REST_Server::DELETABLE,
            ),
            'callback' => array( $this, 'slider' ) ,
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            ),
        ));

        // A route with a DELETE method to delete a slider
        register_rest_route( $namespace, '/d/(?P<id>\d+)', array(
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => array( $this, 'delete_slider' ),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            ),
        ));
    }

    /**
     * A function that gets all sliders from the database and returns an object in JSON format
     *
     * @since 1.0.0
     * @return mixed|null|WP_REST_Response
     */
    function all_sliders() {
        global $wpdb;
        $query_sliders = $wpdb->prepare("SELECT slider_id, name, image_url, image_name FROM " . $wpdb->prefix . "as_slider ", array());
        $sliders = $wpdb->get_results($query_sliders);
        $data = array();
        foreach ($sliders as $slider) {
            $data[$slider->slider_id] = array(
                'slider_id' => $slider->slider_id,
                'name' => $slider->name,
                'images' => array()
            );
        }
        for ($i = 0; $i < sizeof($sliders); $i++ ) {
            if (array_key_exists($sliders[$i]->slider_id, $data)) {
                array_push($data[$sliders[$i]->slider_id]['images'], array(
                    'image_url' => $sliders[$i]->image_url,
                    'image_name' => $sliders[$i]->image_name
                ));
            }
        }
        if (sizeof($sliders) > 0) {
            return rest_ensure_response($data);
        }
        else
            return null;
    }

    /**
     * A function that adds a new slider with the info sent from the user via AJAX call
     *
     * @since 1.0.0
     * @param $request
     * @return mixed|WP_REST_Response
     */
    function add_slider($request) {
        global $wpdb;
        $table_name = $wpdb->prefix . "as_slider";

        $query_last_slider = $wpdb->prepare("SELECT slider_id FROM " . $wpdb->prefix . "as_slider ORDER BY id DESC LIMIT 1;", array());
        $last_slider_id = $wpdb->get_var($query_last_slider);

        $last_slider_id = $last_slider_id ? $last_slider_id : 0;

        $data = json_decode($request['slider']);

//        return rest_ensure_response($data);
        $insert = -1;
        if (sizeof($data->images) > 0) {
            foreach ($data->images as $item) {
                $insert = $wpdb->insert($table_name, array(
                    "name" => $data->name,
                    "slider_id" => $last_slider_id + 1,
                    "image_url" => $item->image_url,
                    "image_name" => $item->image_name
                ));
            }
        }
        else {
            $insert = $wpdb->insert($table_name, array(
                "name" => $data->name,
                "slider_id" => $last_slider_id + 1
            ));
        }

        $response = array(
            'items_added'   => sizeof($data->images),
            'wpdb_insert'   => $insert,
            'wpdb_error'    => $wpdb->last_error
        );

        return rest_ensure_response($response);
    }

    /**
     * A function for getting, editing or deleting a slider with id
     *
     * @since 1.0.0
     * @param $request
     * @return array|int|mixed|object
     */
    function slider($request) {
        global $wpdb;
        $table_name = $wpdb->prefix . "as_slider";
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $slider_id = $request['id'];
            $slider = json_decode($request['slider']);

            $delete = $wpdb->delete($table_name, array( 'slider_id' => $slider->slider_id));

            if ($delete > 0) {
                foreach($slider->images as $img) {
//                echo $img->image_url;
                    $inserted = $wpdb->insert($table_name, array(
                        "name" => $slider->name,
                        "slider_id" => $slider->slider_id,
                        "image_url" => $img->image_url,
                        "image_name" => $img->image_name
                    ));
                }
                return $inserted ? 1 : -1;
            }
            else
                return -1;
            return $slider_id;
        }
        else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $slider_id = $request['id'];
            $query_slider = $wpdb->prepare("SELECT * from $table_name WHERE slider_id=%d", array($slider_id));
            $slider_res = $wpdb->get_results($query_slider);
            if (!empty($slider_res)) {
                $slider = array(
                    'name' => $slider_res[0]->name,
                    'slider_id' => $slider_res[0]->slider_id,
                    'images' => array()
                );
                foreach ($slider_res as $s) {
                    array_push($slider['images'], array(
                        'image_name' => $s->image_name,
                        'image_url' => $s->image_url
                    ));
                }
                return $slider;
            }
            else
                return -1;
        }
        return -1;
    }

    /**
     * A function to delete a slider using the slider_id
     *
     * @since 1.0.0
     * @param $request
     * @return int
     */
    function delete_slider($request) {
        global $wpdb;

        $delete = $wpdb->delete($wpdb->prefix . 'as_slider', array( 'slider_id' => $request['id']));
        if ($delete > 0)
            return $request['id'];
        else
            return -1;
    }


}
?>
<?php
/**
 * The file that defines the admin plugin class
 *
 * @since      1.0.0
 * @package    Test_Login
 */

class AS_Admin {
    /**
     * The version of the plugin.
     *
     * @since 1.0.0
     * @access private
     * @var string
     */
    var $my_version   = '1';

    /**
     * Initialize the class and call action on AS_Admin init.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Initialize the hooks
        $this->hooks();
    }

    /**
     * Plugin hooks.
     *
     * @since 1.0.0
     */
    function hooks() {

        // Add action to the admin menu which will attach the plugin's menu page
        add_action('admin_menu', array( $this, 'accordion_slider' ) );
    }

    /**
     * Hooking the page in admin dashboard for the plugin with the function
     *
     * @since 1.0.0
     */
    function accordion_slider() {
        // Add main menu page Accordion Slider
        add_menu_page('Accordion Slider', 'Accordion Slider Settings', 'administrator', 'accordion-slider', array( $this, 'accordion_slider_settings' ), 'dashicons-admin-generic');

        // Add submenu page 'Add new slider' under the main page
        add_submenu_page('accordion-slider', 'Add new slider', 'Add new slider', 'administrator', 'as_add_slider', array( $this, 'as_edit_slider' ) );
    }

    /**
     * The main page for Accordion Slider in the admin dashboard
     *
     * @since 1.0.0
     */
    function accordion_slider_settings() {
        // Check if this function was called with an action
        if (isset($_GET['action'])) {
            // If the action was to edit a slider, call the display for editing
            if ($_GET['action'] == 'edit') {
                $this->as_edit_slider($_GET['slider_id']);
            }
        }
        else {
            // The HTML part of the page
            ?>
            <div id="tableSliders" class="wrap">
                <h1>All sliders</h1>

                <table class="widefat">
                    <thead>
                    <th class="column-slider-id">Slider id</th>
                    <th class="column-slider-name">Slider name</th>
                    <th class="column-no-images">No. images</th>
                    <th class="column-shortcode">Shortcode</th>
                    <th class="column-edit">Edit</th>
                    </thead>

                    <tfoot>
                    <th class="column-slider-id">Slider id</th>
                    <th class="column-slider-name">Slider name</th>
                    <th class="column-no-images">No. images</th>
                    <th class="column-shortcode">Shortcode</th>
                    <th class="column-edit">Edit</th>
                    </tfoot>

                    <tbody>
                    <tr v-for="slider in sliders">
                        <td>{{slider.slider_id}}</td>
                        <td>{{slider.name}}</td>
                        <td>{{Object.values(slider.images[0])[0] == null ? 0 : slider.images.length}}</td>
                        <td><input type='text' onClick='this.setSelectionRange(0, this.value.length)'
                                   :value="'[as slider_id=' + slider.slider_id + ']'" readonly></td>
                        <td>
                            <a :href="'?page=accordion-slider&action=edit&slider_id=' + slider.slider_id" ><span class='glyphicon glyphicon-pencil'></span>&nbsp;&nbsp;</a>
                            <a href="#" v-on:click="remove(slider)"><span class='glyphicon glyphicon-minus'></span></a>
                        </td>
                    </tr>
                    </tr>
                    </tbody>
                </table>
                <div v-if="show_message" :class="'notice is-dismissible ' + message_class"><p>{{message}}</p></div>
            </div>
            <?php
        }
    }

    /**
     * A function that is called when the user clicks to edit a certain slider
     *
     * The edit is controlled with Vue.js
     *
     * @param int $slider_id
     */
    function as_edit_slider($slider_id) {
//        print_r(isset($slider_id));
        $slider_id = $slider_id ? $slider_id : -1;
        ?>
        <div class="wrap">
            <h1>Edit slider</h1>
            <form action="#" method="POST" id="edit_slider">
                <input type="hidden" class="slider_id" value="<?php echo $slider_id; ?>">
                <table class="widefat">
                    <tbody>
                    <tr>
                        <td><label>Name:</label></td>
                        <td><input v-model="name"><div style="float:inherit" class="spinner is-active"></div></td>
                    </tr>
                    <tr>
                        <td><label>Images:</label></td>
                        <td>
                            <div id="display_imgs" class="display_images">
                                <div class="row" v-if="images.length > 0">
                                    <input type="text" name="list_length" :value="images.length" hidden>
                                    <div v-for="(item, key) in images" class="col-md-4">
                                        <input type="text" :value="item.image_url" v-bind:name="'item_' + key" hidden>
                                        <input type="text" :value="item.image_name" v-bind:name="'name_' + key" hidden>
                                        <img :src="item.image_url" alt="" class="col-md-12"/>
                                        <span class="col-md-12">{{item.image_name}}</span>
                                        <span class='glyphicon glyphicon-remove' v-on:click="remove(item)"></span>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td> <input type="button" class="chooseImage" value="Add Image"/></td>
                    </tr>
                    <tr>
                        <td>
                            <input v-if="action == 'edit' " v-on:click="save_edit()" type="button" value="Submit" class="btn btn-primary">
                            <button type="button" v-if="action == 'add'" v-on:click="add()" class="btn btn-primary">Submit</button>
                        </td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
                <div v-if="show_message" :class="'notice is-dismissible ' + message_class"><p>{{message}}</p></div>
            </form>
        </div>

        <?php
    }

}
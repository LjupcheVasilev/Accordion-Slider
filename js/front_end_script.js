/**
 * Javascript file with the function and objects used in the front-end
 */

// Configuration object with REST API endpoints
var config = {
    api: ACObject.home_url + '/wp-json/accordion-slider/v1/all-sliders',
    remove: ACObject.home_url + '/wp-json/accordion-slider/v1/d/',
    edit: ACObject.home_url + '/wp-json/accordion-slider/v1/s/'
};

/**
 * Vue.js object for the front end accordion slider
 */
var slider = new Vue({
    el: ".slider_container", // The HTML object
    data: {
        name: '',
        slider_id: -1,
        images: [],
        message: '',
        message_class: 'notice-info',
        show_message: false,
        items: 0,
        sizeBig: 0,
        parent_width: 0,
        hovered_suposed_width: 0,
        ul: 0,
        li: 0
    },
    methods: {
        get_slider: function (id) { // A function for getting the slider with certain id
            var self = this;
            // AJAX call to the REST API endpoint to get the slider's data
            jQuery.ajax({
                url: config.edit + id,
                method: 'GET'
            }).done(function (response) { // After tha call is done
                if (response != -1) { // If the function in the back-end was successful
                    // Remove the spinners and initialize the variables
                    jQuery('.spinner').removeClass('is-active');
                    jQuery('.display_images .row').show();
                    self.slider_id = response.slider_id;
                    self.name = response.name;

                    // Add each image to the images array
                    jQuery.each(response.images, function (index, value) {
                        var image_name = value['image_name'];
                        var image_url = value['image_url'];
                        var img = {
                            'image_name': image_name,
                            'image_url': image_url
                        };
                        self.images.push(img);
                    });
                    // How many images
                    self.items = self.images.length;
                }
                else { // If the function in the back-end was not successful
                    self.message = 'Something went wrong! Please try again.';
                    self.show_message = true;
                    self.message_class = 'notice-error';

                }
            }).error(function (error) { // Error connecting to the REST API endpoint
                console.log(error);
                self.message = "Error!";
                self.show_message = true;
                self.message_class = "notice-error";
            });
        },
        setVars: function() { // Set the initial variables for the slider
            ul_width = jQuery(this.ul).width() - 1;
            start_width = ul_width;
            jQuery('.as_image_container').unbind("mouseover");
            jQuery('.as_image_container').unbind("mouseout");
            start_width = parseInt(ul_width / this.li.length);
            jQuery(this.li).css({'width': start_width + 'px', 'height': '500px', 'background-size': 'cover'});
        },
        setHover: function() { // Set the hover function for the slider
            var self = this;
            jQuery('.as_image_container').each(function () {
                var $item = jQuery(this),
                    item_width;

                item_width = (jQuery(self.ul).width() - 1) * 0.45;
                console.log(jQuery(self.ul).width() - 1);
                $item.on({ // Set a function on mouseover event
                    'mouseover': function (event) {

                        self.li.find('img').css({'filter': 'brightness(80%)'});
                        if (self.li.length > 1) {
                            self.li.css({'width': ((ul_width - item_width) / (self.li.length - 1)) + 'px'});
                            $item.css({'width': item_width + 'px'});
                        }
                        $item.find('img').css({'filter': 'brightness(100%)'});
                    },
                    'mouseout': function (event) {
                        if (jQuery('.main-navigation').find("a:hover").length == 0) {
                            self.li.css({'width': start_width + 'px'});
                            jQuery(self.li).find('img').css({'filter': 'brightness(80%)'});
                        }
                    }
                });
            });
        }
    },
    mounted: function () { // When the Vue.js is mounted to the HTML element
        this.get_slider(jQuery('.slider_container').attr('slider'));
    },
    updated: function() { // When the Vue.js is updated
        this.li = jQuery('.as_image_container');
        this.ul = jQuery('.slider_container');
        this.setVars();
        this.setHover();
    }
});

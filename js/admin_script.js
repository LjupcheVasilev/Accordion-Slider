/**
 * Javascript file with the functions and object used for the back-end
 */

jQuery(document).ready(function () {

    // Initialize variables
    var media_uploader = null;
    var imgs = {};
    var url = window.location.href;


    if(url.indexOf('?page=as_add_slider') > -1 || url.indexOf('action=edit') > -1) { // Add or edit slider
        // Configuration object with API urls
        var config = {
            api: ACObject.home_url + '/wp-json/accordion-slider/v1/all-sliders',
            add: ACObject.home_url + '/wp-json/accordion-slider/v1/add',
            nonce: 'hiroy',
            remove:  ACObject.home_url + '/wp-json/accordion-slider/v1/d/',
            edit: ACObject.home_url + '/wp-json/accordion-slider/v1/s/'
        };

        // Slider object using Vue.js
        var slider = new Vue({
            el: "#edit_slider", // HTML element
            data: {
                name: '',
                slider_id: -1,
                images: [],
                message: '',
                message_class: 'notice-info',
                show_message: false,
                action: 'add'
            },
            methods: {
                remove: function (img) { // Remove image from slider
                    index = this.images.indexOf(img);
                    this.images.splice(index, 1);
                },
                get_slider: function (id) { // Get slider with id
                    var self = this;
                    // AJAX request to the REST API to get the slider
                    $.ajax({
                        url: config.edit + id,
                        method: 'GET'
                    }).done(function (response) {
                        if (response != -1) { // If the request was successful
                            jQuery('.spinner').removeClass('is-active');
                            jQuery('.display_images .row').show();

                            // Assign the slider in this object to the slider from the response
                            self.slider_id = response.slider_id;
                            self.name = response.name;

                            // Add all of the images to the images object
                            jQuery.each(response.images, function (index, value) {
                                var image_name = value['image_name'];
                                var image_url = value['image_url'];
                                var img = {
                                    'image_name': image_name,
                                    'image_url': image_url
                                };
                                self.images.push(img);
                            });
                        }
                        else {
                            // Show message if not successful
                            self.message = 'Something went wrong! Please try again.';
                            self.show_message = true;
                            self.message_class = 'notice-error';
                        }
                    }).error(function (error) { // Error with connecting to the API url
                        console.log(error);
                        self.message = "Error!";
                        self.show_message = true;
                        self.message_class = "notice-error";
                    });
                },
                save_edit: function () { // Save the edit
                    jQuery('.spinner').addClass('is-active');
                    var self = this;
                    if (this.name.trim().length === 0) {
                        jQuery('.spinner').removeClass('is-active');
                        self.show_message = true;
                        self.message = "Please enter name!";
                        self.message_class = "notice-error";
                    }
                    else if (this.name.trim().length > 50) {
                        jQuery('.spinner').removeClass('is-active');
                        self.show_message = true;
                        self.message = "Please enter a name smaller than 50 characters!";
                        self.message_class = "notice-error";
                    }
                    else if (this.images.length <= 2) {
                        jQuery('.spinner').removeClass('is-active');
                        self.show_message = true;
                        self.message = "Please add at least three images!";
                        self.message_class = "notice-error";
                    }
                    else {
                        $.ajax({ // AJAX call to the REST API to send the data
                            url: config.edit + self.slider_id,
                            method: 'POST',
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
                            },
                            data: {slider: JSON.stringify(self.$data)}
                        }).done(function (response) { // After the call is done
                            jQuery('.spinner').removeClass('is-active');
                            if (response > -1) { // If the updating was successful
                                self.message = 'Slider updated successfully.';
                                self.show_message = true;
                                self.message_class = 'notice-info';
                            }
                            else { // If the updating was successful
                                self.show_message = true;
                                self.message = "Error!";
                                self.message_class = "notice-error";
                            }
                        }).error(function (error) { // Error while connecting to the REST API endpoint
                            jQuery('.spinner').removeClass('is-active');
                            console.log(error);
                            self.message = "Error!";
                            self.show_message = true;
                            self.message_class = "notice-error";
                        });
                    }
                },
                add: function () { // Add slider to the database
                    jQuery('.spinner').addClass('is-active');
                    var self = this;
                    var dataToSend = {
                        'images': this.images,
                        'name'  : this.name
                    };
                    if (this.name.trim().length === 0) {
                        jQuery('.spinner').removeClass('is-active');
                        self.show_message = true;
                        self.message = "Please enter name!";
                        self.message_class = "notice-error";
                    }
                    else if (this.name.trim().length > 50) {
                        jQuery('.spinner').removeClass('is-active');
                        self.show_message = true;
                        self.message = "Please enter a name smaller than 50 characters!";
                        self.message_class = "notice-error";
                    }
                    else if (this.images.length <= 2) {
                        jQuery('.spinner').removeClass('is-active');
                        self.show_message = true;
                        self.message = "Please add at least three images!";
                        self.message_class = "notice-error";
                    }
                    else {
                        $.ajax({ // AJAX call to send the data to the REST API endpoint
                            url: config.add,
                            method: 'POST',
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
                            },
                            data: {slider: JSON.stringify(dataToSend)}
                        }).done(function (response) {
                            jQuery('.spinner').removeClass('is-active');
                            if (response.wpdb_insert) { // If the insert was successful
                                self.message = 'Slider added successfully.';
                                self.show_message = true;
                                self.message_class = 'notice-info';
                            }
                            else { // If the insert was not successful
                                self.show_message = true;
                                self.message = "Error! " + response.wpdb_error;
                                self.message_class = "notice-error";
                            }
                        }).error(function (error) { // Error connecting to the REST API endpoint
                            jQuery('.spinner').removeClass('is-active');
                            console.log(error);
                            self.message = "Error! " + error.errorMessage;
                            self.show_message = true;
                            self.message_class = "notice-error";
                        });
                    }
                }
            },
            mounted: function () { // A function that gets called when the object is mounted to the HTML element
                var id = jQuery('.slider_id').val();

                if (id != "-1") { // If the id is set, edit the slider with that id
                    this.get_slider(id);
                    this.action = 'edit';

                }
                else { // Leave everything empty
                    jQuery('.spinner').removeClass('is-active');
                }
            }
        });

        jQuery('.chooseImage').click(function () { // Assign the media uploader when the button is clicked
            open_media_uploader_image();
        });
    }


    /**
     * Function for opening the Wordpress media uploader and adding the images to the database
     */
    function open_media_uploader_image() {
        // Initialize the variables
        media_uploader = wp.media({
            frame: "post",
            state: "insert",
            multiple: true
        });

        var _images = imgs;
        var _sliki = slider;

        // Assign a function when the insert button is clicked
        media_uploader.on("insert", function () {
            // Get the images and the length of the images
            var length = media_uploader.state().get("selection").length;
            var images = media_uploader.state().get("selection").models;

            // For each image, add them to the Vue.js object
            for (var iii = 0; iii < length; iii++) {
                var image_url = typeof images[iii].changed.url === 'undefined' ? images[iii].attributes.url : images[iii].changed.url;
                var image_caption = typeof images[iii].changed.caption === 'undefined' ? images[iii].attributes.caption : images[iii].changed.caption;
                var image_title = typeof images[iii].changed.title === 'undefined' ? images[iii].attributes.title : images[iii].changed.title;
                var image = {id: iii, image_url: image_url, image_name: image_title, caption: image_caption};

                _images[iii] = image;

                _sliki.images.push(image);
            }

        });

        // Open the media uploader
        media_uploader.open();
    }

    if(window.location.href.indexOf('?page=accordion-slider') > -1 && window.location.href.indexOf('action=edit') == -1) { // If we are on the main accordion slider page
        // Configuration object with REST API endpoints
        var config = {
            api: ACObject.home_url + '/wp-json/accordion-slider/v1/all-sliders',
            nonce: 'hiroy',
            remove: ACObject.home_url + '/wp-json/accordion-slider/v1/d/'
        };

        // Vue.js object for all sliders
        var ex4 = new Vue({
            el: '#tableSliders', // Selector for HTML element
            data: {
                sliders: [],
                message: '',
                show_message: false,
                message_class: 'notice-info'
            },
            methods: {
                setData: function (data) { // Set the data to the sliders array
                    this.sliders = new Array();
                    var self = this;
                    $.each(data, function (key, item) {
                        self.sliders.push(item);
                    });
                },
                remove: function (el) { // Remove a slider
                    // Initialize the variables
                    var index = this.sliders.indexOf(el);
                    var self = this;

                    // AJAX call to the REST API endpoint for removing a slider
                    $.ajax( {
                        url: config.remove + el.slider_id,
                        method: 'DELETE',
                        beforeSend: function ( xhr ) {
                            xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
                        }
                    }).done( function ( response ) { // After the call is done
                        self.show_message = true;
                        if (response > -1) { // If the removing was successful
                            self.message = "Slider with ID=" + response + " has been removed";
                            self.sliders.splice(index,1);
                        }
                        else { // If the removing was not successful
                            self.message = "Error!";
                            self.message_class = "notice-error";
                        }
                    }).error(function (error) { // Error while connecting to the REST API endpoint
                        console.log(error);
                        self.message = "Error!";
                        self.show_message = true;
                        self.message_class = "notice-error";
                    });
                }
            }
        });
        // TODO: add this to the mounted function on the ex4 object
        // AJAX call to get all sliders from the REST API endpoint
        jQuery.get({
            url: config.api
        }).success( function(r) {
            var data = JSON.parse(JSON.stringify(r));
            jQuery("#tableSliders").show();
            ex4.setData(data);
        })
        .error(function (error) {
            console.log(error);
        });
    }

});
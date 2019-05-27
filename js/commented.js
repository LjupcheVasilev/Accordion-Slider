// }
// else {
//     var media_uploader = null;
//     var imgs = {};
//     var sliki = new Vue({
//         el: ".display_images",
//         data: {
//             images: []
//         },
//         methods: {
//             remove: function (id) {
//                 this.images.splice(id, 1);
//             },
//             itemName: function (key) {
//                 return "item-" + key;
//             }
//         }
//     });
// }
// var timeoutId;
//
// var $li = jQuery('.as_image_container'),
//     li_length,
//     start_width,
//     ul_width,
//     active,
//     $ul = jQuery('.slider_container');
//
// console.log($li, li_length);
// li_length = $li.length;
//
// function setVars() {
//     active = jQuery(window).width() < 1024;
//     console.log(active);
//     ul_width = $ul.width() - 1;
//     if (active) {
//         start_width = ul_width;
//         jQuery('.as_image_container').unbind("mouseover");
//         jQuery('.as_image_container').unbind("mouseout");
//     }
//     else
//         start_width = parseInt(ul_width / li_length);
//     console.log(ul_width + ' sdsd ' + start_width);
//     $li.css({'width': start_width + 'px'});
// }
//
// jQuery(window).on('resize', function () {
//     setVars();
//     setHover();
// });
// setVars();
// setHover();
// function setHover() {
//     console.log("hover");
//     jQuery('.as_image_container').each(function () {
//         if (!active) {
//             var $item = jQuery(this),
//                 item_width;
//
//             item_width = ul_width * 0.45;
//
//             $item.on({
//                 'mouseover': function (event) {
//                     console.log(item_width);
//                     // item_width += 200;
//                     $li.find('img').css({'filter': 'brightness(80%)'});
//                     $li.css({'width': ((ul_width - item_width) / (li_length - 1)) + 'px'});
//                     $item.find('img').css({'filter': 'brightness(100%)'});
//                     $item.css({'width': item_width + 'px'});
//                 },
//                 'mouseout': function (event) {
//                     if (jQuery('.main-navigation').find("a:hover").length == 0) {
//                         $li.css({'width': start_width + 'px'});
//                         jQuery($li).find('img').css({'filter': 'brightness(80%)'});
//                     }
//                 }
//             });
//         }
//     });
// }
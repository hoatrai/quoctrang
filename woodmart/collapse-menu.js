/*jQuery(document).ready(function($) {
    $('.wp-menu-name').on('click', function(e) {
        e.preventDefault(); // Prevent default link behavior

        // Check if menu is collapsed
        if ($('body').hasClass('folded')) {
            $('body').removeClass('folded'); // Expand
        } else {
            $('body').addClass('folded'); // Collapse
        }
    });
});*/
jQuery(document).ready(function($) {
    $('body').addClass('folded'); // Always collapse menu on load
});

<?php
/**
 * Plugin Name: Better Open Links in New Tab
 * Plugin URI: https://themeimpact.com
 * Description: This plugin will open all external links in new tab with excludes feature.
 * Version: 1.0.0
 * Author: ThemeImpact
 * Author URI: https://themeimpact.com
 * License: GPL2
 */


add_action( 'wp_head', 'themeimpact_initialize_links_in_new_tab' );
add_action( 'admin_menu', 'themeimpact_admin_menu' );

/**
 * Script that forces links to open in a new tab.
 *
 * @since 1.0.0
 *
 * @return void
 */
function themeimpact_initialize_links_in_new_tab() {

    //get current website domain.
    $current_domain = parse_url( get_option( 'home' ) );
    ?>
    <script type="text/javascript">
        //<![CDATA[
        function themeimpact_prepare_all_external_links() {

            if( !document.links ) {
                document.links = document.getElementsByTagName('a');
            }
            var all_links                = document.links;
            var open_in_new_tab          = false;
            var open_external_in_new_tab = '<?php echo trim( get_option( "themeimpact_open_external_link_in_new_tab", '' ) ) ?>';
            var open_internal_in_new_tab = '<?php echo trim( get_option( "themeimpact_open_internal_link_in_new_tab", '' ) ) ?>';

            // Set target for excluded links
            themeimpact_excludes_links_by_parent();
            // loop through all the links of current page.
            for( var current = 0; current < all_links.length; current++ ) {
                var current_link = all_links[current];
                open_in_new_tab  = false;

                //only work if current link does not have any onClick attribute.
                if( all_links[current].hasAttribute('onClick') == false ) {
                    if('yes' == open_internal_in_new_tab){
                        // open link in new tab if the web address starts with http or https, and refers to current domain.
                        if( (current_link.href.search(/^http/) != -1) && ((current_link.href.search('<?php echo esc_html( $current_domain['host'] ); ?>')) || (current_link.href.search(/^#/))) ){
                            open_in_new_tab = true;
                        }
                    }
                    if('yes' == open_external_in_new_tab){
                        // open link in new tab if the web address starts with http or https, but does not refer to current domain.
                        if( (current_link.href.search(/^http/) != -1) && (current_link.href.search('<?php echo esc_html( $current_domain['host'] ); ?>') == -1)  && (current_link.href.search(/^#/) == -1) ){
                            open_in_new_tab = true;
                        }
                    }
                    // Do not open link in new tab if target already set
                    if (current_link.hasAttribute('target')) {
                        open_in_new_tab = false;
                    }
                    //if open_in_new_tab is true, update onClick attribute of current link.
                    if( open_in_new_tab == true ){
                        //all_links[current].setAttribute( 'onClick', 'javascript:window.open(\''+current_link.href+'\'); return false;' );
                        all_links[current].setAttribute('target', '_blank');
                    }
                    //all_links[current].removeAttribute('target');
                }
            }

            
        }

        function themeimpact_excludes_links_by_parent() {
            // themeimpact_open_external_link_in_new_tab stored in textarea field
            <?php $excludes = trim(get_option('themeimpact_excludes', ''));
            $excludes = str_replace("\r\n", ',', $excludes);
            $excludes = str_replace("\n", ',', $excludes);
            $excludes = str_replace("\r", ',', $excludes);
            ?>

            var excludes = '<?php echo esc_attr($excludes); ?>';
            // Some default excludes
            var default_excludes = [
                'wpadminbar',
            ];
            // Merge default excludes with user defined
            if (excludes !== '') {
                excludes = excludes + ',' + default_excludes.join(',');
            } else {
                excludes = default_excludes.join(',');
            }

            if (excludes !== '') {
                var excludes_array = excludes.split(',');
                var all_links = document.links;

                for (var current = 0; current < all_links.length; current++) {
                    var current_link = all_links[current];
                    // Excludes links by parent element classes
                    for (var i = 0; i < excludes_array.length; i++) {
                        if (current_link.parentElement.classList.contains(excludes_array[i])) {
                            current_link.setAttribute('target', '_self');
                        }

                        // Travel up the DOM tree until meet body tag and check if parent element has class
                        var parent_element = current_link.parentElement;
                        while (parent_element.tagName != 'BODY') {
                            if (parent_element.classList.contains(excludes_array[i])) {
                                current_link.setAttribute('target', '_self');
                            }
                            // or id
                            if (parent_element.id == excludes_array[i]) {
                                current_link.setAttribute('target', '_self');
                            }
                            parent_element = parent_element.parentElement;
                        }

                        // Or ID
                        if (current_link.parentElement.id == excludes_array[i]) {
                            current_link.setAttribute('target', '_self');
                        }

                        // Exclude anchor links
                        if (current_link.getAttribute('href').search(/^#/) != -1) {
                            current_link.setAttribute('target', '_self');
                        }
                    }
                    

                    // Excludes links by href
                    for (var i = 0; i < excludes_array.length; i++) {
                        if (current_link.getAttribute('href').search(excludes_array[i]) != -1) {
                            current_link.setAttribute('target', '_self');
                        }
                    }
                }
            }
        }

        function themeimpact_load_external_links_in_new_tab( function_name ){
            var themeimpact_on_load = window.onload;

            if (typeof window.onload != 'function'){
                window.onload = function_name;
            } else {
                window.onload = function(){
                    themeimpact_on_load();
                    function_name();
                }
            }
        }

        themeimpact_load_external_links_in_new_tab( themeimpact_prepare_all_external_links );

    //]]>
    </script>
    <?php
}

/**
 * Add menu to admin menu.
 *
 * @since 1.0.0
 *
 * @return void
 */
function themeimpact_admin_menu() {
    add_options_page( esc_html__( 'Better Open links in new tab', "better-better-open-links-in-new-tab" ),
        esc_html__( 'Links In New Tab', "better-open-links-in-new-tab" ),
        'manage_options',
        'better-open-links-in-new-tab',
        'themeimpact_options_page' );
}

/**
 * Shows settings page for our plugin.
 *
 * @since 1.0.0
 *
 * @return void
 */
function themeimpact_options_page() {
    ?>
    <div class="themeimpact-wrap">
        <h2><?php echo esc_html__( "Open Links in New Tab", "better-open-links-in-new-tab" );?></h2>
        <p>
            <form method="post" action="options.php">
                <?php wp_nonce_field( 'update-options' );?>
                <?php echo esc_html__( "By default, all external links (i.e. links that point outside the current host name) will open in a new tab.", "better-open-links-in-new-tab" );?><br />
                <?php echo esc_html__( "You can change this feature by below options. You can also open internal links in new tab.", "better-open-links-in-new-tab" );?><br /><br />

                <input class="themeimpact-input input-text" name="themeimpact_open_external_link_in_new_tab" type="checkbox" id="themeimpact_open_external_link_in_new_tab" value="yes" <?php echo ('yes' === get_option( 'themeimpact_open_external_link_in_new_tab', '' )) ? 'checked' : ''; ?> />
                <label for="themeimpact_open_external_link_in_new_tab"><?php echo esc_html__('Open external links in new tab','better-open-links-in-new-tab');?></label><br>
                <input class="themeimpact-input input-text" name="themeimpact_open_internal_link_in_new_tab" type="checkbox" id="themeimpact_open_internal_link_in_new_tab" value="yes" <?php echo ('yes' === get_option( 'themeimpact_open_internal_link_in_new_tab', '' )) ? 'checked' : ''; ?> />
                <label for="themeimpact_open_internal_link_in_new_tab"><?php echo esc_html__('Open internal links in new tab','better-open-links-in-new-tab');?></label><br>

                <label for="themeimpact_excludes"><?php echo esc_html__('Exclude links by parent element classes, IDs or hrefs','better-open-links-in-new-tab');?></label><br>
                <textarea class="themeimpact-input input-text" name="themeimpact_excludes" id="themeimpact_excludes" rows="10" cols="50"><?php echo esc_html( get_option( 'themeimpact_excludes', '' ) ); ?></textarea><br>

                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="page_options" value="themeimpact_open_external_link_in_new_tab,themeimpact_open_internal_link_in_new_tab,themeimpact_excludes" />
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
            </form>
        </p>
    </div>
    <?php
}

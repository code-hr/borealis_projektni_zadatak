<?php
/*
Plugin Name: Brojač Pregleda Objava
Description: Borealis Projektni Zadatak
Version: 1.0
Author: Robert Matijevic
*/

// Kreiranje tablice 
register_activation_hook(__FILE__, 'nova_tablica');

function nova_tablica() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pregledi';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        datum date NOT NULL,
        pregledi int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY  (id),
        UNIQUE KEY post_datum (post_id, datum)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Učitaj CSS i JS
add_action('admin_enqueue_scripts', 'stilovi_skripte');

function stilovi_skripte() {
    wp_enqueue_style('brojac-pregleda-css', plugin_dir_url(__FILE__) . '/scss/brojac_pregleda.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('sortiranje-js', plugin_dir_url(__FILE__) . 'js/brojac_postova.js', ['jquery'], null, true);
}

// praćenje pregleda objava
add_action('wp_head', 'prati_preglede_objava');

function prati_preglede_objava() {
    if (is_single()) {
        global $wpdb;
        $id = get_the_ID();
        $datum = current_time('Y-m-d');
        $table_name = $wpdb->prefix . 'pregledi';

        $pregledi = $wpdb->get_var($wpdb->prepare(
            "SELECT pregledi FROM $table_name WHERE post_id = %d AND datum = %s",
            $id, $datum
        ));

        if ($pregledi !== null) {
            $wpdb->update(
                $table_name,
                ['pregledi' => $pregledi + 1],
                ['post_id' => $id, 'datum' => $datum]
            );
        } else {
            $wpdb->insert(
                $table_name,
                ['post_id' => $id, 'datum' => $datum, 'pregledi' => 1]
            );
        }
    }
}

// mmetabox za prikaz pregleda
add_action('add_meta_boxes', 'metabox_pregleda');

function metabox_pregleda() {
    add_meta_box(
        'brojac_pregleda',
        'Pregledi Objave',
        'prikazi_preglede',
        'post',
        'side',
        'high'
    );
}


// Prikaz pregleda
function prikazi_preglede($post) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pregledi';
    $trenutni_datum = current_time('Y-m-d');
    ?>
    <table class="pregledi-tablica">
        <thead>
            <tr>
                <th class="datum sortiraj active desc" data-sort="date">Datum</th>
                <th class="pregledi sortiraj asc" data-sort="views">Pregledi</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 0; $i < 14; $i++): 
                $datum = date('Y-m-d', strtotime("-$i days", strtotime($trenutni_datum)));
                $pregledi = $wpdb->get_var($wpdb->prepare(
                    "SELECT pregledi FROM $table_name WHERE post_id = %d AND datum = %s",
                    $post->ID, $datum
                )) ?: 0;

                $razina_citljivosti = $pregledi > 20 ? 'visoka' : ($pregledi > 10 ? 'srednja' : 'niska');
                ?>

                <tr class="<?php echo esc_attr($razina_citljivosti); ?>" <?php echo ($i >= 6 ? 'style="display:none;"' : ''); ?>>
                    <td><?php echo esc_html($datum); ?></td>
                    <td><?php echo esc_html($pregledi); ?> pregleda</td>
                </tr>

            <?php endfor; ?>
        </tbody>
    </table>

    <span id="toggle-rows" class="button">Prikaži više</span>

    <div class="opis-boja">
        <h4>Popularnost po danima - opis boja</h4>
        <div class="boje">
            <div class="boja niska">Niska = 0 - 10 pregleda<span></span></div>
            <div class="boja srednja">Srednja = 10 - 20 pregleda<span></span></div>
            <div class="boja visoka">Visoka = 20+ pregleda<span></span></div>
        </div>
    </div>

    <?php
}
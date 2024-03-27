<?php
/*
Plugin Name: WooCommerce Cost Price Field
Description: Adiciona um campo de "Preço de Custo" aos produtos no WooCommerce.
Version: 1.2
Author: Eduardo Souza
*/

// Adiciona o campo "Preço de Custo" aos produtos
function custom_add_cost_price_field() {
    global $post;
    $cost_price = get_post_meta($post->ID, 'cost_price', true);
    
    woocommerce_wp_text_input( array(
        'id' => 'cost_price',
        'label' => __('Preço de Custo', 'woocommerce'),
        'placeholder' => '',
        'desc_tip' => 'true',
        'description' => __('Insira o preço de custo do produto aqui.', 'woocommerce'),
        'value' => $cost_price, // Adiciona o valor atual do preço de custo
        'type' => 'number',
        'custom_attributes' => array(
            'step' => 'any',
            'min' => '0'
        )
    ));
}
add_action('woocommerce_product_options_pricing', 'custom_add_cost_price_field');

// Salva o valor do campo "Preço de Custo" ao salvar o produto
function custom_save_cost_price_field($product_id) {
    $cost_price = isset($_POST['cost_price']) ? wc_format_decimal($_POST['cost_price']) : '';
    // Substitui vírgula por ponto ou ponto por vírgula, se necessário
    $cost_price = str_replace(',', '.', $cost_price);
    update_post_meta($product_id, 'cost_price', $cost_price);
}
add_action('woocommerce_process_product_meta', 'custom_save_cost_price_field');

// Função para retornar o preço de custo com base no ID do produto
add_action('wp_ajax_get_cost_price', 'get_cost_price_callback');
add_action('wp_ajax_nopriv_get_cost_price', 'get_cost_price_callback');

function get_cost_price_callback() {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $cost_price = get_post_meta($product_id, 'cost_price', true);
    
    if ($cost_price) {
        echo $cost_price;
    } else {
        echo 'Preço de Custo não encontrado para o produto ID: ' . $product_id;
    }

    wp_die(); // Finaliza a execução do script
}

// Adiciona o script JavaScript para obter o preço de custo com base no ID do produto
function custom_add_js_to_admin() {
    ?>
    <script>
        jQuery(document).ready(function($) {
            // Função para obter o preço de custo com base no ID do produto
            function getCostPrice(productId) {
                $.ajax({
                    url: ajaxurl, // URL para o endpoint AJAX do WordPress
                    type: 'POST',
                    data: {
                        action: 'get_cost_price',
                        product_id: productId
                    },
                    success: function(response) {
                        console.log('Preço de Custo:', response);
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro ao obter o Preço de Custo:', error);
                    }
                });
            }

            // TEste
            // Chamar a função getCostPrice com o ID do produto desejado
            var productId = 13293; 
            getCostPrice(productId);
        });
    </script>
    <?php
}
add_action('admin_footer', 'custom_add_js_to_admin');
?>

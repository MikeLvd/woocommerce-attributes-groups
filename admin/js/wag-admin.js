(function ($) {
    'use strict';
    $(document).ready(function () {

        function check_group_element() {

            $("select.attribute_group option").each(function (index, element) {
                var group_id = $(element).val();
                if (group_id in wag_attributes_groups) {
                    var attributes = $('#product_attributes').find('.product_attributes');
                    var attributes_rows = $(attributes).find('.woocommerce_attribute');
                    var attributes_taxonomies = [];
                    var result = true;
                    var group_attributes = wag_attributes_groups[group_id];
                    $.each(attributes_rows, function (index, element) {
                        var tax = $(element).attr("data-taxonomy");
                        attributes_taxonomies.push(tax);
                    });

                    for (var j = 0; j < group_attributes.length; j++) {
                        var attribute = group_attributes[j];
                        if ($.inArray(attribute, attributes_taxonomies) < 0) {
                            result = false;
                            return;
                        }
                    }

                    if (result == false) {
                        $('select.attribute_group').find('option[value="' + group_id + '"]').prop("disabled", false);
                    } else {
                        $('select.attribute_group').find('option[value="' + group_id + '"]').prop("disabled", true);
                    }
                }
            });
        }

        check_group_element();


        function attribute_row_indexes() {
            $('.product_attributes .woocommerce_attribute').each(function (index, el) {
                $('.attribute_position', el).val(parseInt($(el).index('.product_attributes .woocommerce_attribute'), 10));
            });
        }

        // Add rows.
        $('#add_attribute_group').on('click', function () {
            var group_id = $('select.attribute_group').val();
            if (group_id in wag_attributes_groups) {
                var group_attributes = wag_attributes_groups[group_id];
                for (var j = 0; j < group_attributes.length; j++) {
                    var attribute = group_attributes[j];
                    var size = $('.product_attributes .woocommerce_attribute').length + j;
                    var wrapper = $(this).closest('#product_attributes');
                    var attributes = wrapper.find('.product_attributes');
                    var attributes_rows = $(attributes).find('.woocommerce_attribute');
                    var attributes_taxonomies = [];
                    $.each(attributes_rows, function (index, element) {
                        var tax = $(element).attr("data-taxonomy");
                        attributes_taxonomies.push(tax);
                    });

                    if ($.inArray(attribute, attributes_taxonomies) !== -1) {
                        return;
                    } else {
                        var product_type = $('select#product-type').val();
                        var data = {
                            action: 'woocommerce_add_attribute',
                            taxonomy: attribute,
                            i: size,
                            security: woocommerce_admin_meta_boxes.add_attribute_nonce
                        };
                        wrapper.block({
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        });
                        $.post(woocommerce_admin_meta_boxes.ajax_url, data, function (response) {
                            attributes.append(response);

                            if ('variable' !== product_type) {
                                attributes.find('.enable_variation').hide();
                            }

                            $(document.body).trigger('wc-enhanced-select-init');
                            attribute_row_indexes();
                            wrapper.unblock();
                            $(document.body).trigger('woocommerce_added_attribute');
                        });

                        if (attribute) {
                            $('select.attribute_taxonomy').find('option[value="' + attribute + '"]').attr('disabled', 'disabled');
                            $('select.attribute_taxonomy').val('');
                        }

                    }
                }

                $('select.attribute_group').find('option[value="' + group_id + '"]').prop("disabled", true);
                $('select.attribute_group').val('');

            }

        });


    });

})(jQuery);
<?php

/**
 * Outputs the settings fields
 * @param array $options Settings to output
 */
function o_admin_fields($options) {
    global $o_row_templates;
    ob_start();
    foreach ($options as $value) {
        if (!isset($value['type']))
            continue;
        if (!isset($value['id']))
            $value['id'] = '';
        if (!isset($value['name']))
            $value['name'] = $value['id'];
        if (!isset($value['hierarchy']))
            $value['hierarchy'] = array($value['name']);
        if (!isset($value['title']))
            $value['title'] = isset($value['name']) ? $value['name'] : '';
        if (!isset($value['class']))
            $value['class'] = '';
        if (!isset($value['row_class']))
            $value['row_class'] = '';
        if (!isset($value['css']))
            $value['css'] = '';
        if (!isset($value['row_css']))
            $value['row_css'] = '';
        if (!isset($value['default']))
            $value['default'] = '';
        if (!isset($value['desc']))
            $value['desc'] = '';
        if (!isset($value['desc_tip']))
            $value['desc_tip'] = false;
        if (!isset($value['ignore_desc_col']))
            $value['ignore_desc_col'] = false;
        if (!isset($value['label_class']))
            $value['label_class'] = "";
        $tip = "";
        if (isset($value["tip"]))
            $tip = "<span class='acd-info' data-tooltip-title='" . $value["tip"] . "'></span>";

        // Custom attribute handling
        $custom_attributes = array();

        if (!empty($value['custom_attributes']) && is_array($value['custom_attributes']))
            foreach ($value['custom_attributes'] as $attribute => $attribute_value) {
                $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';
            }

        $description = $value['desc'];

        if ($description && in_array($value['type'], array('textarea', 'radio'))) {
            $description = '<p style="margin-top:0">' . wp_kses_post($description) . '</p>';
        } elseif ($description && in_array($value['type'], array('checkbox'))) {
            $description = wp_kses_post($description);
        } elseif ($description) {
            $description = '<span class="description">' . wp_kses_post($description) . '</span>';
        }

        $post_id = get_the_ID();
        $option_value = "";
        $url_field_value = "";
        $raw_hierarchy = explodeX(array('[', ']'), $value["name"]);
        $hierarchy = array_filter($raw_hierarchy);
        $section_types = array("sectionbegin", "sectionend");
        $settings_table = get_proper_value($options[0], "table", "metas");
        
        if (!in_array($value["type"], $section_types) & !empty($hierarchy)) {
            $root_key = $hierarchy[0];
            $session_key = $root_key . "_$post_id";
            //We check if the meta is already stored in the session (db optimization) otherwise, we look for the original meta
            //$session=  get_proper_value($_SESSION, "o-data", array());
            $option_value = get_proper_value($_SESSION["o-data"], $session_key, false);


            if (!$option_value) {
                //Retrive from the metas
                if ($settings_table == "metas") {
                    $option_value = get_post_meta($post_id, $root_key, true);
//                    var_dump("metaextraction");
                    //var_dump("$root_key");
                    $_SESSION["o-data"][$session_key] = $option_value;
                    //var_dump($option_value);
                }
                //Retrive from the options
                else if ($settings_table == "options") {
                    $option_value = get_option($root_key);
                    $_SESSION["o-data"][$session_key] = $option_value;
                }
            }

            $i = 0;
            $prev = "";

            $session_key = $root_key . "_$post_id";
            $root_value = get_proper_value($_SESSION["o-data"], $session_key, false);
            if ($root_key != $value["name"])
                $option_value = o_find_in_array_by_key($root_value, $value["name"]);
        }
        if (!$option_value && $option_value !== "0")
            $option_value = $value['default'];


        if (!in_array($value["type"], $section_types) && !$value["ignore_desc_col"]) {
            ?>
            <tr style="<?php echo esc_attr($value['row_css']); ?>" class="<?php echo esc_attr($value['row_class']); ?>">
                <td class='label'>
                    <?php echo $value['title'] . $tip ?>
                    <div class='acd-desc'>
                        <?php echo $value['desc']; ?>
                    </div>
                </td>
                <?php
            }

            if (!in_array($value["type"], $section_types)) {
                if (isset($value["show_as_label"]))
                    echo "<label class='" . $value['label_class'] . "'>" . $value["title"] . $tip;
                else
                    echo "<td>";
            }
            // Switch based on type
            switch ($value['type']) {
                case 'sectionbegin':
                    //We start/reset the session
                    $_SESSION["o-data"] = array();
                    ?>
                <div class="o-wrap">
                    <div id="<?php echo $value["id"]; ?>" class="o-metabox-container">
                        <div class='block-form'>
                            <table class="wp-list-table widefat fixed pages o-root">
                                <tbody>
                                    <?php
                                    break;
                                case 'sectionend':
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php
                break;
            // Standard text inputs and subtypes like 'number'
            case 'text':
            case 'email':
            case 'number':
            case 'password' :

                $type = $value['type'];
                ?>

                <input
                    name="<?php echo esc_attr($value['name']); ?>"
                    id="<?php echo esc_attr($value['id']); ?>"
                    type="<?php echo esc_attr($type); ?>"
                    style="<?php echo esc_attr($value['css']); ?>"
                    value="<?php echo esc_attr($option_value); ?>"
                    class="<?php echo esc_attr($value['class']); ?>"
                    <?php echo implode(' ', $custom_attributes); ?>
                    />

                <?php
                break;

            case 'color' :
                $type = 'text';
                $value['class'] .= 'acd-color';
                ?>
                <div class="acd-color-container">
                    <input
                        name="<?php echo esc_attr($value['name']); ?>"
                        id="<?php echo esc_attr($value['id']); ?>"
                        type="<?php echo esc_attr($type); ?>"
                        style="<?php echo esc_attr($value['css']); ?>"
                        value="<?php echo esc_attr($option_value); ?>"
                        class="<?php echo esc_attr($value['class']); ?>"
                        <?php echo implode(' ', $custom_attributes); ?>
                        />
                    <span class="acd-color-btn"></span>
                </div>

                <?php
                break;

            case 'textarea':
                ?>

                <textarea
                    name="<?php echo esc_attr($value['name']); ?>"
                    id="<?php echo esc_attr($value['id']); ?>"
                    style="<?php echo esc_attr($value['css']); ?>"
                    class="<?php echo esc_attr($value['class']); ?>"
                    <?php echo implode(' ', $custom_attributes); ?>
                    ><?php echo esc_textarea($option_value); ?></textarea>

                <?php
                break;

            case 'texteditor':
                wp_editor($option_value, $value["id"], array(
                    'wpautop' => true,
                    'media_buttons' => false,
                    'textarea_name' => $value["name"],
                    'textarea_rows' => 10,
                    'false' => true
                ));
                break;

            case 'select' :
            case 'multiselect' :
            case 'post-type' :
                if ($value['type'] == 'post-type') {
                    //We make sure the limit is -1 if not set
                    $value["args"]["posts_per_page"]=  get_proper_value($value["args"], "posts_per_page", -1);
                    $posts = get_posts($value['args']);
                    $posts_ids=  get_proper_value($value, "first_value", array());
                    foreach ($posts as $post) {
                        $posts_ids[$post->ID] = $post->post_title;
                    }
                    $value['options'] = $posts_ids;
                }
                ?>

                <select
                    name="<?php echo esc_attr($value['name']); ?><?php if ($value['type'] == 'multiselect' || in_array('multiple="multiple"', $custom_attributes)) echo '[]'; ?>"
                    id="<?php echo esc_attr($value['id']); ?>"
                    style="<?php echo esc_attr($value['css']); ?>"
                    class="<?php echo esc_attr($value['class']); ?>"
                    <?php echo implode(' ', $custom_attributes); ?>
                    <?php if ($value['type'] == 'multiselect') echo 'multiple="multiple"'; ?>
                    >
                        <?php
                        foreach ($value['options'] as $key => $val) {
                            ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php
                    if (is_array($option_value))
                        selected(in_array($key, $option_value), true);
                    else
                        selected($option_value, $key);
                            ?>><?php echo $val ?></option>
                                <?php
                            }
                            ?>
                </select>

                <?php
                break;
            case 'groupedselect' :
                ?>
                                                                                                                                <!--<td class="forminp forminp-<?php // echo sanitize_title($value['type'])     ?>">-->
                <select
                    name="<?php echo esc_attr($value['name']); ?><?php if ($value['type'] == 'multiselect') echo '[]'; ?>"
                    id="<?php echo esc_attr($value['id']); ?>"
                    style="<?php echo esc_attr($value['css']); ?>"
                    class="<?php echo esc_attr($value['class']); ?>"
                    <?php echo implode(' ', $custom_attributes); ?>
                    <?php if ($value['type'] == 'multiselect') echo 'multiple="multiple"'; ?>
                    >
                        <?php
                        foreach ($value['options'] as $group => $group_values) {
                            ?><optgroup label="<?php echo $group; ?>"><?php
                        foreach ($group_values as $key => $val) {
                            ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php
                        if (is_array($option_value))
                            selected(in_array($key, $option_value), true);
                        else
                            selected($option_value, $key);
                            ?>><?php echo $val ?></option>
                                        <?php
                                    }
                                    ?></optgroup><?php
                        }
                        ?>
                </select> <?php echo $description; ?>
                <!--</td>-->
                <?php
                break;

            // Radio inputs
            case 'radio' :
                ?>                
                <fieldset>
                    <ul>
                        <?php
                        foreach ($value['options'] as $key => $val) {
                            ?>
                            <li>
                                <label><input
                                        name="<?php echo esc_attr($value['name']); ?>"
                                        value="<?php echo $key; ?>"
                                        type="radio"
                                        style="<?php echo esc_attr($value['css']); ?>"
                                        class="<?php echo esc_attr($value['class']); ?>"
                                        <?php echo implode(' ', $custom_attributes); ?>
                                        <?php checked($key, $option_value); ?>
                                        /> <?php echo $val ?></label>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </fieldset>                
                <?php
                break;

            case 'checkbox' :
                $visbility_class = array();

                if (!isset($value['hide_if_checked'])) {
                    $value['hide_if_checked'] = false;
                }
                if (!isset($value['show_if_checked'])) {
                    $value['show_if_checked'] = false;
                }
                if ($value['hide_if_checked'] == 'yes' || $value['show_if_checked'] == 'yes') {
                    $visbility_class[] = 'hidden_option';
                }
                if ($value['hide_if_checked'] == 'option') {
                    $visbility_class[] = 'hide_options_if_checked';
                }
                if ($value['show_if_checked'] == 'option') {
                    $visbility_class[] = 'show_options_if_checked';
                }

                if (!isset($value['checkboxgroup']) || 'start' == $value['checkboxgroup']) {
                    ?>
                    <fieldset>
                        <?php
                    } else {
                        ?>
                        <fieldset class="<?php echo esc_attr(implode(' ', $visbility_class)); ?>">
                            <?php
                        }

                        if (!empty($value['title'])) {
                            ?>
                            <legend class="screen-reader-text"><span><?php echo esc_html($value['title']) ?></span></legend>
                            <?php
                        }
                        $cb_value = get_proper_value($value, "value", false);
                        if (!$cb_value)
                            $cb_value = get_proper_value($value, "default", 1);
                        ?>
                        <label for="<?php echo $value['id'] ?>">
                            <input
                                name="<?php echo esc_attr($value['name']); ?>"
                                id="<?php echo esc_attr($value['id']); ?>"
                                type="checkbox"
                                value="<?php echo $cb_value; ?>"
                                <?php checked($option_value, $cb_value); ?>
                                <?php echo implode(' ', $custom_attributes); ?>
                                /> <?php echo $description ?>
                        </label> <?php echo $tip; ?>
                        <?php
                        if (!isset($value['checkboxgroup']) || 'end' == $value['checkboxgroup']) {
                            ?>
                        </fieldset>

                        <?php
                    } else {
                        ?>
                    </fieldset>
                    <?php
                }
                break;

            case 'image' :
                $set_btn_label = get_proper_value($value, "set", "Set image");
                $remove_btn_label = get_proper_value($value, "remove", "Remove image");

                $img_src = "";
                $root_img_src = o_get_proper_image_url($option_value, false);
                if ($root_img_src)
                    $img_src = home_url("/$root_img_src");
                ?>
                <div class="<?php echo $value["class"]; ?>">
                    <button class="button o-add-media"><?php echo $set_btn_label; ?></button>
                    <button class="button o-remove-media"><?php echo $remove_btn_label; ?></button>
                    <input type="hidden" name="<?php echo $value["name"]; ?>" value="<?php echo $root_img_src; ?>">
                    <?php
                    ?>
                    <div class="media-preview">
                        <?php
                        if (isset($option_value)) {
                            echo "<img src='$img_src'>";
                        }
                        ?>
                    </div>
                </div>

                <?php
                break;
            case 'file' :
                $set_btn_label = get_proper_value($value, "set", "Set file");
                $remove_btn_label = get_proper_value($value, "remove", "Remove file");

                ?>
                <div class="<?php echo $value["class"]; ?>">
                    <button class="button o-add-media"><?php echo $set_btn_label; ?></button>
                    <button class="button o-remove-media"><?php echo $remove_btn_label; ?></button>
                    <input type="hidden" name="<?php echo $value["name"]; ?>" value="<?php echo $option_value; ?>">
                    <?php
                    ?>
                    <div class="media-name">
                        <?php
                        if (isset($option_value)) {
                            echo basename($option_value);
                        }
                        ?>
                    </div>
                </div>

                <?php
                break;

            case 'date' :
                $type = 'date';
                $value['class'] .= 'o-date';
                ?>
                <div class="acd-date-container">
                    <input
                        name="<?php echo esc_attr($value['name']); ?>"
                        id="<?php echo esc_attr($value['id']); ?>"
                        type="<?php echo esc_attr($type); ?>"
                        style="<?php echo esc_attr($value['css']); ?>"
                        value="<?php echo esc_attr($option_value); ?>"
                        class="<?php echo esc_attr($value['class']); ?>"
                        <?php echo implode(' ', $custom_attributes); ?>
                        />
                    <!-- <span class="acd-date-btn"></span> -->
                </div>

                <?php
                break;

            case 'repeatable-fields' :
                if (!is_array($option_value))
                    $option_value = array();
                $value["popup"] = get_proper_value($value, "popup", false);

                if ($value["popup"]) {
                    add_thickbox();
                    $modal_id = uniqid("o-modal-");
                    echo "<a class='o-modal-trigger button button-primary button-large' data-toggle='o-modal' data-target='#$modal_id' data-modalid='$modal_id'>" . $value["popup_button"] . "</a>";
                    echo '<div class="omodal fade o-modal" id="' . $modal_id . '" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="omodal-dialog">
                                          <div class="omodal-content">
                                            <div class="omodal-header">
                                              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                              <h4 class="omodal-title" id="myModalLabel' . $modal_id . '">' . $value["popup_title"] . '</h4>
                                            </div>
                                            <div class="omodal-body">';
                    $value["class"].=" table-fixed-layout";
                }
                ?>

                <table id="<?php echo $value["id"]; ?>" class="<?php echo esc_attr($value['class']); ?> widefat repeatable-fields-table">
                    <thead>
                        <tr>
                            <?php
                            foreach ($value["fields"] as $field) {
                                $tip = "";
                                if (isset($field["tip"]))
                                    $tip = "<span class='acd-info' data-tooltip-title=\"" . $field["tip"] . "\"></span>";
                                echo "<td>" . $field["title"] . "$tip</td>";
                            }
                            ?>
                            <td style="width: 20px;"></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($option_value as $i => $row) {
                            echo "<tr class='" . $value['row_class'] . "'>";
                            foreach ($value["fields"] as $field) {
                                if (isset($row[$field["name"]]))
                                    $field_value = $row[$field["name"]];
                                else
                                    $field_value = "";
                                $field["name"] = $value["name"] . "[$i][" . $field["name"] . "]";
                                if ($field["type"] == "image" && isset($field["url_name"]))
                                    $field["url_name"] = $value["name"] . "[$i][" . $field["url_name"] . "]";
                                $field["default"] = $field_value;
                                $field["ignore_desc_col"] = true;

                                echo o_admin_fields(array($field));
                            }
                            ?>
                        <td>
                            <a class="remove-rf-row"></a>
                        </td>
                        <?php
                        echo "</tr>";
                    }
                    $row_tpl = get_row_template($value);
                    $row_tpl = preg_replace("/\r|\n/", "", $row_tpl);
                    $row_tpl = preg_replace('/\s+/', ' ', $row_tpl);
//                if(!isset($o_row_templates))
//                    $o_row_templates=array();
                    $tpl_id = uniqid();
                    $o_row_templates[$tpl_id] = $row_tpl;

                    $add_label = get_proper_value($value, "add_btn_label", __("Add", "vpc"));
                    ?>
                </tbody>
                </table>
                <a class="button mg-top add-rf-row" data-tpl="<?php echo $tpl_id; ?>"><?php echo $add_label; ?></a>

                <?php
                if ($value["popup"]) {
                    echo '</div>
                                          </div>
                                        </div>
                                      </div>';
                }
                break;

            case 'groupedfields':
                ?>

                <div class="o-wrap xl-gutter-8">
                    <?php
                    foreach ($value["fields"] as $field) {
                        $field["show_as_label"] = true;
                        $field["ignore_desc_col"] = true;
                        $field["table"] = $settings_table;
                        if (!isset($field["label_class"])) {
                            $nb_cols = count($value["fields"]);
//                            if($nb_cols>12)
//                                $nb_cols=12;
                            $field["label_class"] = "col xl-1-" . $nb_cols;
                        }
                        echo o_admin_fields(array($field));
                    }
                    ?>
                </div>

                <?php
                break;

            case 'custom':
                call_user_func($value["callback"]);
                break;
            case 'button' :
                ?>

                <a

                    id="<?php echo esc_attr($value['id']); ?>"
                    style="<?php echo esc_attr($value['css']); ?>"
                    value="<?php echo esc_attr($option_value); ?>"
                    class="<?php echo esc_attr($value['class']); ?>"
                    <?php echo implode(' ', $custom_attributes); ?>
                    ><?php echo esc_attr($value['title']); ?></a>

                <?php
                break;

            // Default: run an action
            default:
                do_action('o_admin_field_' . $value['type'], $value);
                break;
        }
        if (!in_array($value["type"], $section_types)) {
            if (isset($value["show_as_label"]))
                echo "</label>";
            else
                echo "</td>";
        }
        if (!in_array($value["type"], $section_types) && !$value["ignore_desc_col"]) {
            ?>
            </tr>
            <?php
        }
    }

    return ob_get_clean();
}

function get_row_template($value) {
    $row_tpl = "<tr class='o-rf-row'>";
    //ID unique permettant d'identifier de façon unique tous les indexes de ce template et de la remplacer tous ensemble en cas de besoin
    $index = uniqid();
    foreach ($value["fields"] as $field) {
        $field_tpl = $field;
//       ob_start();
        $field_tpl["name"] = $value["name"] . "[{" . $index . "}][" . $field_tpl["name"] . "]";
        $field_tpl["ignore_desc_col"] = true;
        $row_tpl.=o_admin_fields(array($field_tpl));
//       $row_tpl.=ob_get_clean();
    }
    //We add the remove button to the template
    $row_tpl.='<td><a class="remove-rf-row"></a></td></tr>';

    return $row_tpl;
}

/**
 * Get a value by key in an array if defined
 * @param array $values Array to search into
 * @param string $search_key Searched key
 * @param mixed $default_value Value if the key does not exist in the array
 * @return mixed
 */
function get_proper_value($values, $search_key, $default_value = "") {
    if (isset($values[$search_key]))
        $default_value = $values[$search_key];
    return $default_value;
}

function explodeX($delimiters, $string) {
    return explode(chr(1), str_replace($delimiters, chr(1), $string));
}

/**
 * Returns a media URL
 * @param type $media_id Media ID
 * @return type
 */
function get_media_url($media_id) {
    $attachment = wp_get_attachment_image_src($media_id, "full");
    $attachment_url = $attachment[0];
    return $attachment_url;
}

function o_find_in_array_by_key($root_value, $key) {
    $bracket_pos = strpos($key, "[");
    $usable_value_index = substr($key, $bracket_pos);
    $search = array("[", "]");
    $replace = array("['", "']");
    $usable_value_index2 = str_replace($search, $replace, $usable_value_index);
    
    //$expression = 'if (isset ($root_value' . $usable_value_index2 . ')) return $root_value' . $usable_value_index2 . '; else return false;';
    $expression = 'if (is_array($root_value)&&(isset ($root_value' . $usable_value_index2 . '))) return $root_value' . $usable_value_index2 . '; else return false;';
    
    $value = eval($expression);

    return $value;
}

function o_get_proper_image_url($suspected_link, $with_root = true) {
//    var_dump($suspected_link);
    if (empty($suspected_link))
        return $suspected_link;
    $img_src = $suspected_link;
    if (is_numeric($suspected_link)) {
        $raw_img_src = wp_get_attachment_url($suspected_link);
        $img_src = str_replace(home_url("/"), "", $raw_img_src);
    }
    $img_src = str_replace(home_url("/"), "", $img_src);
    //Code for bad https handling
    if(strpos(home_url("/"), "https")===FALSE)
    {
        $https_home=str_replace("http", "https", home_url("/"));
        $img_src = str_replace($https_home, "", $img_src);
    }

    if ($with_root)
        $img_src = home_url("/$img_src");
    return $img_src;
}

function o_startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function o_endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}
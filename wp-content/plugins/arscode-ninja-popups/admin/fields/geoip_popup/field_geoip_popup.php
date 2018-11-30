<?php

class SNP_NHP_Options_geoip_popup extends SNP_NHP_Options
{
    public function __construct($field = array(), $value ='', $parent)
    {
        parent::__construct($parent->sections, $parent->args, $parent->extra_tabs);

        $this->field = $field;
        $this->value = $value;
    }

    public function render()
    {
        $countryList = snp_get_countries();

        $class = (isset($this->field['class'])) ? $this->field['class'] : 'regular-text';

        echo '<ul id="'.$this->field['id'].'-ul">';

        if (isset($this->value) && is_array($this->value)) {
            foreach($this->value['country'] as $key => $value) {
                if ($value != '') {
                    echo '<li>';

                    echo '<div>';
                    echo '<select id="' . $this->field['id'] . '" name="' . $this->args['opt_name'] . '[' . $this->field['id'] . '][country][]" class="' . $class . '">';
                    foreach ($countryList as $countryListKey => $cl) {
                        echo '<option value="' . $cl['alpha2'] . '" ' . selected($value, $cl['alpha2'], false) . '>' . $cl['name'] . '</option>';
                    }
                    echo '</select>';
                    echo '</div>';

                    echo '<div>';
                    echo '<select id="' . $this->field['id'] . '" name="' . $this->args['opt_name'] . '[' . $this->field['id'] . '][popup][]" class="' . $class . '">';
                    foreach ($this->field['options'] as $k => $v) {
                        echo '<option value="' . $k . '" ' . selected($this->value['popup'][$key], $k, false) . '>' . $v . '</option>';
                    }
                    echo '</select>';
                    echo '</div>';

                    echo '<input type="button" class="nhp-opts-geoip-popup-remove button" value="' . __('Remove', 'nhp-opts') . '" />';
                    echo '</li>';
                }
            }
        } else {
            echo '<li>';

            echo '<div>';
            echo '<select id="' . $this->field['id'] . '" name="'.$this->args['opt_name'].'['.$this->field['id'].'][country][]" class="'.$class.'">';

            foreach($countryList as $key => $cl) {
                echo '<option value="' . $cl['alpha2'] . '">' . $cl['name'] . '</option>';
            }
            echo '</select>';
            echo '</div>';

            echo '<div>';
            echo '<select id="' . $this->field['id'] . '" name="'.$this->args['opt_name'].'['.$this->field['id'].'][popup][]" class="'.$class.'">';
            foreach($this->field['options'] as $k => $v) {
                echo '<option value="' . $k . '">' . $v . '</option>';
            }
            echo '</select>';
            echo '</div>';

            echo '<input type="button" class="nhp-opts-geoip-popup-remove button" value="'.__('Remove', 'nhp-opts').'" />';
            echo '</li>';
        }

        echo '<li style="display:none;">';

        echo '<div>';
        echo '<select id="' . $this->field['id'] . '" name="'.$this->args['opt_name'].'['.$this->field['id'].'][country][]" class="'.$class.'" placeholder="Country">';
        echo '<option value=""></option>';
        foreach ($countryList as $countryListKey => $cl) {
            echo '<option value="' . $cl['alpha2'] . '">' . $cl['name'] . '</option>';
        }
        echo '</select>';
        echo '</div>';

        echo '<div>';
        echo '<select id="' . $this->field['id'] . '" name="'.$this->args['opt_name'].'['.$this->field['id'].'][popup][]" class="'.$class.'" placeholder="Pop-up">';
        echo '<option value=""></option>';
        foreach($this->field['options'] as $k => $v) {
            echo '<option value="' . $k . '">' . $v . '</option>';
        }
        echo '</select>';
        echo '</div>';

        echo '<input type="button" class="nhp-opts-geoip-popup-remove button" value="'.__('Remove', 'nhp-opts').'" />';
        echo '</li>';

        echo '</ul>';

        echo '<input type="button" class="nhp-opts-geoip-popup-add button" rel-id="'.$this->field['id'].'-ul" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][]" value="'.__('Add More', 'nhp-opts').'" />';
        echo '<br/>';

        echo (isset($this->field['desc']) && !empty($this->field['desc']))?' <span class="description">'.$this->field['desc'].'</span>':'';
    }

    public function enqueue()
    {
        wp_enqueue_script('nhp-opts-field-geoip-popup-js', SNP_NHP_OPTIONS_URL.'fields/geoip_popup/field_geoip_popup.js', array('jquery'), time(), true);
    }
}
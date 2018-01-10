<?php
N2Loader::import('libraries.form.element.number');

class N2ElementNumberAutocompleteSlider extends N2ElementNumber {

    function fetchElement() {
        $html = parent::fetchElement();
        N2JS::addInline('new NextendElementAutocompleteSlider("' . $this->_id . '", ' . json_encode(array(
                'min'  => floatval(N2XmlHelper::getAttribute($this->_xml, 'min')),
                'max'  => floatval(N2XmlHelper::getAttribute($this->_xml, 'max')),
                'step' => floatval(N2XmlHelper::getAttribute($this->_xml, 'step'))
            )) . ');');
        return $html;
    }

    protected function getClass() {
        return 'n2-form-element-autocomplete ui-front ' . parent::getClass();
    }
}
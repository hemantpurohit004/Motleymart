<?php

N2Loader::import("libraries.slider.abstract", "smartslider");
N2Loader::import("models.Slides", "smartslider");

class N2SmartsliderSlidersModel extends N2Model {

    public function __construct() {
        parent::__construct("nextend2_smartslider3_sliders");
    }

    public function get($id) {
        return $this->db->queryRow("SELECT * FROM " . $this->db->tableName . " WHERE id = :id", array(
            ":id" => $id
        ));
    }

    public function getWithThumbnail($id) {
        $slidesModel = new N2SmartsliderSlidesModel();
        return $this->db->queryRow("SELECT sliders.*, IF(sliders.thumbnail != '',sliders.thumbnail,(SELECT slides.thumbnail from " . $slidesModel->db->tableName . " AS slides WHERE slides.slider = sliders.id AND slides.published = 1 AND slides.generator_id = 0 AND slides.thumbnail NOT LIKE '' ORDER BY  slides.first DESC, slides.ordering ASC LIMIT 1)) AS thumbnail 
        FROM " . $this->db->tableName . " AS sliders
        WHERE sliders.id = :id", array(
            ":id" => $id
        ));
    }

    public function refreshCache($sliderid) {
        N2Cache::clearGroup(N2SmartSliderAbstract::getCacheId($sliderid));
        N2Cache::clearGroup(N2SmartSliderAbstract::getAdminCacheId($sliderid));
        self::markChanged($sliderid);
    }


    /**
     * @return mixed
     */
    public function getAll($orderBy = 'ordering', $orderByDirection = 'DESC') {
        $slidesModel = new N2SmartsliderSlidesModel();
        $sliders     = $this->db->queryAll("
            SELECT sliders.*, 
                  IF(sliders.thumbnail != '',sliders.thumbnail,(SELECT slides.thumbnail FROM " . $slidesModel->db->tableName . " AS slides WHERE slides.slider = sliders.id AND slides.published = 1 AND slides.generator_id = 0 AND slides.thumbnail NOT LIKE '' ORDER BY  slides.first DESC, slides.ordering ASC LIMIT 1)) AS thumbnail,
                  (SELECT count(*) FROM " . $slidesModel->db->tableName . " AS slides2 WHERE slides2.slider = sliders.id GROUP BY slides2.slider) AS slides
            FROM " . $this->db->tableName . " AS sliders
            ORDER BY " . $orderBy . ' ' . $orderByDirection);
        return $sliders;
    }

    public static function renderAddForm($data = array()) {
        return self::editForm($data);
    }

    public static function renderEditForm($slider) {

        $data = json_decode($slider['params'], true);
        if ($data == null) $data = array();
        $data['title']     = $slider['title'];
        $data['type']      = $slider['type'];
        $data['thumbnail'] = $slider['thumbnail'];
        return self::editForm($data);
    }

    private static function editForm($data = array()) {

        $configurationXmlFile = dirname(__FILE__) . '/forms/slider.xml';

        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('smartslider')
                                 ->getApplicationType('backend'));
        $form->set('class', 'nextend-smart-slider-admin');

        $form->loadArray($data);

        $form->loadXMLFile($configurationXmlFile);

        echo $form->render('slider');

        N2Loader::import('libraries.form.element.url');
        N2JS::addFirstCode('nextend.NextendElementUrlParams=' . N2ElementUrl::getNextendElementUrlParameters() . ';');

        return $data;
    }

    public static function renderImportByUploadForm() {

        $configurationXmlFile = dirname(__FILE__) . '/forms/import/upload.xml';

        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('smartslider')
                                 ->getApplicationType('backend'));

        $form->loadXMLFile($configurationXmlFile);

        echo $form->render('slider');
    }

    public static function renderRestoreByUploadForm() {

        $configurationXmlFile = dirname(__FILE__) . '/forms/import/restore.xml';

        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('smartslider')
                                 ->getApplicationType('backend'));

        $form->loadXMLFile($configurationXmlFile);

        echo $form->render('slider');
    }

    public static function renderImportFromServerForm() {

        $configurationXmlFile = dirname(__FILE__) . '/forms/import/server.xml';

        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('smartslider')
                                 ->getApplicationType('backend'));

        $form->loadXMLFile($configurationXmlFile);

        echo $form->render('slider');
    }

    public static function renderRestoreFromServerForm() {
        $configurationXmlFile = dirname(__FILE__) . '/forms/import/restorefromserver.xml';

        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('smartslider')
                                 ->getApplicationType('backend'));

        $form->loadXMLFile($configurationXmlFile);

        echo $form->render('slider');
    }

    function import($slider) {
        try {
            $this->db->insert(array(
                'title'     => $slider['title'],
                'type'      => $slider['type'],
                'thumbnail' => empty($slider['thumbnail']) ? '' : $slider['thumbnail'],
                'params'    => $slider['params']->toJSON(),
                'time'      => date('Y-m-d H:i:s', N2Platform::getTime())
            ));

            return $this->db->insertId();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    function restore($slider) {

        if (isset($slider['id']) && $slider['id'] > 0) {

            $this->delete($slider['id']);

            try {
                $this->db->insert(array(
                    'id'        => $slider['id'],
                    'title'     => $slider['title'],
                    'type'      => $slider['type'],
                    'thumbnail' => empty($slider['thumbnail']) ? '' : $slider['thumbnail'],
                    'params'    => $slider['params']->toJSON(),
                    'time'      => date('Y-m-d H:i:s', N2Platform::getTime())
                ));

                return $this->db->insertId();
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        return $this->import($slider);
    }

    /**
     * @param $sliderId
     * @param $params N2Data
     */
    function importUpdate($sliderId, $params) {

        $this->db->update(array(
            'params' => $params->toJson()
        ), array(
            "id" => $sliderId
        ));
    }

    function create($slider) {
        if (!isset($slider['title'])) return false;
        if ($slider['title'] == '') $slider['title'] = n2_('New slider');

        $title = $slider['title'];
        unset($slider['title']);
        $type = $slider['type'];
        unset($slider['type']);

        $thumbnail = '';
        if (!empty($slider['thumbnail'])) {
            $thumbnail = $slider['thumbnail'];
            unset($slider['thumbnail']);
        }

        try {
            $this->db->insert(array(
                'title'     => $title,
                'type'      => $type,
                'params'    => json_encode($slider),
                'thumbnail' => $thumbnail,
                'time'      => date('Y-m-d H:i:s', N2Platform::getTime())
            ));

            return $this->db->insertId();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    function save($id, $slider) {
        if (!isset($slider['title']) || $id <= 0) return false;
        if ($slider['title'] == '') $slider['title'] = n2_('New slider');

        $title = $slider['title'];
        unset($slider['title']);
        $type = $slider['type'];
        unset($slider['type']);

        $thumbnail = '';
        if (!empty($slider['thumbnail'])) {
            $thumbnail = $slider['thumbnail'];
            unset($slider['thumbnail']);
        }

        $this->db->update(array(
            'title'     => $title,
            'type'      => $type,
            'params'    => json_encode($slider),
            'thumbnail' => $thumbnail
        ), array(
            "id" => $id
        ));

        self::markChanged($id);

        return $id;
    }

    function setThumbnail($id, $thumbnail) {

        $this->db->update(array(
            'thumbnail' => $thumbnail
        ), array(
            "id" => $id
        ));

        self::markChanged($id);

        return $id;
    }

    function delete($id) {
        $slidesModel = new N2SmartsliderSlidesModel();
        $slidesModel->deleteBySlider($id);

        $this->db->deleteByPk($id);

        N2Cache::clearGroup(N2SmartSliderAbstract::getCacheId($id));
        N2Cache::clearGroup(N2SmartSliderAbstract::getAdminCacheId($id));

        self::markChanged($id);
    }

    function deleteSlides($id) {
        $slidesModel = new N2SmartsliderSlidesModel();
        $slidesModel->deleteBySlider($id);
        self::markChanged($id);
    }

    function duplicate($id) {
        $slider = $this->get($id);
        unset($slider['id']);

        $slider['title'] .= n2_(' - copy');
        $slider['time'] = date('Y-m-d H:i:s', N2Platform::getTime());

        try {
            $this->db->insert($slider);
            $newSliderId = $this->db->insertId();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        if (!$newSliderId) {
            return false;
        }

        $slidesModel = new N2SmartsliderSlidesModel();

        foreach ($slidesModel->getAll($id) AS $slide) {
            $slidesModel->copy($slide['id'], $newSliderId);
        }

        return $newSliderId;

    }

    function redirectToCreate() {
        N2Request::redirect($this->appType->router->createUrl(array("sliders/create")), 302, true);
    }

    function exportSlider($id) {

    }

    function exportSliderAsHTML($id) {

    }

    public static function markChanged($sliderid) {
        N2SmartSliderHelper::getInstance()
                           ->setSliderChanged($sliderid, 1);
    }

    public static function box($slider, $widget, $appType) {
        $lt   = array();
        $lt[] = N2Html::tag('div', array(
            'class' => 'n2-ss-box-select',
        ), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-tick2'), ''));

        $rt = array();

        $rb = array();

        $thumbnail = $slider['thumbnail'];
        if (empty($thumbnail)) {
            $thumbnail = '$system$/images/placeholder/image.png';
        }

        $editUrl = $appType->router->createUrl(array(
            'slider/edit',
            array(
                'sliderid' => $slider['id']
            )
        ));

        $lb = array(
            N2Html::tag('div', array(
                'class' => 'n2-button n2-button-normal n2-button-xs n2-radius-s n2-button-grey n2-h5',
            ), '#' . $slider['id'])
        );


        $attributes = array(
            'style'         => 'background-image: url(' . N2ImageHelper::fixed($thumbnail) . ');',
            'class'         => 'n2-ss-box-slider n2-box-selectable',
            'data-title'    => $slider['title'],
            'data-editUrl'  => $editUrl,
            'data-sliderid' => $slider['id']
        );
        $widget->init("box", array(
            'attributes'         => $attributes,
            'firstCol'           => N2Html::link($slider['title'], $editUrl, array('class' => 'n2-h4')) . N2Html::tag('div', array(
                    'class' => 'n2-button n2-button-normal n2-button-s n2-radius-s n2-button-grey n2-h4 n2-right',
                ), $slider['slides'] | 0),
            'lt'                 => implode('', $lt),
            'lb'                 => implode('', $lb),
            'rt'                 => implode('', $rt),
            'rtAttributes'       => array('class' => 'n2-on-hover'),
            'rb'                 => implode('', $rb),
            'overlay'            => N2Html::tag('div', array(
                'class' => 'n2-box-overlay n2-on-hover'
            ), N2Html::tag('div', array('class' => 'n2-button n2-button-normal n2-button-s n2-button-green n2-radius-s n2-uc n2-h5'), n2_('Edit'))),
            'placeholderContent' => ''
        ));
    }

    public function order($ids, $isReverse = false) {
        if (is_array($ids) && count($ids) > 0) {
            if ($isReverse) {
                $ids = array_reverse($ids);
            }
            $i = 0;
            foreach ($ids AS $id) {
                $id = intval($id);
                if ($id > 0) {
                    $this->db->update(array(
                        'ordering' => $i,
                    ), array(
                        "id" => $id
                    ));

                    $i++;
                }
            }
            return $i;
        }
        return false;
    }

    protected function getMaximalOrderValue() {

        $query  = "SELECT MAX(ordering) AS ordering FROM " . $this->db->tableName . "";
        $result = $this->db->queryRow($query);

        if (isset($result['ordering'])) return $result['ordering'] + 1;
        return 0;
    }
} 
<?php

namespace Sprint\Migration\Helpers;
use Sprint\Migration\Helper;

class IblockHelper extends Helper
{

    public function addIblockTypeIfNotExists($fields) {
        $id = $fields['ID'];

        if ($this->getIblockTypeId($id)) {
            return $id;
        }

        $default = Array(
            'ID' => '',
            'SECTIONS' => 'Y',
            'IN_RSS' => 'N',
            'SORT' => 100,
            'LANG' => Array(
                'ru' => Array(
                    'NAME' => 'Catalog',
                    'SECTION_NAME' => 'Sections',
                    'ELEMENT_NAME' => 'Elements'
                ),
                'en' => Array(
                    'NAME' => 'Catalog',
                    'SECTION_NAME' => 'Sections',
                    'ELEMENT_NAME' => 'Elements'
                ),
            )
        );

        $fields = array_merge($default, $fields);

        $ib = new \CIBlockType;
        if ($ib->Add($fields)) {
            return $id;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);
    }

    public function addIblockIfNotExists($fields) {
        $code = $fields['CODE'];
        $iblockId = $this->getIblockId($code);

        if ($iblockId) {
            return $iblockId;
        }

        $default = array(
            'ACTIVE' => 'Y',
            'NAME' => '',
            'CODE' => '',
            'LIST_PAGE_URL' => '',
            'DETAIL_PAGE_URL' => '',
            'SECTION_PAGE_URL' => '',
            'IBLOCK_TYPE_ID' => 'main',
            'SITE_ID' => array('s1'),
            'SORT' => 500,
            'GROUP_ID' => array('2' => 'R'),
            'VERSION' => 2,
            'BIZPROC' => 'N',
            'WORKFLOW' => 'N',
            'INDEX_ELEMENT' => 'N',
            'INDEX_SECTION' => 'N'
        );

        $fields = array_merge($default, $fields);

        $ib = new \CIBlock;
        $iblockId = $ib->Add($fields);

        if ($iblockId) {
            return $iblockId;
        }


        $this->throwException(__METHOD__, $ib->LAST_ERROR);
    }

    public function deleteIblockIfExists($iblockCode) {
        $iblockId = $this->getIblockId($iblockCode);
        return ($iblockId) ? \CIBlock::Delete($iblockId) : false;
    }

    public function addPropertyIfNotExists($iblockId, $fields) {
        $code = $fields['CODE'];
        $propId = $this->getPropertyId($iblockId, $code);

        if ($propId) {
            return $propId;
        }

        $default = array(
            'IBLOCK_ID' => $iblockId,
            'NAME' => '',
            'ACTIVE' => 'Y',
            'SORT' => '500',
            'CODE' => '',
            'PROPERTY_TYPE' => 'S',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE' => 'N',
            'USER_TYPE' => '',
            'IS_REQUIRED' => 'N',
            'FILTRABLE' => 'Y',
            'LINK_IBLOCK_ID' => 0
        );

        $fields = array_merge($default, $fields);
        if (isset($fields['VALUES'])) {
            $fields['PROPERTY_TYPE'] = 'L';
        }

        $ib = new \CIBlockProperty;
        $propId = $ib->Add($fields);

        if ($propId) {
            return $propId;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);

    }


    public function deletePropertyIfExists($iblockId, $propertyCode) {
        $propId = $this->getPropertyId($iblockId, $propertyCode);
        if (!$propId) {
            return false;
        }

        $ib = new \CIBlockProperty;
        if ($ib->Delete($propId)) {
            return true;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);

    }


    public function updatePropertyIfExists($iblockId, $propertyCode, $fields) {
        $propId = $this->getPropertyId($iblockId, $propertyCode);
        if (!$propId) {
            return false;
        }

        $ib = new \CIBlockProperty();
        if ($ib->Update($propId, $fields)) {
            return true;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);

    }

    public function addSection($iblockId, $fields) {

        $default = Array(
            "ACTIVE" => "Y",
            "IBLOCK_SECTION_ID" => false,
            "NAME" => 'section',
            "CODE" => '',
            "SORT" => 100,
            "PICTURE" => false,
            "DESCRIPTION" => '',
            "DESCRIPTION_TYPE" => 'text'
        );

        $fields = array_merge($default, $fields);
        $fields["IBLOCK_ID"] = $iblockId;

        $ib = new \CIBlockSection;
        $id = $ib->Add($fields);

        if ($id) {
            return $id;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);

    }

    public function addElement($iblockId, $fields, $props = array()) {
        $default = array(
            "NAME" => "element",
            "IBLOCK_SECTION_ID" => false,
            "ACTIVE" => "Y",
            "PREVIEW_TEXT" => "",
            "DETAIL_TEXT" => "",
        );

        $fields = array_merge($default, $fields);
        $fields["IBLOCK_ID"] = $iblockId;

        if (!empty($props)) {
            $fields['PROPERTY_VALUES'] = $props;
        }

        $ib = new \CIBlockElement;
        $id = $ib->Add($fields);

        if ($id) {
            return $id;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);
    }

    public function addElementIfNotExists($iblockId, $fields, $props = array()) {
        if (!empty($fields['CODE']) && !$this->getElementByCode($iblockId, $fields['CODE'])){
            return $this->addElement($iblockId, $fields, $props);
        }
        return false;
    }

    public function deleteElementIfExists($iblockId, $code){
        $aItem = $this->getElementByCode($iblockId, $code);

        if (!$aItem){
            return false;
        }

        $ib = new \CIBlockElement;
        if ($ib->Delete($aItem['ID'])) {
            return true;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);
    }

    public function getIblockId($code) {
        $aIblock = $this->getIblock($code);
        return ($aIblock && isset($aIblock['ID'])) ? $aIblock['ID'] : 0;
    }

    public function getIblock($code) {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        return \CIBlock::GetList(array('SORT' => 'ASC'), array('CHECK_PERMISSIONS' => 'N', 'CODE' => $code))->Fetch();
    }

    public function getIblocks() {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $dbResult = \CIBlock::GetList(array('SORT' => 'ASC'), array('CHECK_PERMISSIONS' => 'N'));
        $list = array();
        while ($aItem = $dbResult->Fetch()) {
            $list[] = $aItem;
        }
        return $list;
    }

    public function getIblockType($id) {
        return \CIBlockType::GetList(array('SORT' => 'ASC'), array('CHECK_PERMISSIONS' => 'N', '=ID' => $id))->Fetch();
    }

    public function getIblockTypeId($id) {
        $aIblock = $this->getIblockType($id);
        return ($aIblock && isset($aIblock['ID'])) ? $aIblock['ID'] : 0;
    }

    public function getPropertyId($iblockId, $code) {
        $aIblock = $this->getProperty($iblockId, $code);
        return ($aIblock && isset($aIblock['ID'])) ? $aIblock['ID'] : 0;
    }

    public function getProperty($iblockId, $code) {
        return \CIBlockProperty::GetList(array('SORT' => 'ASC'), array('IBLOCK_ID' => $iblockId, 'CODE' => $code, 'CHECK_PERMISSIONS' => 'N'))->Fetch();
    }

    public function mergeIblockFields($iblockId, $fields) {
        $default = \CIBlock::GetFields($iblockId);
        $fields = $this->arraySoftMerge($default, $fields);
        \CIBlock::SetFields($iblockId, $fields);
    }

    public function deleteProperty($iblockId, $propertyCode) {
        return $this->deletePropertyIfExists($iblockId, $propertyCode);
    }

    public function updateProperty($iblockId, $propertyCode, $fields) {
        return $this->updatePropertyIfExists($iblockId, $propertyCode, $fields);
    }

    protected function getElementByCode($iblockId, $code){
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        return \CIBlockElement::GetList(array('SORT' => 'ASC'), array(
            'IBLOCK_ID' => $iblockId,
            '=CODE' => $code
        ), false, array('nTopCount' => 1), array(
            'ID',
            'IBLOCK_ID',
            'NAME',
            'CODE',
        ))->Fetch();
    }

    protected function arraySoftMerge($default, $fields) {
        foreach ($default as $key => $val) {
            if (isset($fields[$key])) {
                if (is_array($val) && is_array($fields[$key])) {
                    $default[$key] = $this->arraySoftMerge($val, $fields[$key]);
                } else {
                    $default[$key] = $fields[$key];
                }
            }
            unset($fields[$key]);
        }

        foreach ($fields as $key => $val) {
            $default[$key] = $val;
        }

        return $default;
    }


}
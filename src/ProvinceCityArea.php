<?php
/**
 * Created by PhpStorm.
 * User: aoxiang
 * Date: 2019-06-04
 * Time: 21:46
 */

namespace Aoxiang\Pca;

use Aoxiang\Pca\Models\ProvinceCityArea as PCA;

class ProvinceCityArea
{
    static public function test()
    {
        return date('Y-m-d H:i:s');
    }

    static public function getProvinceList()
    {
        return self::getListByParentId(0);
    }

    static public function getCityList($provinceId)
    {
        return self::getListByParentId($provinceId);
    }

    static public function getAreaList($cityId)
    {
        return self::getListByParentId($cityId);
    }

    static public function getProvince($provinceId)
    {
        return self::getItem($provinceId);
    }

    static public function getCity($cityId)
    {
        return self::getItem($cityId);
    }

    static public function getArea($areaId)
    {
        return self::getItem($areaId);
    }

    static public function getAllProvince()
    {
        return self::getListByType();
    }

    static public function getAllCity()
    {
        return self::getListByType('city');
    }

    static public function getAllArea()
    {
        return self::getListByType('area');
    }

    static public function getAllStreet()
    {
        return self::getListByType('street');
    }

    static protected function getListByParentId($parentId)
    {
        return PCA::where('parent_id', $parentId)->get();
    }

    static protected function getItem($id)
    {
        return PCA::where('id', $id)->first();
    }

    static protected function getListByType($type = 'province')
    {
        return PCA::where('type', $type)->get();
    }


    static public function getName($provinceId = 0, $cityId = 0, $areaId = 0, $streetId)
    {
        $text = [];
        if (!empty($provinceId)) {
            $province = self::getItem($provinceId);
            $text[]   = $province->name;
        }

        if (!empty($cityId)) {
            $city   = self::getItem($cityId);
            $text[] = $city->name;
        }

        if (!empty($areaId)) {
            $area   = self::getItem($areaId);
            $text[] = $area->name;
        }

        if (!empty($streetId)) {
            $street = self::getItem($streetId);
            $text[] = $street->name;
        }
        return implode('', $text);
    }
}
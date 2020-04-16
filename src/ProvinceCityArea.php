<?php
/**
 * Created by PhpStorm.
 * User: aoxiang
 * Date: 2019-06-04
 * Time: 21:46.
 */

namespace Aoxiang\Pca;

use Aoxiang\Pca\Models\ProvinceCityArea as PCA;

class ProvinceCityArea
{
    public static function test()
    {
        return date('Y-m-d H:i:s');
    }

    public static function getProvinceList()
    {
        return self::getListByParentId(0);
    }

    public static function getCityList($provinceId)
    {
        return self::getListByParentId($provinceId);
    }

    public static function getAreaList($cityId)
    {
        return self::getListByParentId($cityId);
    }

    public static function getProvince($provinceId)
    {
        return self::getItem($provinceId);
    }

    public static function getCity($cityId)
    {
        return self::getItem($cityId);
    }

    public static function getArea($areaId)
    {
        return self::getItem($areaId);
    }

    public static function getAllProvince()
    {
        return self::getListByType();
    }

    public static function getAllCity()
    {
        return self::getListByType('city');
    }

    public static function getAllArea()
    {
        return self::getListByType('area');
    }

    public static function getAllStreet()
    {
        return self::getListByType('street');
    }

    protected static function getListByParentId($parentId)
    {
        return PCA::where('parent_id', $parentId)->get();
    }

    protected static function getItem($id)
    {
        return PCA::where('id', $id)->first();
    }

    protected static function getListByType($type = 'province')
    {
        return PCA::where('type', $type)->get();
    }

    public static function getName($provinceId, $cityId, $areaId, $streetId)
    {
        $text = [];
        if (! empty($provinceId)) {
            $province = self::getItem($provinceId);
            $text[] = $province->name;
        }

        if (! empty($cityId)) {
            $city = self::getItem($cityId);
            $text[] = $city->name;
        }

        if (! empty($areaId)) {
            $area = self::getItem($areaId);
            $text[] = $area->name;
        }

        if (! empty($streetId)) {
            $street = self::getItem($streetId);
            $text[] = $street->name;
        }

        return implode('', $text);
    }
}

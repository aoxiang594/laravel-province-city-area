<?php
/**
 * Created by PhpStorm.
 * User: aoxiang
 * Date: 2019-06-04
 * Time: 21:46.
 */

namespace Aoxiang\Pca;

use Aoxiang\Pca\Models\ProvinceCityArea as PCA;
use Illuminate\Support\Facades\Cache;

class ProvinceCityArea
{
    /**
     * @return false|string
     */
    public static function test()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * @return mixed
     */
    public static function getProvinceList()
    {
        return self::getListByParentId(0);
    }

    /**
     * @param $provinceId
     *
     * @return mixed
     */
    public static function getCityList($provinceId)
    {
        return self::getListByParentId($provinceId);
    }

    /**
     * @param $cityId
     *
     * @return mixed
     */
    public static function getAreaList($cityId)
    {
        return self::getListByParentId($cityId);
    }

    /**
     * @param $provinceId
     *
     * @return mixed
     */
    public static function getProvince($provinceId)
    {
        return self::getItem($provinceId);
    }

    /**
     * @param $cityId
     *
     * @return mixed
     */
    public static function getCity($cityId)
    {
        return self::getItem($cityId);
    }

    /**
     * @param $areaId
     *
     * @return mixed
     */
    public static function getArea($areaId)
    {
        return self::getItem($areaId);
    }

    /**
     * @return mixed
     */
    public static function getAllProvince()
    {
        return self::getListByType();
    }

    /**
     * @return mixed
     */
    public static function getAllCity()
    {
        return self::getListByType('city');
    }

    /**
     * @return mixed
     */
    public static function getAllArea()
    {
        return self::getListByType('area');
    }

    /**
     * @return mixed
     */
    public static function getAllStreet()
    {
        return self::getListByType('street');
    }

    /**
     * @param $parentId
     *
     * @return mixed
     */
    protected static function getListByParentId($parentId)
    {
        $cache_key = 'pca_list_' . $parentId;
        $result    = Cache::get($cache_key);
        if( empty($result) ){
            $result = PCA::where('parent_id', $parentId)->get();
            Cache::put($cache_key, $result);
        }

        return $result;
    }

    protected static function getAreaByProvinceId($provinceId)
    {
        $cache_key = 'pca_area_province' . $provinceId;
        $result    = Cache::get($cache_key);
        if( empty($result) ){
            $result = PCA::whereIn('parent_id', PCA::where('parent_id', $provinceId)->pluck('id'))->get();
            Cache::put($cache_key, $result);
        }

        return $result;

    }

    /**
     * @param $id
     *
     * @return mixed
     */
    protected static function getItem($id)
    {
        return PCA::where('id', $id)->first();
    }

    /**
     * @param  string  $type
     *
     * @return mixed
     */
    protected static function getListByType($type = 'province')
    {
        $cache_key = 'pca_list_' . $type;
        $result    = Cache::get($cache_key);
        if( empty($result) ){
            $result = PCA::where('type', $type)->get();
            Cache::put($cache_key, $result);
        }

        return $result;
    }

    /**
     * @param $provinceId
     * @param $cityId
     * @param $areaId
     * @param $streetId
     *
     * @return string
     */
    public static function getName($provinceId, $cityId, $areaId, $streetId)
    {
        $text = [];
        if( !empty($provinceId) ){
            $province = self::getItem($provinceId);
            $text[]   = $province->name;
        }

        if( !empty($cityId) ){
            $city   = self::getItem($cityId);
            $text[] = $city->name;
        }

        if( !empty($areaId) ){
            $area   = self::getItem($areaId);
            $text[] = $area->name;
        }

        if( !empty($streetId) ){
            $street = self::getItem($streetId);
            $text[] = $street->name;
        }

        return implode('', $text);
    }


    /**
     *
     * 解析地址，代码比较烂，先跑起来
     *
     * @param $data
     *
     * @return array
     */
    public static function parseAddress($data)
    {

        //省市区县
        $result = [];
        //1. 过滤掉收货地址中的常用说明字符，排除干扰词
        $data = preg_replace(
            "/收货地址|地址|收货人|收件人|收货|邮编|电话|联系电话|姓名|身份证号码|身份证号|身份证|详细地址|手机号码|所在地区|：|:|；|;|，|,|。|\.|“|”|\"/",
            ' ',
            $data
        );

        //2. 把空白字符(包括空格\r\n\t)都换成一个空格,去除首位空格
        $data = trim(preg_replace('/\s{1,}/', ' ', $data));

        //3. 替换特定文字

        $data = preg_replace(
            "/维吾尔自治区|维吾尔族自治区|回族自治区|自治区|行政区|特别行政区|\//",
            '',
            $data
        );
        //解析手机号
        $data     = (string) preg_replace('/(\\d{3})-(\\d{4})-(\\d{4})/u', '$1$2$3', $data);
        $data     = (string) preg_replace('/(\\d{3}) (\\d{4}) (\\d{4})/u', '$1$2$3', $data);
        $data     = (string) preg_replace('/(\\d{4}) \\d{4} \\d{4}/u', '$1$2$3', $data);
        $data     = (string) preg_replace('/(\\d{4})/u', '$1$2$3', $data);
        $phoneReg = '/(\\d{7,12})|(\\d{3,4}-\\d{6,8})|(86-[1][0-9]{10})|(86[1][0-9]{10})|([1][0-9]{10})/u';
        preg_match($phoneReg, $data, $m);

        if( \count($m) > 0 ){
            $result['mobile'] = $m[0];
            $data             = trim(str_replace($m[0], ' ', $data));
        }
        //再次把2个及其以上的空格合并成一个，并首位TRIM
        $data = trim(preg_replace('/ {2,}/', ' ', $data));

        //按照空格切分 长度长的为地址 短的为姓名 因为不是基于自然语言分析，所以采取统计学上高概率的方案
        $split_arr = explode(' ', $data);

        if( count($split_arr) > 1 ){
            $result['name'] = $split_arr[0];
            foreach ($split_arr as $value) {
                if( strlen($value) < strlen($result['name']) ){
                    $result['name'] = $value;
                }
            }
            $data = trim(str_replace($result['name'], '', $data));
        }


        $province_list = static::getProvinceList()->pluck('name', 'id');

        //解析省市县
        if( strpos($data, '省') !== false ){
            $province    = explode('省', $data);
            $province_id = array_search($province[0], $province_list->toArray());
            if( $province_id !== false ){
                $result['province_id'] = $province_id;
                $result['province']    = $province[0];
                $data                  = $province[1];
            }
        } else {
            foreach ($province_list as $key => $temp) {
                if( strpos($data, $temp) === 0 ){
                    $result['province']    = $temp;
                    $result['province_id'] = $key;
                    $data                  = trim(str_replace($temp, '', $data));
                }
            }
        }
        //是否是直辖市,必须是在开头，因为其他城市可能会有北京西路、上海路之类的街道
        $length  = strpos($data, '市');
        $city    = substr($data, 0, $length);
        $hasCity = PCA::where('name', 'like', $city . '%')->first();

        if( $hasCity ){
            if( $hasCity === PCA::TYPE_PROVINCE ){
                $result['province']    = $city;
                $result['province_id'] = $hasCity->id;
            } else {
                $result['city']    = $city;
                $result['city_id'] = $hasCity->id;
                if( strpos($data, $city . '市') !== false ){
                    $data = mb_substr($data, mb_strlen($city) + 1);
                } else {
                    $data = mb_substr($data, mb_strlen($city));
                }
            }
        }

        //区
        if( empty($result['city']) ){
            $areaList = static::getAreaByProvinceId($result['province_id']);
        } else {
            $areaList = static::getAreaList($result['city_id']);

        }
        foreach ($areaList as $key => $area) {
            // 去掉最后一位区或者县字
//            $temp = str_replace(['区', '县'], '', $area->name);
//            dump($temp);
//            if( strpos($data, $temp) !== false ){
//                $result['area']    = $area->name;
//                $result['area_id'] = $area->id;
//                $result['city_id'] = intval($area->parent_id);
//                $data              = trim(str_replace([$area->name, $temp], '', $data));
//                break;
//            }
            $match = false;
            $temp  = $area->name;
            if( strpos($data, $temp) !== false ){
                $match = true;
            } else {
                $temp = str_replace(['区', '县'], '', $area->name);
                if( !empty($temp) ){
                    if( strpos($data, $temp) !== false ){
                        $match = true;
                    }
                }

            }
            if( $match ){
                $result['area']    = $area->name;
                $result['area_id'] = $area->id;
                $result['city_id'] = intval($area->parent_id);
                $data              = trim(str_replace([$area->name, $temp], '', $data));
                break;
            }

        }

        if( empty($result['city']) ){
            //阿克苏地区，实际填写 阿克苏这样的简写
            $city = PCA::where('id', $areaList[$key]->parent_id)->first();
            if( !empty($city) ){
                $result['city']    = $city->name;
                $result['city_id'] = $city->id;
                //断字把简写去除
                $len = mb_strlen($result['city']);
                for ($i = 0; $i <= $len; $i++) {
                    $temp = mb_substr($result['city'], 0, $len - $i);
                    if( !empty($temp) ){
                        if( strpos($data, $temp) === 0 ){
                            //完全匹配
                            $data = str_replace($temp, '', $data);
                            break;
                        }
                    }

                }
            }

        }

        $result['address'] = $data;

        return $result;

    }
}

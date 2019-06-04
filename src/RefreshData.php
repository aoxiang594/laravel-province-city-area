<?php
/**
 * Created by PhpStorm.
 * User: aoxiang
 * Date: 2019-06-04
 * Time: 22:03
 */

namespace Aoxiang\Pca;

use Aoxiang\Pca\Models\ProvinceCityArea as PCAModel;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class RefreshData
{
    static $client = null, $provinceList = null, $url = '';
    static $result = [];
    static $headers = ["accept"                    => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3",
                       "accept-encoding"           => "gzip, deflate, br",
                       "accept-language"           => "zh-CN,zh;q=0.9,en;q=0.8",
                       "cache-control"             => "max-age=0",
                       "cookie"                    => "wxa_level=1; retina=1; cid=9; wqmnx1=MDEyNjM3MnR0ZHIxNnR6Lm9VZV8gIGx0LlRrb2kwZTJpMWZkLTVRT0YmKQ%3D%3D; webp=1; __jda=122270672.1559637044910143121828.1559637044.1559637044.1559637044.1; __jdv=122270672%7Cdirect%7C-%7Cnone%7C-%7C1559637044913; __jdc=122270672; mba_muid=1559637044910143121828; __wga=1559637047072.1559637047072.1559637047072.1559637047072.1.1; PPRD_P=UUID.1559637044910143121828; sc_width=375; shshshfp=827a04050b2522269f28c6d0dd11fcf2; shshshfpa=d6f7d411-30ed-7855-3a58-d47d262384ac-1559637047; shshshfpb=pisuk%2Fwyhm%2FErXKDTfJs9%2FQ%3D%3D; sk_history=100000068472%2C; wq_addr=0%7C84_1310_53281_0%7C%u9493%u9C7C%u5C9B_%u9493%u9C7C%u5C9B_%u9493%u9C7C%u5C9B%u5168%u533A_%7C%7C; jdAddrId=84_1310_53281_0; jdAddrName=%u9493%u9C7C%u5C9B_%u9493%u9C7C%u5C9B_%u9493%u9C7C%u5C9B%u5168%u533A_; mitemAddrId=84_1310_53281_0; mitemAddrName=; wq_logid=1559637176.1241587375",
                       "upgrade-insecure-requests" => "1",
                       "user-agent"                => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36",
    ];

    static function run()
    {

        self::$provinceList = [
            '1'     => '北京',
            '2'     => '上海',
            '3'     => '天津',
            '4'     => '重庆',
            '5'     => '河北',
            '6'     => '山西',
            '7'     => '河南',
            '8'     => '辽宁',
            '9'     => '吉林',
            '10'    => '黑龙江',
            '11'    => '内蒙古',
            '12'    => '江苏',
            '13'    => '山东',
            '14'    => '安徽',
            '15'    => '浙江',
            '16'    => '福建',
            '17'    => '湖北',
            '18'    => '湖南',
            '19'    => '广东',
            '20'    => '广西',
            '21'    => '江西',
            '22'    => '四川',
            '23'    => '海南',
            '24'    => '贵州',
            '25'    => '云南',
            '26'    => '西藏',
            '27'    => '陕西',
            '28'    => '甘肃',
            '29'    => '青海',
            '30'    => '宁夏',
            '31'    => '新疆',
            '32'    => '台湾',
            '84'    => '钓鱼岛',
            '52993' => '港澳',
//            '53283' => '海外',
        ];

        foreach (self::$provinceList as $id => $province) {
            self::$result[$id] = [
                'id'        => $id,
                'name'      => $province,
                'city_list' => [],
            ];
            try {
                $city = self::getCity($id);

                $cityList = [];
                if ($city !== false) {
                    foreach ($city as $item) {
                        self::info("获取数据成功:" . $item['name']);
                        $cityList[$item['id']] = [
                            'id'        => $item['id'],
                            'name'      => $item['name'],
                            'area_list' => [],
                        ];
                        $area                  = self::getArea($item['id']);
                        if ($area !== false) {
                            foreach ($area as &$a) {
                                unset($a['areaCode']);
                            }
                            $cityList[$item['id']]['area_list'] = $area;
                        }
                    }
                }
                self::$result[$id]['city_list'] = array_values($cityList);
            } catch (\Exception $e) {

            }
        }
        self::$result = array_values(self::$result);

        static::insertToDb();

        //file_put_contents(config('dataFileName'), json_encode(self::$result));
    }

    static function insertToDb()
    {
        DB::beginTransaction();
        try {
            foreach (self::$result as $item) {
                $province = PCAModel::create($item);
                if (!empty($item['city_list'])) {
                    foreach ($item['city_list'] as $value) {
                        $value['parent_id'] = $province->id;
                        $city               = PCAModel::create($value);
                        if (!empty($value['area_list'])) {
                            foreach ($value['area_list'] as $v) {
                                $v['parent_id'] = $city->id;
                                $area           = PCAModel::create($v);

                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            self::info("更新省市县数据失败.");
            self::info($e->getMessage());
        }

        DB::commit();
    }

    static function getCity($id)
    {
        return self::parseData($id);
    }

    static function getArea($id)
    {
        return self::parseData($id);
    }

    static function parseData($id)
    {
        $data = self::getData($id);
        if ($data === false) {
            self::info("获取数据失败.");
        } else {
            $data = self::parseJson($data);
            if ($data === false) {
                self::info(self::$url);
                self::info("解析数据失败.");
            } else {
                return $data;
            }

        }
    }

    static function getData($id)
    {
        self::$url    = 'https://fts.jd.com/area/get?fid=' . $id . '&callback=getAreaList_callbackF&sceneval=2';
        self::$client = is_null(self::$client) ? new Client() : self::$client;
        $response     = self::$client->request('get', self::$url, self::$headers);
        if ($response->getStatusCode() == 200) {
            return $response->getBody()->getContents();
        } else {
            return false;
        }
    }

    static function parseJson($data = '')
    {
        $data = preg_replace('/^getAreaList_callback(\w)\(/', '', $data);
        $data = preg_replace('/\)$/', '', $data);

        $data = json_decode($data, true);
        if (is_array($data)) {
            return $data;
        } else {

            return false;
        }

    }

    static function info($str)
    {

        echo "[" . date('Y-m-d H:i:s') . "]" . $str . "\n";
    }
}
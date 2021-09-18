# Laravel-Province-City-Area   「全国省市县乡镇街道」数据(数据来源于京东，内置爬虫，可自行获取最新数据)

![StyleCI build status](https://github.styleci.io/repos/190225659/shield) 

这是一个提供「全国省市县乡镇街道」数据的轮子

试过了很多数据来源(国家统计局、网上其他开发者提供的json等),发现还是会有小部分省市有遗漏。

这个包的数据是**来自于京东**，相对来说会更准确、详尽一些。


> 内部自带京东「省市县乡镇街道」数据爬虫，用户可以自行运行爬取最新的「省市县乡镇街道」数据

#### 项目地址
https://github.com/aoxiang594/laravel-province-city-area

#### Composer 安装
```
composer require aoxiang/province-city-area
```
 

#### 添加provider（laravel 版本 < 5.5）
将`Aoxiang\Pca\ProvinceCityAreaServiceProvider::class`复制到`config.php`内`providers`数组内


#### 执行数据库迁移
```
php artisan migrate
```

#### 最后一步:从京东获取新的「省市县乡镇街道」数据
```
php artisan pca:refreshData
```

#### 其他

`php artisan pca:refreshData -d:file|jd` 
更新数据有一个参数 driver

| 参数值 | 说明 |
| --- | --- |
|file|从本地资源中获取数据，file是默认值|
|jd|从京东获取抓取最新数据|


```
php artisan pca:refreshData

您选择从本地文件中载入省市县数据
如果您要强制从京东获取，请执行php artisan pca:refreshData -d jd
已从本地加载数据完毕，共48054条
数据最后更新时间:2019-06-08 22:50:30
正在插入数据库
48054/48054 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
数据已更新完成
共插入:48054条数据，其中省级行政区:34,城市:457,区县:5171,乡镇街道:42392

```


```
php artisan pca:refreshData -d jd

获取数据成功:云南昆明市盘龙区双龙街道
获取数据成功:云南昆明市盘龙区松华街道
获取数据成功:云南昆明市盘龙区城区
获取数据成功:云南昆明市盘龙区拓东街道
获取数据成功:云南昆明市盘龙区鼓楼街道
获取数据成功:云南昆明市盘龙区东华街道
获取数据成功:云南昆明市盘龙区联盟街道
获取数据成功:云南昆明市盘龙区金辰街道
获取数据成功:云南昆明市盘龙区青云街道
获取数据成功:云南昆明市盘龙区龙泉街道
获取数据成功:云南昆明市盘龙区茨坝街道
获取数据成功:云南昆明市盘龙区滇源街道
获取数据成功:云南昆明市五华区
获取数据成功:云南昆明市五华区沙朗镇
获取数据成功:云南昆明市五华区厂口镇
获取数据成功:云南昆明市五华区高新区
获取数据成功:云南昆明市五华区城区
获取数据成功:云南昆明市五华区华山街道
获取数据成功:云南昆明市五华区护国街道
获取数据成功:云南昆明市五华区大观街道
获取数据成功:云南昆明市五华区龙翔街道
获取数据成功:云南昆明市五华区丰宁街道
获取数据成功:云南昆明市五华区莲华街道
获取数据成功:云南昆明市五华区红云街道
获取数据成功:云南昆明市五华区黑林铺街道
获取数据成功:云南昆明市五华区普吉街道
获取数据成功:云南昆明市五华区西翥街道
正在插入数据库
 48054/48054 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
数据已更新完成
共插入:48054条数据，其中省级行政区:34,城市:457,区县:5171,乡镇街道:42392
```

####  Demo

```php
<?php

namespace App\Http\Controllers;

use Aoxiang\Pca\ProvinceCityArea;
use Illuminate\Routing\Controller as BaseController;

class Controller  extends BaseController{
    public function getProvinceList()
    {
        return response()->json(ProvinceCityArea::getProvinceList());
    }
    
    public function getProvince()
    {
        return response()->json(ProvinceCityArea::getProvinceList());
    }

    public function getCity($provinceId)
    {
        if (empty($provinceId)) {
            return response()->json([]);
        }
        return response()->json(ProvinceCityArea::getCityList($provinceId));
    }

    public function getArea($cityId)
    {
        if (empty($cityId)) {
            return response()->json([]);
        }
        return response()->json(ProvinceCityArea::getAreaList($cityId));
    }
    
    
    public function getAllProvince()
    {
        return response()->json(ProvinceCityArea::getAllProvince());
    }
    
    public function getAllCity()
    {
        return response()->json(ProvinceCityArea::getAllCity());
    }
    public function getAllArea()
    {
        return response()->json(ProvinceCityArea::getAllArea());
    }
    public function getAllStreet()
    {
        return response()->json(ProvinceCityArea::getAllStreet());
    }
    

    public function test()
    {
        echo ProvinceCityArea::getName(21, 1827, 40847, 53114);
        //echo "江西南昌市西湖区系马桩街道";
    }
}

```



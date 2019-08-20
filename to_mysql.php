<?php
require 'vendor/autoload.php';


use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection(
[
	'driver' => 'sqlite',
	// 'url' => "./dist/data.sqlite",
	'database' => "/Users/quan/Desktop/code/Administrative-divisions-of-China/dist/data.sqlite",
	'prefix' => '',
	'foreign_key_constraints' => false
], 
'sqlite'
);


$capsule->addConnection(
[
	'driver'         => 'mysql',
    'host'           => '127.0.0.1',
    'port'           => '3306',
    'database'       => 'gongxubao',
    'username'       => 'root',
    'password'       => '',
    'unix_socket'    => '',
    'charset'        => 'utf8mb4',
    'collation'      => 'utf8mb4_unicode_ci',
    'prefix'         => '',
    'prefix_indexes' => true,
    'strict'         => true,
    'engine'         => null,
    'options'        => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => null,
    ]) : [],
],
'mysql'
);

// 使用设置静态变量方法，令当前的 Capsule 实例全局可用
$capsule->setAsGlobal();

// village
// toMySql('village');
// validate('village');

// street
// toMySql('street');
// validate('street');

// area
// toMySql('area');
// validate('street');

// city
// toMySql('city');
// validate('city');
// province
// toMySql('province');
// validate('province');

function output($msg){
	echo $msg . PHP_EOL;
}

function toMySql($table){

	$count = Capsule::connection('sqlite')->table($table)->select('*')->orderBy('code', 'desc')->count();
	output( $table . " 一共 " . $count  .' 条' );

	$setup = 1;
	$startDate = date('Y-m-d H:i:s');
	output($startDate);
	Capsule::connection('sqlite')->table($table)->orderBy('code', 'asc')->chunk(1000, function($logs) use(&$setup){
		output("开始处理... " . $setup++);
		$inserts = [];
		foreach ($logs as $key => $log) {
			$inserts[] = [
				'code' => $log->code,
				'name' => $log->name,
				'street_code' => $log->streetCode ?? '',
				'area_code' => $log->areaCode ?? '',
				'city_code' => $log->cityCode ?? '',
				'province_code' => $log->provinceCode ?? '',
			];
		}

		output("插入新库... ");
		Capsule::connection('mysql')->table('areas')->insert($inserts);
		output("插入完成... ");
	});
	output($startDate);
	output(date('Y-m-d H:i:s'));

	$count = Capsule::connection('mysql')->table('areas')->select('*')->orderBy('code', 'desc')->count();
	output( "village 一共 " . $count  .' 条' );

}

function validate($table){
	$setup = 1;
	Capsule::connection('sqlite')->table($table)->orderBy('code', 'asc')->chunk(1000, function($logs) use(&$setup){
		output("开始校验... " . $setup++);
		foreach ($logs as $key => $log) {
			$have = Capsule::connection('mysql')->table('areas')->where(
				[
				'code' => $log->code,
				'name' => $log->name,
				'street_code' => $log->streetCode ?? '',
				'area_code' => $log->areaCode ?? '',
				'city_code' => $log->cityCode ?? '',
				'province_code' => $log->provinceCode ?? '',
			]
			)->exists();

			if(!$have){
				output($log->code . ' ' . $table);
			}
		}
	});
}

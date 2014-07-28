# TL404

タイムライン生成支援の API

## 動作環境

- [Redis](http://redis.io/)
- [RabbitMQ](http://www.rabbitmq.com/)
- [PHP](http://www.php.net/) (>= 5.3)
- [jq](http://stedolan.github.io/jq/) (optional)

## インストール方法

~~~
##### Redisをインストールして立ち上げる #####
$ cd tl404
$ mkdir redis
$ wget http://download.redis.io/releases/redis-2.8.13.tar.gz
$ tar zxvf redis-2.8.13.tar.gz
$ rm *.gz
$ cd redis-2.8.13
$ make
$ ./src/redis-server --port 9736

##### RabbitMQをインストールして立ち上げる #####
# Mac OS X での方法を以下に掲載。Linux等その他OSについては公式ドキュメントを参照(http://www.rabbitmq.com/download.html)
$ cd tl404
$ mkdir rabbitmq
$ cd rabbitmq/
$ wget http://www.rabbitmq.com/releases/rabbitmq-server/v3.3.4/rabbitmq-server-mac-standalone-3.3.4.tar.gz
$ tar zxvf rabbitmq-server-mac-standalone-3.3.4.tar.gz
$ rm *.gz
$ cd rabbitmq_server-3.3.4
$ ./sbin/rabbitmq-server

##### PHPの環境を整える #####
# ライブラリのインストール
$ sudo pecl install redis
# 存在しているか確認
$ $ php -m | grep redis
# amqplibのインストール
$ cd tl404/lib
$ vi composer.json
$ cat composer.json
{
  "require": {
      "videlalvaro/php-amqplib": "2.2.*"
  }
}
$ composer.phar install

##### APIを立ち上げる #####
$ cd tl404
$ cd pub
$ php -S localhost:9080

##### workerを立ち上げる #####
$ cd tl404/worker
$ php distributer.php
~~~

## API

### 一覧

API一覧を示す。sid = source id, tid = target id, lim = limit, tlid = timeline id の意味である。

|method|動作|必須パラメータ|追加パラメータ|
|:-:|:-:|:-:|:-:|
|barusu|全てのデータを削除|barusu||
|subscribe|sid が tid を購読|sid, tid||
|unsubscribe|sid が tid の購読を解除|sid,tid||
|get_all_subscriptions|sid が購読している ID をすべて取得|sid||
|get_all_subscribers|sid を購読している ID をすべて取得|sid||
|get_subscriptions|sid が 購読している ID を ofs から cnt 件だけ取得|sid|ofs, cnt|
|get_subscribers|sid を 購読している ID を ofs から cnt 件だけ取得|sid|ofs, cnt|
|get_number_of_subscriptions|sid の購読数|sid||
|get_number_of_subscribers|sid の購読者数|sid||
|get_timeline|sid の tlid 番の Timeline を取得|sid||
|is_my_subscriber|tid は sid の購読者かどうか|sid,tid||
|is_my_subscription|tid は sid が購読しているかどうか|sid, tid||
|put|sid の tlid 番の Timeline に elm を追加|sid, tlid, elm||
|publish|sid を購読している id の tlid 番の Timeline に elm を追加|sid, tlid, elm||
|delete_id|sid の 全ての Timeline、購読・購読者関係を削除|sid||
|delete_tl|sid の tlid番の Timeline を削除|sid, tlid||

### 15分ツアー

ひとまず全ての API を使ってみて全容を把握する具体例。ターミナルで以下のようにコマンドを打っていけばよい。

~~~
########## 全てのデータを削除 (barusu) ##########
$ curl -d 'method=barusu&barusu=BARUSU!!!' localhost:9080 | jq "."
{
  "result": "BARUSU!!!",
  "message": "OK",
  "code": 20000
}

########## sid:1 が tid:2,3,4,5 を購読 (subscribe) ##########
$ curl -d 'method=subscribe&sid=1&tid=2' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}
$ curl -d 'method=subscribe&sid=1&tid=3' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}
$ curl -d 'method=subscribe&sid=1&tid=4' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}
$ curl -d 'method=subscribe&sid=1&tid=5' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## sid:1 が tid:5 の購読をやめる (unsubscribe) ##########
$ curl -d 'method=unsubscribe&sid=1&tid=5' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## sid:1 が購読している ID をすべて取得 (get_all_subscriptions) ##########
$ curl -d 'method=get_all_subscriptions&sid=1' localhost:9080 | jq "."
{
  "result": [
    2,
    3,
    4
  ],
  "message": "OK",
  "code": 20000
}

########## sid:2 を購読している ID をすべて取得 (get_all_subscribers) ##########
$ curl -d 'method=get_all_subscribers&sid=2' localhost:9080 | jq "."
{
  "result": [
    1
  ],
  "message": "OK",
  "code": 20000
}

########## sid:1 が購読している ID の最新を高々2件を取得 (get_subscriptions) ##########
$ curl -d 'method=get_subscriptions&sid=1&ofs=0&cnt=2' localhost:9080 | jq "."
{
  "result": [
    4,
    3
  ],
  "message": "OK",
  "code": 20000
}

########## sid:2 を購読している ID の最新を高々2件取得 (get_random_subscribers) ##########
$ curl -d 'method=get_subscribers&sid=2&ofs=0&cnt=2' localhost:9080 | jq "."
{
  "result": [
    1
  ],
  "message": "OK",
  "code": 20000
}


########## id:1 の購読数 (get_number_of_subscriptions) ##########
$ curl -d 'method=get_number_of_subscriptions&sid=1' localhost:9080 | jq "."
{
  "result": 3,
  "message": "OK",
  "code": 20000
}

########## id:2 の購読者数 (get_number_of_subscribers) ##########
$ curl -d 'method=get_number_of_subscribers&sid=2' localhost:9080 | jq "."
{
  "result": 1,
  "message": "OK",
  "code": 20000
}

########## id:1 は id:2 の購読者かどうか (is_my_subscriber) ##########
$ curl -d 'method=is_my_subscriber&sid=2&tid=1' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## tid は sid が購読しているかどうか (is_my_subscription) ##########
$ curl -d 'method=is_my_subscription&sid=1&tid=2' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## id:1 の tlid:1 番の Timeline に ##########
##########  elm: "uniqid:53a8f8d8e6453" を追加 (put) ##########
$ curl -d 'method=put&sid=1&tlid=1&elm=uniqid:53a8f8d8e6453' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

##########  もう一度、elm: "uniqid:53a8f8d8e6453" を追加 (put) ##########
$ curl -d 'method=put&sid=1&tlid=1&elm=uniqid:53a8f8fe0e95c' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## id:1 の tlid:1 番の Timeline を取得 (get_timeline) ##########
########## (先の2回の put が反映されているか確認) ##########
$ curl -d 'method=get_timeline&sid=1&tlid=1' localhost:9080 | jq "."
{
  "result": [
    "uniqid:53a8f8d8e6453",
    "uniqid:53a8f8fe0e95c"
  ],
  "message": "OK",
  "code": 20000
}

######### id:4 を購読している id群(今回は id:1 のみ) の tlid 番の Timeline に elm を追加	(publish)
$ curl -d 'method=publish&sid=4&tlid=1&elm=uniqid:53a8fc605a7cd' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## id:1 の tlid:1 番の Timeline を取得 (get_timeline)	##########
########## (先の2回の put と1回の publish が反映されているか確認)	##########
$ curl -d 'method=get_timeline&sid=1&tlid=1' localhost:9080 | jq "."
{
  "result": [
    "uniqid:53a8f8d8e6453",
    "uniqid:53a8f8fe0e95c",
    "uniqid:53a8fc605a7cd"
  ],
  "message": "OK",
  "code": 20000
}

######### sid の tlid番の Timeline を削除 (delete_tl) #########
$ curl -d 'method=delete_tl&sid=1&tlid=1' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## id:1 の tlid:1 番の Timeline を取得 (get_timeline)	##########
########## (先の削除が反映されているか確認)	##########

$ curl -d 'method=get_timeline&sid=1&tlid=1' localhost:9080 | jq "."
{
  "message": "An empty timeline.",
  "code": 60200
}

########## id:1 の 全ての Timeline、購読・購読者関係を削除 (delete_id) ##########
$ curl -d 'method=delete_id&sid=1' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## id:1 は id:2 の購読者かどうか (is_my_subscriber) ##########
######### (id:1 は削除されたので、購読者ではないはず) #########
$ curl -d 'method=is_my_subscriber&sid=2&tid=1' localhost:9080 | jq "."
{
  "result": false,
  "message": "OK",
  "code": 20000
}
~~~

### API詳細

それぞれの API について述べる。

#### barusu

全てのデータを削除する。危険なのでデバッグモードのときのみ動作する。さらにパラメータ「barusu」の value が「BARUSU!!!」のときに呪文がはなたれる。

- send

|パラメータ|例|
|:-:|:-:|
|barusu|BARUSU!!!|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。データは削除された|
|70100|失敗。barusu はデバッグモードでのみ有効|
|40001|失敗。パラメータが不正|
|59000|失敗。内部DBでエラーが発生|

#### subscribe

sid が tid を購読する。フォローとも言える。sid と tid が等しい場合は失敗する。

- send

|パラメータ|例|
|:-:|:-:|
|sid|1|
|tid|2|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。sid が tid を購読|
|40001|失敗。パラメータが不正|
|59000|失敗。内部DBでエラーが発生|
|40100|失敗。sid は すでに tid を購読済み|

#### unsubscribe

sid が tid の購読を解除する。アンフォローとも言える。sid と tid が等しい場合は失敗する。

- send

|パラメータ|例|
|:-:|:-:|
|sid|1|
|tid|2|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。sid が tid を購読|
|40001|失敗。パラメータが不正|
|59000|失敗。内部DBでエラーが発生|
|40101|失敗。そもそも sid は tid を購読していない。|


## ログ

- 2014-06-24
  - このプロジェクトを作成開始
- 2014-07-29
  - DBとの入出力をDBH.phpで一元化
  - 購読者の保存を、RedisのSetからSorted Setに変更
  - randomで取得できる系のメソッドを廃止し、任意の場所から任意の数だけ取れるメソッドを用意
  - RabbitMQと連携した。workerも作成済
  - ドキュメントを改善
  - テストを整備
  - プロジェクト名をTL404に決定。以前はmute cityという名。

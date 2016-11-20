# data_analysis
用PHP做資料分析演講範例程式

#### [系統環境]
1. PHP 7

2. PostgreSQL 9.6

3. Elasticsearch 5.0

4. Kibana 5.0

5. 設定名為data_analysis.local的VirtualHost，並在hosts檔案中追加對應IP

### [匯入Schema]
連線資訊寫在/application/config/database.php中，資料庫的sql放在/application/schema/中

### [如何使用爬蟲]
http://data_analysis.local/Crawler/run/[Service Name]

其中Service Name是對應到/application/library/Crawler/下的php檔，好比Service Name = Mobile01，就是對應到Mobile01.php

你也可將php http://data_analysis.local/Crawler/run/[Service Name]寫入到Crontab中，讓它可以定期運行

如果網站資料抓失敗會寫入到Analysis_Mobile01_Log資料表中

#### [About author]
Name    : Peter Cheng

Country : Taiwan

EMail 1 : teed7334@gmail.com

EMail 2 : teed7334@163.com

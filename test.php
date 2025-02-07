ln -s /etc/nginx/sites-available/ubonrat.hospital-erp.org  /etc/nginx/sites-enabled/ubonrat.hospital-erp.org
ln -s /etc/nginx/sites-available/nampong.hospital-erp.org  /etc/nginx/sites-enabled/nampong.hospital-erp.org
ln -s /etc/nginx/sites-available/umphang.hospital-erp.org  /etc/nginx/sites-enabled/umphang.hospital-erp.org
ln -s /etc/nginx/sites-available/lomkao.hospital-erp.org  /etc/nginx/sites-enabled/lomkao.hospital-erp.org
ln -s /etc/nginx/sites-available/pua.hospital-erp.org  /etc/nginx/sites-enabled/pua.hospital-erp.org
ln -s /etc/nginx/sites-available/thatphanom.hospital-erp.org  /etc/nginx/sites-enabled/thatphanom.hospital-erp.org
ln -s /etc/nginx/sites-available/yaha.hospital-erp.org  /etc/nginx/sites-enabled/yaha.hospital-erp.org
ln -s /etc/nginx/sites-available/denchai.hospital-erp.org  /etc/nginx/sites-enabled/denchai.hospital-erp.org
ln -s /etc/nginx/sites-available/phakhao.hospital-erp.org  /etc/nginx/sites-enabled/phakhao.hospital-erp.org
ln -s /etc/nginx/sites-available/thawangpha.hospital-erp.org  /etc/nginx/sites-enabled/thawangpha.hospital-erp.org
ln -s /etc/nginx/sites-available/chombung.hospital-erp.org  /etc/nginx/sites-enabled/chombung.hospital-erp.org



new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 10 THEN 1 ELSE 0 END) AS normal_10'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 10 THEN 1 ELSE 0 END) AS normal_10'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 11 THEN 1 ELSE 0 END) AS normal_11'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 12 THEN 1 ELSE 0 END) AS normal_12'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 1 THEN 1 ELSE 0 END) AS normal_1'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 2 THEN 1 ELSE 0 END) AS normal_2'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 3 THEN 1 ELSE 0 END) AS normal_3'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 4 THEN 1 ELSE 0 END) AS normal_4'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 5 THEN 1 ELSE 0 END) AS normal_5'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 6 THEN 1 ELSE 0 END) AS normal_6'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 7 THEN 1 ELSE 0 END) AS normal_7'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 8 THEN 1 ELSE 0 END) AS normal_8'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 9 THEN 1 ELSE 0 END) AS normal_9'),
            ])
            
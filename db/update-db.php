#!/bin/bash
docker exec -it dansai yii update-table &&
docker exec -it ubonrat yii update-table &&
docker exec -it lomkao  yii update-table &&
docker exec -it pua  yii update-table &&
docker exec -it thatphanom  yii update-table &&
docker exec -it yaha  yii update-table &&
docker exec -it denchai  yii update-table &&
docker exec -it phakhao  yii update-table &&
docker exec -it chombung yii update-table &&
docker exec -it erawan  yii update-table &&
docker exec -it pcrh  yii update-table &&
docker exec -it banphue  yii update-table


ALTER TABLE asset ADD COLUMN license_plate VARCHAR(20) DEFAULT NULL;
ALTER TABLE asset ADD COLUMN car_type VARCHAR(20) DEFAULT NULL;
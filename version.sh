#!/bin/bash
set -e

echo "===> Checkout tag ล่าสุด"
LATEST_TAG=$(git describe --tags --abbrev=0)  # เอา tag ล่าสุด เช่น v1.2.0
git checkout $LATEST_TAG

echo "===> สร้างไฟล์ version.php"
echo "<?php return '$LATEST_TAG';" > config/version.php

echo "===> ล้าง cache Yii2"
php yii cache/flush-all || true

echo "✅ Deploy สำเร็จ Version: $LATEST_TAG"

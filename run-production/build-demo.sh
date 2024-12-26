# build image แล้ว push ขึ้น docker hub
docker buildx build --platform linux/amd64  -t erp:demo ../. --load &&
# สร้าง tag
docker tag erp:demo patjawat/erp:demo &&
# สร้าง image ขึ้น docker hub
docker push patjawat/erp:demo &&
# ลบ image ที่เก่าออก
docker builder prune -a



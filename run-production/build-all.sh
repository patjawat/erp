# build image แล้ว push ขึ้น docker hub
docker buildx build --platform linux/amd64  -t erp:1.1 ../. --load &&
docker buildx build --platform linux/amd64  -t erp:demo ../. --load &&
# สร้าง tag
docker tag erp:demo patjawat/erp:demo &&
docker tag erp:1.1 patjawat/erp:1.1 &&
# สร้าง image ขึ้น docker hub
docker push patjawat/erp:demo &&
docker push patjawat/erp:1.1 &&
# ลบ image ที่เก่าออก
docker image prune -f





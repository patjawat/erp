# build image แล้ว push ขึ้น docker hub
docker buildx build --platform linux/amd64  -t erp:1.1 ../. --load &&
# สร้าง tag
docker tag erp:1.1 patjawat/erp:1.1 &&
# สร้าง image ขึ้น docker hub
docker push patjawat/erp:1.1 &&
# ลบ image ที่เก่าออก
docker image prune -f





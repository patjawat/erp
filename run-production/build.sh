# build image แล้ว push ขึ้น docker hub
docker buildx build --platform linux/amd64  -t erp:latest ../. --load &&
# สร้าง tag
docker tag erp:latest patjawat/erp:latest &&
# สร้าง image ขึ้น docker hub
docker push patjawat/erp:latest &&
# ลบ image ที่เก่าออก
docker image prune -f





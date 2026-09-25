#!/usr/bin/env bash
# =====================================================================
#  อัปเดตระบบ ERP คำสั่งเดียว (สำหรับ รพ. ที่รันด้วย Docker Compose)
#
#  ขั้นตอน: สำรองฐานข้อมูล → ดึงเวอร์ชันใหม่ → เปลี่ยน container → migration
#
#  วิธีใช้ (รันในโฟลเดอร์ที่มี docker-compose.yml และ .env):
#     ./update.sh                  อัปเดตตาม APP_IMAGE ใน .env
#     ./update.sh -f docker-compose-nginx.yml
#     ./update.sh --no-backup      ข้ามการสำรอง (ไม่แนะนำ)
#     ./update.sh -y               ไม่ถามอะไรเลย (ใช้กับ cron)
#
#  ดึงสคริปต์ครั้งแรก:
#     docker run --rm --entrypoint cat patjawat/erp:stable /app/scripts/update.sh > update.sh && chmod +x update.sh
#
#  ตัวแปรเสริม: SERVICE (ชื่อ service แอป ค่าเริ่มต้น app), BACKUP_DIR (./backups), KEEP_BACKUPS (7)
# =====================================================================
set -uo pipefail

SERVICE="${SERVICE:-app}"
BACKUP_DIR="${BACKUP_DIR:-./backups}"
KEEP_BACKUPS="${KEEP_BACKUPS:-7}"
STABLE_IMAGE="patjawat/erp:stable"
COMPOSE_FILES=()
DO_BACKUP=1
ASSUME_YES=0

while [ $# -gt 0 ]; do
    case "$1" in
        -f|--file) COMPOSE_FILES+=("-f" "$2"); shift 2 ;;
        --no-backup) DO_BACKUP=0; shift ;;
        -y|--yes) ASSUME_YES=1; shift ;;
        -h|--help) sed -n '2,18p' "$0"; exit 0 ;;
        *) echo "ไม่รู้จักตัวเลือก: $1 (ดู ./update.sh --help)"; exit 2 ;;
    esac
done

say()  { printf '\n\033[1;34m==> %s\033[0m\n' "$*"; }
ok()   { printf '\033[0;32m    %s\033[0m\n' "$*"; }
warn() { printf '\033[0;33m    ! %s\033[0m\n' "$*"; }
die()  { printf '\n\033[1;31mหยุดการอัปเดต: %s\033[0m\n' "$*"; exit 1; }

# ---------- ตรวจสภาพแวดล้อม ----------
[ -f .env ] || die "ไม่พบไฟล์ .env — ให้ cd เข้าโฟลเดอร์ที่มี docker-compose.yml ของระบบ ERP ก่อน"
if docker compose version >/dev/null 2>&1; then
    DC=(docker compose ${COMPOSE_FILES[@]+"${COMPOSE_FILES[@]}"})
elif command -v docker-compose >/dev/null 2>&1; then
    DC=(docker-compose ${COMPOSE_FILES[@]+"${COMPOSE_FILES[@]}"})
else
    die "ไม่พบคำสั่ง docker compose / docker-compose"
fi
"${DC[@]}" config --services 2>/dev/null | grep -qx "$SERVICE" \
    || die "ไม่พบ service '$SERVICE' ใน compose (ถ้าใช้ไฟล์ชื่ออื่นให้เติม -f <ไฟล์>)"

# อ่านค่าจาก .env โดยไม่ source (กันค่าที่มีช่องว่าง/อักขระพิเศษ)
env_get() {
    grep -E "^[[:space:]]*$1[[:space:]]*=" .env | tail -n 1 | cut -d= -f2- \
        | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//' -e 's/^"\(.*\)"$/\1/' -e "s/^'\(.*\)'\$/\1/" | tr -d '\r'
}

APP_IMAGE="$(env_get APP_IMAGE)"
[ -n "$APP_IMAGE" ] || die "ไม่ได้ตั้ง APP_IMAGE ใน .env (แนะนำ APP_IMAGE=$STABLE_IMAGE)"

# ---------- ช่องทาง stable / latest ----------
case "$APP_IMAGE" in
    *:latest|*/erp)
        warn "เครื่องนี้ใช้ $APP_IMAGE = รุ่นทดสอบ (ได้ทุก build ก่อนผ่านการทดสอบ)"
        warn "โรงพยาบาลทั่วไปควรใช้ $STABLE_IMAGE (เฉพาะเวอร์ชันที่ปล่อยแล้ว)"
        if [ "$ASSUME_YES" -eq 0 ] && [ -t 0 ]; then
            read -r -p "    เปลี่ยนเป็น $STABLE_IMAGE เลยไหม? [Y/n] " ans
            if [ -z "$ans" ] || [ "$ans" = "y" ] || [ "$ans" = "Y" ]; then
                cp .env ".env.bak.$(date +%Y%m%d%H%M%S)"
                sed -i.tmp -E "s#^([[:space:]]*APP_IMAGE[[:space:]]*=).*#\1$STABLE_IMAGE#" .env && rm -f .env.tmp
                APP_IMAGE="$STABLE_IMAGE"
                ok "แก้ .env แล้ว (สำรองไฟล์เดิมเป็น .env.bak.*)"
            fi
        fi
        ;;
esac
ok "ช่องทาง: $APP_IMAGE"

app_running() { [ -n "$("${DC[@]}" ps -q "$SERVICE" 2>/dev/null)" ]; }
app_version() { "${DC[@]}" exec -T "$SERVICE" php -r 'echo require "/app/config/version.php";' 2>/dev/null; }

OLD_VERSION="-"
if app_running; then OLD_VERSION="$(app_version || echo -)"; fi
ok "เวอร์ชันปัจจุบัน: $OLD_VERSION"

# ---------- 1) สำรองฐานข้อมูล ----------
BACKUP_FILE=""
if [ "$DO_BACKUP" -eq 1 ]; then
    say "1/4 สำรองฐานข้อมูล"
    DSN="$(env_get DB_DSN)"
    DB_HOST="$(printf '%s' "$DSN" | sed -n 's/.*host=\([^;]*\).*/\1/p')"
    DB_PORT="$(printf '%s' "$DSN" | sed -n 's/.*port=\([^;]*\).*/\1/p')"
    DB_NAME="$(printf '%s' "$DSN" | sed -n 's/.*dbname=\([^;]*\).*/\1/p')"
    [ -n "$DB_HOST" ] && [ -n "$DB_NAME" ] || die "อ่าน host/dbname จาก DB_DSN ใน .env ไม่ได้"
    ok "ฐาน $DB_NAME ที่ $DB_HOST${DB_PORT:+:$DB_PORT}"

    mkdir -p "$BACKUP_DIR"
    BACKUP_FILE="$BACKUP_DIR/erp_${DB_NAME}_$(date +%Y%m%d_%H%M%S)_${OLD_VERSION}.sql"
    # รัน mysqldump ใน container แอป (มี mysql client อยู่แล้ว) ใช้ user/รหัสจาก env ของ container
    # ใช้ได้ทั้งฐานในชุด Docker (mysqlDB) และฐานของโรงพยาบาลเอง
    DUMP_SCRIPT='MYSQL_PWD="$DB_PASS" exec mysqldump -h "$1" -P "${2:-3306}" -u "$DB_USERNAME" --single-transaction --routines --triggers --no-tablespaces --default-character-set=utf8mb4 "$3"'
    if app_running; then
        "${DC[@]}" exec -T "$SERVICE" sh -c "$DUMP_SCRIPT" _ "$DB_HOST" "$DB_PORT" "$DB_NAME" > "$BACKUP_FILE"
    else
        "${DC[@]}" run --rm --no-deps -T "$SERVICE" sh -c "$DUMP_SCRIPT" _ "$DB_HOST" "$DB_PORT" "$DB_NAME" > "$BACKUP_FILE"
    fi
    rc=$?
    if [ $rc -ne 0 ] || [ ! -s "$BACKUP_FILE" ]; then
        rm -f "$BACKUP_FILE"
        die "สำรองฐานข้อมูลไม่สำเร็จ (ยังไม่ได้เปลี่ยนอะไร) — ตรวจ DB_DSN/DB_USERNAME/DB_PASS ใน .env หรือใช้ --no-backup ถ้าสำรองเองแล้ว"
    fi
    if command -v gzip >/dev/null 2>&1; then gzip -f "$BACKUP_FILE" && BACKUP_FILE="$BACKUP_FILE.gz"; fi
    ok "สำรองแล้ว: $BACKUP_FILE ($(du -h "$BACKUP_FILE" | cut -f1))"
    # เก็บไว้ KEEP_BACKUPS ชุดล่าสุด
    ls -1t "$BACKUP_DIR"/erp_"${DB_NAME}"_*.sql* 2>/dev/null | tail -n +"$((KEEP_BACKUPS + 1))" | while read -r f; do rm -f "$f"; done
else
    say "1/4 ข้ามการสำรองฐานข้อมูล (--no-backup)"
fi

# ---------- 2) ดึงเวอร์ชันใหม่ ----------
say "2/4 ดึง $APP_IMAGE"
"${DC[@]}" pull "$SERVICE" || die "ดึง image ไม่สำเร็จ — ตรวจอินเทอร์เน็ต/การเข้าถึง Docker Hub"

# เก็บสคริปต์รุ่นใหม่จาก image ไว้ใช้ครั้งหน้า (mv = ไฟล์ใหม่ ไม่ทับไฟล์ที่กำลังรัน)
if docker run --rm --entrypoint cat "$APP_IMAGE" /app/scripts/update.sh > "$0.new" 2>/dev/null && [ -s "$0.new" ]; then
    if ! cmp -s "$0" "$0.new"; then chmod +x "$0.new" && mv "$0.new" "$0" && ok "อัปเดตสคริปต์ update.sh เป็นรุ่นใหม่แล้ว (มีผลครั้งหน้า)"; fi
fi
rm -f "$0.new"

# ---------- 3) เปลี่ยน container ----------
say "3/4 เปลี่ยน container แอปเป็นเวอร์ชันใหม่ (ไม่แตะฐานข้อมูล)"
"${DC[@]}" up -d --no-deps --force-recreate "$SERVICE" || die "สร้าง container ใหม่ไม่สำเร็จ"
for _ in $(seq 1 30); do
    "${DC[@]}" exec -T "$SERVICE" php -r 'echo 1;' >/dev/null 2>&1 && break
    sleep 2
done
NEW_VERSION="$(app_version || echo -)"
ok "container พร้อมแล้ว เวอร์ชัน $NEW_VERSION"

# ---------- 4) migration ----------
say "4/4 ปรับโครงสร้างฐานข้อมูล (migration)"
if ! "${DC[@]}" exec -T "$SERVICE" php yii migrate --interactive=0; then
    printf '\n\033[1;31mmigration ไม่สำเร็จ\033[0m\n'
    echo "  - ถ้าขึ้น Access denied/command denied: user ฐานข้อมูลต้องมีสิทธิ์ CREATE/ALTER/DROP/INDEX"
    echo "  - แก้แล้วรันซ้ำได้: ${DC[*]} exec $SERVICE php yii migrate --interactive=0"
    [ -n "$BACKUP_FILE" ] && echo "  - ไฟล์สำรองก่อนอัปเดต: $BACKUP_FILE"
    exit 1
fi

say "อัปเดตเสร็จ: $OLD_VERSION → $NEW_VERSION"
[ "$OLD_VERSION" = "$NEW_VERSION" ] && warn "เลขเวอร์ชันเท่าเดิม = ยังไม่มีเวอร์ชันใหม่ในช่องทาง $APP_IMAGE"
exit 0

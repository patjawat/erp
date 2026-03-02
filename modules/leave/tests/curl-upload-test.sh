#!/bin/bash
# ทดสอบอัปโหลดเทมเพลต PDF ด้วย curl
# ใช้: ./modules/leave/tests/curl-upload-test.sh http://localhost:11447
# หรือจากใน Docker: ./modules/leave/tests/curl-upload-test.sh http://localhost/leave/setting/upload-template
set -e
BASE="${1:-http://localhost:11447}"
URL="${BASE}/leave/setting/upload-template"
PDF="${2:-/tmp/mini.pdf}"
if [ ! -f "$PDF" ]; then
  printf '%%PDF-1.4\n1 0 obj\nendobj\nxref\nstartxref\n%%%%EOF' > "$PDF"
  echo "Created $PDF"
fi
echo "POST $URL with file $PDF"
RESP=$(curl -s -w "\n%{http_code}" -X POST "$URL" \
  -F "template_pdf=@$PDF" \
  -F "_csrf=$(curl -s -c - "$BASE" 2>/dev/null | grep -oP 'yii_csrf[^\s]*' | head -1 | cut -d$'\t' -f7 2>/dev/null || echo '')" \
  -H "Accept: application/json" \
  -H "X-Requested-With: XMLHttpRequest")
BODY=$(echo "$RESP" | head -n -1)
CODE=$(echo "$RESP" | tail -1)
echo "HTTP $CODE"
echo "$BODY" | head -5
if echo "$BODY" | grep -q '"success":true'; then
  echo "OK: Upload success"
  exit 0
fi
if echo "$BODY" | grep -q '"success": false'; then
  echo "FAIL: success false in response"
  exit 1
fi
echo "Response (first 500 chars): ${BODY:0:500}"
exit 0

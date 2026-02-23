# การวิเคราะห์: PWA + การแจ้งเตือน (Push Notification) สำหรับ ERP

## สรุปสั้นๆ

**ทำได้ครับ** ทั้งสองส่วน (PWA บนมือถือ + Push แจ้งเตือนถึง user) โดยโปรเจกต์เป็น Yii2 + Bootstrap 5 อยู่แล้ว มี viewport สำหรับมือถือ และมีระบบแจ้งเตือนในแอป (ApproveHelper) อยู่ — แค่เพิ่ม PWA (manifest + service worker) และ Web Push ให้ส่งถึงอุปกรณ์เมื่อมีเหตุการณ์แจ้งเตือน

---

## 1. สถานะปัจจุบันของโปรเจกต์

### 1.1 รองรับมือถือ (Mobile) แล้วบางส่วน

- **Layout**: ใช้ `meta viewport` แล้วในทุก theme (เช่น `themes/v4/layouts/main.php`, `themes/v3/layouts/theme-h/main.php`)
  - `width=device-width, initial-scale=1, shrink-to-fit=no`
- **UI**: ใช้ Bootstrap 5 → responsive อยู่แล้ว (grid, breakpoints)
- **ยังไม่มี**: PWA manifest, Service Worker → ยังติดตั้งเป็นแอปบนมือถือและใช้แบบ offline ไม่ได้

### 1.2 การแจ้งเตือน (Notification) ปัจจุบัน

- **ในแอปเท่านั้น**: ใช้ `ApproveHelper::Info()` รวมจำนวนรออนุมัติ (วันลา, จัดซื้อ, stock, development ฯลฯ)
- แสดงใน header เป็น dropdown (เช่น `themes/v3/layouts/theme-v/notification.php`) — user ต้องเปิดเว็บอยู่ถึงจะเห็น
- **ยังไม่มี**: Web Push — ไม่มีการส่งการแจ้งเตือนไปที่อุปกรณ์เมื่อปิดเบราว์เซอร์/ปิดแท็บ

### 1.3 โครงสร้างที่เกี่ยวข้อง

- **Frontend**: `assets/AppAsset.php` (CSS/JS), layout หลักใน `themes/v4/`, `themes/v3/`
- **Backend**: PHP (Yii2), มี Telegram component สำหรับส่งข้อความแล้ว
- **จุดที่เกิดเหตุการณ์แจ้งเตือน**: อนุมัติต่างๆ (leave, purchase, stock, development) ที่เชื่อมกับ `ApproveHelper`

---

## 2. สิ่งที่ต้องเพิ่มเพื่อ PWA (หน้าจอมือถือ + ติดตั้งเป็นแอป)

| รายการ | รายละเอียด |
|--------|------------|
| **Web App Manifest** | ไฟล์ `manifest.json` (หรือ `.webmanifest`) กำหนดชื่อแอป, ไอคอน (อย่างน้อย 192x192, 512x512), `start_url`, `display: standalone`, `theme_color` |
| **Service Worker** | ไฟล์ JS (เช่น `sw.js`) อยู่ใต้ `web/` — ใช้สำหรับ cache หน้า/asset และรองรับ offline (และใช้รับ push ในขั้นถัดไป) |
| **ลิงก์ใน Layout** | ใน `<head>`: `<link rel="manifest" href="/manifest.json">` และ meta เพิ่มเช่น `theme-color`, `apple-mobile-web-app-capable`, apple-touch-icon |
| **HTTPS** | PWA ต้องใช้ HTTPS (และ Push ต้องใช้ HTTPS ด้วย) |

ผลลัพธ์: ผู้ใช้สามารถ “Add to Home Screen” บนมือถือได้ และเปิดแบบเต็มจอเหมือนแอป; ถ้าต้องการให้ใช้ offline ได้บ้าง ต้องออกแบบ cache ใน service worker (เช่น cache-first สำหรับ static, network-first สำหรับ API).

---

## 3. สิ่งที่ต้องเพิ่มเพื่อ Push Notification ไปหา User

| ชั้น | รายละเอียด |
|-----|------------|
| **Frontend (Browser)** | 1) ขอสิทธิ์ `Notification.requestPermission()` 2) สร้าง subscription ผ่าน `PushManager.subscribe()` (ต้องใช้ VAPID public key) 3) ส่ง subscription object (endpoint + keys) ไปเก็บที่ backend (ผูกกับ user id) |
| **Service Worker** | ลงทะเบียนรับ `push` event ใน `sw.js` แล้วแสดง `registration.showNotification()` (หัวข้อ, ข้อความ, icon, คลิกเปิด URL ได้) |
| **Backend (Yii2)** | 1) สร้าง VAPID key pair (ครั้งเดียว) 2) ตารางเก็บ subscription ต่อ user (endpoint, p256dh, auth) 3) เมื่อมีเหตุการณ์แจ้งเตือน (เช่น มีรายการรออนุมัติที่ต้องส่งให้ user) ให้เรียก Web Push API ส่ง payload ไปยัง endpoint ของแต่ละ subscription (ใช้ library เช่น [web-push-php](https://github.com/web-push-libs/web-push-php)) |
| **จุดเชื่อมกับระบบเดิม** | ใช้เหตุการณ์เดียวกับที่ `ApproveHelper` ใช้ (leave, purchase, stock, development ฯลฯ) — เมื่อมีการสร้าง/อัปเดตรายการที่ “รอการอนุมัติของ user คนนี้” ให้ trigger การส่ง push ไปยัง subscription ของ user นั้น |

หมายเหตุ:

- **การแจ้งเตือนไปถึง user** = การส่งจาก server ไปยัง “push service” (ของ browser เช่น FCM สำหรับ Chrome) จากนั้น push service จะส่งไปยังอุปกรณ์แม้แอปปิดอยู่ — นี่คือ “การแจ้งเตือนไปหา user” ที่ต้องการ
- **สิทธิ์**: User ต้องอนุญาต “Notifications” ใน browser ก่อน จึงจะได้ subscription และส่ง push ได้

---

## 4. แนวทาง Implement แนะนำ

### Phase 1: PWA พื้นฐาน (มือถือ + ติดตั้งเป็นแอป)

1. สร้าง `web/manifest.json` (หรือ `manifest.webmanifest`) และไอคอน 192, 512
2. สร้าง `web/sw.js` รุ่นแรก: แค่ลงทะเบียนและ cache หลัก (หรือ cache หน้าแรก + static assets) เพื่อให้ผ่าน PWA criteria
3. ใน layout หลัก (v4 และ/หรือ v3 ที่ใช้จริง): เพิ่ม `<link rel="manifest">`, meta theme-color, apple-touch-icon
4. ในหน้าแรกหรือหลัง login: ลงทะเบียน service worker ด้วย `navigator.serviceWorker.register('/sw.js')`
5. ทดสอบผ่าน HTTPS ว่า “Add to Home Screen” ใช้ได้และเปิดแบบ standalone ได้

### Phase 2: Push Notification

1. **Backend**
   - ติดตั้ง `minishlink/web-push` (หรือ library Web Push อื่นสำหรับ PHP)
   - สร้าง VAPID keys เก็บใน config (ไม่ commit private key)
   - สร้างตาราง `user_push_subscriptions` (user_id, endpoint, p256dh, auth, created_at)
   - สร้าง API/action: (1) รับ subscription จาก frontend แล้วบันทึก (2) helper/service สำหรับส่ง push เมื่อมีเหตุการณ์แจ้งเตือน (รับ user_id, title, body, url)
2. **Frontend**
   - หลัง login: ถ้ามีสิทธิ์ notification แล้ว ให้ subscribe และส่ง subscription ไป backend; ถ้ายังไม่มีสิทธิ์ แสดงปุ่ม “เปิดการแจ้งเตือน” แล้วค่อยขอสิทธิ์ + subscribe + ส่ง subscription
   - ใน `sw.js`: เพิ่ม listener `push` → `event.waitUntil(registration.showNotification(...))` และ `notificationclick` → เปิด URL ที่ส่งมา
3. **เชื่อมกับ ApproveHelper**
   - ณ จุดที่สร้าง/อัปเดต “รออนุมัติ” (เช่น leave, purchase, stock) ให้เรียก service ส่ง push ไปยัง subscription ของ user ที่เป็นผู้อนุมัติ (ใช้ข้อมูลเดียวกับที่ ApproveHelper ใช้ เช่น `emp_id`)

### Phase 3 (ถ้าต้องการ)

- ปรับ service worker ให้ cache หน้าสำคัญสำหรับ offline
- หน้า “การตั้งค่าแจ้งเตือน” ให้ user เปิด/ปิดประเภทการแจ้งเตือนได้
- รองรับหลายอุปกรณ์ต่อ user (หลาย subscription ต่อ user_id)

---

## 5. ข้อควรระวัง

- **HTTPS**: ทั้ง PWA และ Web Push ต้องใช้ HTTPS (localhost ใช้ได้สำหรับพัฒนา)
- **Browser support**: Web Push รองรับ Chrome, Firefox, Edge, Safari 16.4+ บน iOS; ตรวจสอบก่อนขอสิทธิ์
- **VAPID**: ใช้ key pair เดียวทั้งโปรเจกต์; เก็บ private key ใน env/config ลับ
- **ความถี่**: ไม่ส่ง push บ่อยเกินไป (เช่น รวมเป็น digest หรือส่งเฉพาะเหตุการณ์สำคัญ) เพื่อไม่ให้ user ปิดการแจ้งเตือน

---

## 6. สรุปคำตอบ

| คำถาม | คำตอบ |
|--------|--------|
| รองรับ PWA บนมือถือได้ไหม? | **ได้** — เพิ่ม manifest + service worker + ลิงก์ใน layout และใช้ HTTPS |
| แจ้งเตือนไปถึง user (เมื่อมี notify) ได้ไหม? | **ได้** — ใช้ Web Push: เก็บ subscription ต่อ user ที่ backend และส่ง push ตอนมีเหตุการณ์แจ้งเตือน (เชื่อมกับ logic เดียวกับ ApproveHelper) |
| โปรเจกต์พร้อมแค่ไหน? | Viewport และ responsive มีแล้ว; ยังไม่มี manifest, service worker และ Web Push — ต้องเพิ่มทั้ง 3 ส่วน |

ถ้าต้องการให้ช่วยลงมือ implement เป็นขั้นตอน (เช่น เริ่มจาก manifest + sw.js + ลิงก์ใน layout) บอกได้ว่าจะให้เริ่มจาก theme ไหน (v4 หรือ v3) และ path ที่ต้องการใช้เป็น start_url ของ PWA (เช่น `/site` หรือ `/me`).

---

## 7. ทดสอบผ่าน ngrok

**โดยปกติไม่ต้องแก้โค้ด** เมื่อรันผ่าน ngrok เพราะ:

- **HTTPS**: ngrok ให้ HTTPS อยู่แล้ว → PWA และ Service Worker ใช้ได้
- **Path**: manifest ใช้ `start_url: "/site"` (path เท่านั้น) → browser จะใช้ origin ปัจจุบัน (เช่น `https://xxxx.ngrok.io`) อัตโนมัติ
- **@web**: Yii จะ resolve เป็น URL ของ request → เปิดผ่าน ngrok จะได้ base เป็น ngrok URL อยู่แล้ว
- **Service Worker**: ลงทะเบียนต่อ origin (ngrok URL) → ใช้ได้กับ origin นั้น

**ที่อาจต้องตรวจ (ถ้ามีปัญหา):**

1. **Yii host / baseUrl**: ถ้ามีการตั้ง `baseUrl` หรือ `scriptUrl` แบบคงที่ (เช่น เฉพาะ localhost) อาจต้องปรับให้รองรับ host ของ ngrok หรือใช้ค่าจาก request ตามปกติ
2. **Session/Cookie**: ถ้ากำหนด cookie domain แบบตายตัว (เช่น `localhost`) ให้ลบหรือไม่กำหนด domain เพื่อให้ cookie ใช้กับ host ปัจจุบัน (ngrok)
3. **Free ngrok**: URL จะเปลี่ยนทุกครั้งที่รัน → PWA ที่ติดตั้งจาก URL เก่าจะเป็นคนละแอปกับ URL ใหม่ (เป็นเรื่องปกติของ free tier)

สรุป: **เปิดเว็บผ่าน ngrok แล้วทดสอบ PWA / Add to Home Screen ได้เลย ไม่ต้องแก้ manifest หรือ sw.js**

---

## 8. วิธีทดสอบการแจ้งเตือน

### 8.1 แจ้งเตือนในแอป (ที่มีอยู่แล้ว)

- ล็อกอินด้วย user ที่มีสิทธิ์อนุมัติ (วันลา / จัดซื้อ / stock ฯลฯ)
- ให้มีรายการรออนุมัติที่ส่งถึง user คนนี้ (เช่น สร้างใบลาจาก user อื่น แล้วให้ approver เป็น user ที่ล็อกอินอยู่)
- เปิดเมนูด้านบน → ไอคอน **กระดิ่ง** จะมีตัวเลขและ dropdown แสดงรายการแจ้งเตือน

### 8.2 แจ้งเตือนแบบ Push (ทดสอบจากเบราว์เซอร์)

**วิธีที่ 1: ปุ่มในหน้า**

- เปิดเว็บ theme v4 (HTTPS หรือ localhost)
- ลงล่างไปที่ footer → กดลิงก์ **「🔔 ทดสอบแจ้งเตือน」**
- ครั้งแรกเบราว์เซอร์จะถามสิทธิ์ → กด **อนุญาต**
- จะมี popup การแจ้งเตือน (ERP Hospital / นี่คือการทดสอบการแจ้งเตือน)
- ลอง **ย่อแท็บหรือเปิดแท็บอื่น** แล้วกดปุ่มอีกครั้ง → การแจ้งเตือนยังขึ้น (มาจาก Service Worker)

**วิธีที่ 2: จาก Console**

- เปิด DevTools (F12) → แท็บ **Console**
- พิมพ์: `erpTestNotification()` แล้วกด Enter
- หรือส่งข้อความเอง: `erpTestNotification('หัวข้อ', 'ข้อความทดสอบ')`

**วิธีที่ 3: ส่ง Push จาก DevTools (เหมือน server ส่งมา)**

- เปิด DevTools → แท็บ **Application** (Chrome) หรือ **Storage** (Firefox)
- ซ้ายมือเลือก **Service Workers**
- เลือก sw.js ของไซต์นี้ → กดปุ่ม **Push** (หรือ "Send test push")
- จะเห็นการแจ้งเตือนขึ้น (หัวข้อ "ERP", ข้อความ "มีการแจ้งเตือนใหม่")
- กดการแจ้งเตือน → ควรเปิด/โฟกัสหน้าแอป

หมายเหตุ: การแจ้งเตือนจาก server จริง (เมื่อมีรายการรออนุมัติ) ต้องทำ Phase 2 (backend เก็บ subscription + ส่งผ่าน Web Push API) จึงจะส่งถึงอุปกรณ์ได้เมื่อปิดเบราว์เซอร์

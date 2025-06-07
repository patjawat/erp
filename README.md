<p align="center">
    <h1 align="center">Office ERP</h1>
    <br>
</p>

Yii 2 Basic Project Template is a skeleton [Yii 2](http://www.yiiframework.com/) application best for
rapidly creating small projects.


REQUIREMENTS
------------

The minimum requirement by this project template that your Web server supports PHP 5.6.0.


INSTALLATION
------------

### Install via Composer
การติดตั้ง
~~~
1. copy file example_db.php to db.php
2. run คำสั่ง composer update --ignore-platform-reqs
~~~

Now you should be able to access the application through the following URL, assuming `basic` is the directory
directly under the Web root.

### Install with Docker

Update your vendor packages

    docker-compose run --rm php composer update --prefer-dist --ignore-platform-reqs
    
Run the installation triggers (creating cookie validation code)

    docker-compose run --rm php composer install --ignore-platform-reqs
    
Start the container

    docker-compose up -d
    
You can then access the application through the following URL:

    http://127.0.0.1:83

You can then access phpmyadmin:

    http://127.0.0.1:8500

**NOTES:** 
- Minimum required Docker engine version `17.04` for development (see [Performance tuning for volume mounts](https://docs.docker.com/docker-for-mac/osxfs-caching/))
- The default configuration uses a host-volume in your home directory `.docker-composer` for composer caches


CONFIGURATION
-------------

### Database

Edit the file `config/db.php` with real data, for example:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=erp',
    'username' => 'root',
    'password' => 'docker',
    'charset' => 'utf8',
];
```

## Migrate database
yii migrate
migrate Down step

yii migrate/down 1

**NOTES:**
- Yii won't create the database for you, this has to be done manually before you can access it.
- Check and edit the other files in the `config/` directory to customize your application as required.
- Refer to the README in the `tests` directory for information specific to basic application tests.



Docker Build Image
สร้าง File Image
docker build -t erp:v1 .

หากคุณต้องการส่งออก Docker image จากเครื่องหนึ่งไปใช้กับเครื่องอื่น มีขั้นตอนดังนี้

docker save -o <path-to-tar-file> <image-name>:<tag>

ตัวอย่าง: docker save -o yii2-app.tar yii2-app:latest

ตัวอย่างการใช้ scp ส่งไปยังเครื่องอื่น:
scp yii2-app.tar user@remote-server:/path/to/destination

Import Docker Image บนเครื่องใหม่
docker load -i yii2-app.tar

ตรวจสอบว่าการ Import สำเร็จหรือไม่
docker images

 รัน Docker Image บนเครื่องใหม่
 docker run -d -p 8080:80 yii2-app:latest

 # การนำเข้าข้อมูลหนังสือสารบรรณ
yii import-document
yii import-document/upload-file
yii import-leave
yii import-meeting
yii import-vehicle


yii import-leave && yii import-document && yii import-meeting && yii import-vehicle && yii import-development



https://www.canva.com/ai/code/thread/a1329017-9e7f-4b54-a2b5-dd10c5acf3bd



ออกแบบระบบการแจ้งซ่อมสำหรับ user ฟอมการแจ้งซ่อม ระบุอาการเสีย/ความต้องการ หน่วยงานผู้แจ้ง 
งานซ่อมบำรุง
สถานที่อื่นๆ  ความเร่งด่วน เบอร์โทร เพิ่มเติม เลือกช่างที่ต้องการ ซ่อมทั่วไป ซ่อมครุภัณฑ์  สามารถเลือกส่ง งานซ่อมบำรุง ศูนย์คอมพิวเตอร์ ศูนย์เครื่องมือแพทย์ สามารถติดตามงานซ่อม เมื่อเสร็จแล้วให้คะแนนได้ bootstrap5 jquery  apexcharts สามารถทำงานได้ทุกเมนู


ออกแบบระบบการแจ้งซ่อมสำหรับ user ฟอมการแจ้งซ่อม ระบุอาการเสีย/ความต้องการ หน่วยงานผู้แจ้ง 
งานซ่อมบำรุง
สถานที่อื่นๆ  ความเร่งด่วน เบอร์โทร เพิ่มเติม เลือกช่างที่ต้องการ ซ่อมทั่วไป ซ่อมครุภัณฑ์  สามารถเลือกส่ง งานซ่อมบำรุง ศูนย์คอมพิวเตอร์ ศูนย์เครื่องมือแพทย์ สามารถติดตามงานซ่อม เมื่อเสร็จแล้วให้คะแนนได้ สามารถทำงานได้ทุกเมนู แยกการดูแลงานซ่อมของแต่ละงาน bootstrap5 jquery  apexcharts 

### ภาพรวมทรัพยากรบุคคล
https://dansaihospital.my.canva.site/hr-dashboard-web-app-development-summary

### ระบบติดตามอาชีวอนามัยและความปลอดภัย
https://dansaihospital.my.canva.site/dagnh8wv-us

### profile
https://www.canva.com/ai/code/thread/62a18386-9c98-4c12-b680-bd113446d996

### ห้องปชุม
https://www.canva.com/ai/code/thread/e120d73e-48a8-4022-a500-b613578ebfad

### ระบบบริหารงานซ่อมบำรุง
https://dansaihospital.my.canva.site/bootstrap
https://gemini.google.com/share/7236f4657713

### ระบบบันทึกทะเบียนครุภัณฑ
https://www.canva.com/ai/code/thread/ea327072-db24-488e-a38b-a838d74ec8e1
https://www.canva.com/ai/code/thread/ba6b2ae4-bc5b-443a-8ed2-7c92798ae56a



### **Color Palette Control**
```css
Apply gradient color scheme:
- Primary cards: Blue-purple (#667eea → #764ba2), Pink-red (#f093fb → #f5576c), Cyan-blue (#4facfe → #00f2fe), Green-mint (#43e97b → #38f9d7)
- Background: Multi-layer blue gradients with pastel transitions
- Shadows: Color-matched to card gradients with 0.3-0.4 opacity
- Text: Dark charcoal (#212121) with gradient text effects on numbers
```
### **Typography & Spacing**
```css
Font system:
- Primary: 'Noto Sans Thai', sans-serif for full Thai language support
- Weights: 300 (light), 400 (regular), 500 (medium), 600 (semi-bold), 700 (bold)
- Scale: 12px (caption) → 14px (body) → 16px (subtitle) → 20px (title) → 24px (heading) → 36px (display)
- Spacing: 8px base unit system (8, 16, 24, 32, 48px)
```

## 🔄 Animation & Interaction Styles
### **Hover & Focus Effects**
```css
Interaction patterns:
- Cards: transform: translateY(-8px) scale(1.02) on hover
- Buttons: Material ripple effect with 600ms linear animation
- Icons: subtle rotation (5-10deg) or scale(1.1) transforms
- Timing: cubic-bezier(0.4, 0, 0.2, 1) for Material motion
- Duration: 200-300ms for micro-interactions, 600ms for significant changes
```
### **Transition Specifications**
```css
Animation system:
- Page transitions: opacity + translateY(20px) transforms
- Staggered loading: 100ms delay between elements
- Smooth curves: cubic-bezier easing functions
- Performance: transform and opacity only, avoid layout triggers
```
## 📐 Layout & Structure Styles
### **Grid & Spacing System**
```css
Layout specifications:
- Stats grid: repeat(auto-fit, minmax(280px, 1fr)) with 24px gaps
- Card padding: 28px internal spacing
- Border radius: 16px for modern appearance
- Container margins: 24px on desktop, 16px on mobile
```
### **Component Sizing**
```css
Element dimensions:
- Icons: 24px standard, 48px for stat card icons
- Avatars: 32px (header), 44px (activity list)
- Buttons: min-height 36px, padding 12px 24px
- Input fields: 56px height for Material spec compliance
```
## 🎭 Visual Effects & Treatments
### **Glassmorphism & Blur Effects**
```css
Modern glass effects:
- backdrop-filter: blur(10px) on cards and overlays
- background: rgba(255, 255, 255, 0.95) for semi-transparency
- border: 1px solid rgba(255, 255, 255, 0.2) for subtle definition
- Gradient overlays: linear-gradient with 0.03 opacity color tints
```
### **Gradient Treatments**
```css
Gradient applications:
- Card backgrounds: Linear gradients with white base + colored tints
- Icons: Solid gradients with matching shadow colors
- Text effects: Background-clip text for premium number displays
- Top borders: 4px height gradient strips on cards
```
## 📱 Responsive Behavior Styles
### **Breakpoint Adaptations**
```css
Device-specific styles:
- Mobile (<768px): Collapse sidebar to 72px, single column stats, reduced padding
- Tablet (768-1024px): Maintain layout with adjusted spacing
- Desktop (>1024px): Full layout with optimal spacing and hover states
```
### **Touch-Friendly Adjustments**
```css
Mobile optimizations:
- Minimum touch targets: 44px x 44px
- Increased padding on interactive elements
- Simplified hover states for touch devices
- Gesture-friendly navigation patterns
```
## 🎨 Brand & Theme Styling
### **Color Psychology Application**
```css
Semantic color usage:
- Success/Growth: Green gradients (#43e97b → #38f9d7)
- Revenue/Money: Pink-red gradients (#f093fb → #f5576c)  
- Users/People: Blue-purple gradients (#667eea → #764ba2)
- Performance: Cyan-blue gradients (#4facfe → #00

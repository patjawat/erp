<?php
/* @var $this yii\web\View */
use yii\helpers\Url;

$this->title = 'Hospital ERP NextGen';

// 1. ลงทะเบียน Tailwind CSS และ Font
$this->registerJsFile('https://cdn.tailwindcss.com');
$this->registerCss("
    @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap');
    body, .font-sans { font-family: 'Sarabun', sans-serif; }
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
    .animate-in { animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
");

// Mockup Data
$user_name = Yii::$app->user->identity->profile->firstname ?? 'เดชา สายบุญตั้ง'; 
?>

<div id="root">
    <div class="min-h-screen bg-[#f8fafc] flex flex-col font-sans text-slate-600">
        
        <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-200/60 px-6 py-4">
            <div class="max-w-[1600px] mx-auto w-full flex items-center justify-between">
                <div class="flex items-center gap-8">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M2 12h20M12 2a10 10 0 0 1 10 10M12 22a10 10 0 0 0 10-10M2 12a10 10 0 0 1 10-10"/></svg>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-lg font-black text-slate-800 leading-none">HOSPITAL ERP</h1>
                            <span class="text-[10px] font-bold text-slate-400 tracking-[0.2em] uppercase">Enterprise System</span>
                        </div>
                    </div>
                    <div class="hidden lg:flex items-center bg-slate-100/50 rounded-2xl px-4 py-2.5 w-96 group focus-within:ring-2 ring-blue-500/20 transition-all border border-transparent focus-within:bg-white focus-within:border-blue-100">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 group-focus-within:text-blue-500"></i>
                        <input placeholder="ค้นหาเลขที่หนังสือ หรือ หัวข้อเรื่อง..." class="bg-transparent border-none outline-none ml-3 text-sm w-full text-slate-600 placeholder:text-slate-400" type="text">
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button class="hidden sm:flex items-center gap-2 px-3 py-1.5 text-[10px] font-bold text-emerald-600 bg-emerald-50 rounded-lg border border-emerald-100">
                        <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>Server Online
                    </button>
                    <button class="p-2.5 text-slate-400 hover:bg-slate-100 rounded-full transition-colors relative">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>
                    <div class="h-8 w-[1px] bg-slate-200 mx-1"></div>
                    <div class="flex items-center gap-3 pl-2 cursor-pointer hover:bg-slate-50 p-1 rounded-xl transition-all">
                        <div class="text-right hidden md:block">
                            <p class="text-xs font-bold text-slate-800"><?= $user_name ?></p>
                            <p class="text-[10px] text-slate-400 font-medium italic">General Admin</p>
                        </div>
                        <img class="w-9 h-9 rounded-xl border-2 border-white shadow-sm object-cover" src="https://ui-avatars.com/api/?name=<?= urlencode($user_name) ?>&background=0F172A&color=fff">
                    </div>
                </div>
            </div>
        </header>

        <div class="sticky top-[73px] z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/60 shadow-sm">
            <div class="max-w-[1600px] mx-auto flex items-center h-24">
                <div class="flex-1 overflow-x-auto hide-scrollbar flex items-center px-6 gap-3 scroll-smooth">
                    <a href="#" class="flex flex-col items-center justify-center min-w-[100px] h-[80px] rounded-2xl transition-all group relative bg-blue-50 text-blue-600">
                        <div class="mb-1 p-1.5 rounded-lg bg-blue-600 text-white shadow-lg shadow-blue-500/30">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        </div>
                        <span class="text-[11px] font-bold">Dashboard</span>
                        <div class="absolute bottom-0 left-1/4 right-1/4 h-1 bg-blue-600 rounded-t-full"></div>
                    </a>
                    <?php 
                    $menus = [
                        ['icon'=>'users', 'name'=>'บุคลากร'], ['icon'=>'car', 'name'=>'จองรถ'],
                        ['icon'=>'calendar', 'name'=>'ห้องประชุม'], ['icon'=>'box', 'name'=>'คลังพัสดุ'],
                        ['icon'=>'briefcase', 'name'=>'ทรัพย์สิน'], ['icon'=>'file-text', 'name'=>'งานสารบรรณ'],
                        ['icon'=>'clipboard-list', 'name'=>'แผนงาน'], ['icon'=>'graduation-cap', 'name'=>'อบรม'],
                        ['icon'=>'log-out', 'name'=>'ระบบลา'], ['icon'=>'shopping-cart', 'name'=>'จัดซื้อ'],
                        ['icon'=>'wrench', 'name'=>'ซ่อมบำรุง'], ['icon'=>'monitor', 'name'=>'คอมพิวเตอร์']
                    ];
                    foreach($menus as $m): ?>
                    <a href="#" class="flex flex-col items-center justify-center min-w-[100px] h-[80px] rounded-2xl transition-all group hover:bg-slate-50 text-slate-500 hover:text-blue-600">
                        <div class="mb-1 p-1.5 rounded-lg bg-slate-100 text-slate-400 group-hover:bg-white group-hover:text-blue-600 group-hover:shadow-md transition-all">
                            <i data-lucide="<?= $m['icon'] ?>" class="w-5 h-5"></i>
                        </div>
                        <span class="text-[11px] font-bold"><?= $m['name'] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <div class="flex items-center px-4 h-full border-l border-slate-100 bg-gradient-to-l from-white via-white to-transparent">
                    <button class="flex flex-col items-center justify-center gap-1 w-16 h-16 rounded-xl border-2 border-dashed border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all">
                        <i data-lucide="grid" class="w-5 h-5"></i>
                        <span class="text-[10px] font-bold">ทั้งหมด</span>
                    </button>
                </div>
            </div>
        </div>

        <main class="flex-1 p-6 max-w-[1600px] mx-auto w-full space-y-8 animate-in pb-20">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Overview Dashboard</h2>
                    <div class="flex items-center gap-2 text-slate-500 text-xs font-medium mt-1">
                        <span>ข้อมูลภาพรวมรายบุคคล</span>
                        <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                        <span class="text-blue-600 font-bold">ปีงบประมาณ 2569</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-600 hover:border-blue-400 hover:text-blue-600 transition-all shadow-sm">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i> กรองข้อมูล
                    </button>
                    <button class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition-all">
                        <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i> จัดการหน้าหลัก
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
                
                <div class="xl:col-span-6 relative bg-gradient-to-br from-[#2563eb] to-[#4f46e5] rounded-[32px] p-8 text-white overflow-hidden shadow-xl shadow-blue-200">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-cyan-400/20 rounded-full blur-2xl translate-y-1/2 -translate-x-1/4"></div>
                    
                    <div class="relative flex flex-col sm:flex-row items-center gap-8 h-full">
                        <div class="relative flex-shrink-0">
                            <div class="absolute -top-3 -left-3 bg-gradient-to-br from-yellow-300 to-amber-500 p-2 rounded-xl rotate-[-10deg] shadow-lg border border-white/20">
                                <i data-lucide="trophy" class="w-5 h-5 text-white"></i>
                            </div>
                            <img src="https://picsum.photos/200?random=1" class="w-32 h-32 rounded-[2rem] border-[4px] border-white/20 shadow-2xl object-cover">
                            <div class="absolute -bottom-2 -right-2 bg-emerald-500 p-1.5 rounded-full border-[4px] border-[#3758cf]">
                                <i data-lucide="check" class="w-4 h-4 text-white"></i>
                            </div>
                        </div>
                        
                        <div class="flex-1 text-center sm:text-left">
                            <div class="flex flex-col sm:flex-row items-center gap-3 mb-2">
                                <h3 class="text-3xl font-black tracking-tight text-white"><?= $user_name ?></h3>
                                <span class="bg-amber-400/20 border border-amber-400/30 text-amber-100 text-[10px] font-bold px-2 py-0.5 rounded-md flex items-center gap-1">
                                    <i data-lucide="star" class="w-3 h-3 fill-amber-400 text-amber-400"></i> ดาวเด่นโรงพยาบาล
                                </span>
                            </div>
                            <p class="text-blue-100 text-sm font-medium mb-6">นักวิชาการคอมพิวเตอร์ ชำนาญการ • <span class="font-bold text-white opacity-80">RANK: GOLD</span></p>
                            
                            <div class="flex flex-wrap justify-center sm:justify-start gap-3">
                                <span class="bg-white/10 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-2 backdrop-blur-sm border border-white/10">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> ศูนย์คอมพิวเตอร์
                                </span>
                                <span class="bg-white/10 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-2 backdrop-blur-sm border border-white/10">
                                    <i data-lucide="heart" class="w-3.5 h-3.5 text-pink-300 fill-pink-300"></i> ได้รับคำชมแล้ว: 28 ครั้ง
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-col items-center justify-center bg-white/10 backdrop-blur-md rounded-3xl p-5 border border-white/10 min-w-[160px]">
                            <div class="flex items-center gap-1.5 text-blue-100 text-[10px] font-bold mb-1 uppercase tracking-wider">
                                <i data-lucide="clock" class="w-3 h-3"></i> บันทึกเวลา
                            </div>
                            <div id="current-time-display" class="text-4xl font-black mb-3 tracking-tighter tabular-nums">00:00</div>
                            <button id="btn-clock-in" class="w-full py-2.5 bg-white text-blue-600 rounded-xl text-xs font-black hover:bg-blue-50 transition-all shadow-lg active:scale-95 flex items-center justify-center gap-1.5">
                                Check-in <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-3 bg-white rounded-[32px] p-6 shadow-sm border border-slate-100 flex flex-col justify-between relative overflow-hidden">
                    <i data-lucide="trophy" class="absolute -top-4 -right-4 w-32 h-32 text-slate-50 -rotate-12"></i>
                    
                    <div class="flex items-center justify-between relative z-10 mb-4">
                        <div>
                            <h4 class="font-bold text-slate-800 text-lg">บุคลากรทรงคุณค่า</h4>
                            <p class="text-[10px] text-slate-400 font-medium">อีก 750 คะแนน เพื่อเป็น Platinum</p>
                        </div>
                        <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-500">
                            <i data-lucide="award" class="w-6 h-6"></i>
                        </div>
                    </div>

                    <div class="relative z-10 space-y-4">
                        <div>
                            <div class="flex justify-between text-[10px] font-bold uppercase mb-1">
                                <span class="text-slate-400">ความก้าวหน้า (Gold)</span>
                                <span class="text-amber-500">62%</span>
                            </div>
                            <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-amber-400 to-orange-500 w-[62%] rounded-full"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100">
                                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">สะสมดาว</p>
                                <div class="flex items-center gap-1 text-lg font-black text-slate-800">
                                    <i data-lucide="star" class="w-4 h-4 fill-amber-400 text-amber-400"></i> 45
                                </div>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100">
                                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">แต้มแลกรางวัล</p>
                                <div class="flex items-center gap-1 text-lg font-black text-blue-600">
                                    <i data-lucide="gift" class="w-4 h-4"></i> 1,250
                                </div>
                            </div>
                        </div>
                        
                        <button class="w-full py-3 text-xs font-bold text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors flex items-center justify-center gap-2">
                            ดูสิทธิประโยชน์ <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <div class="xl:col-span-3 bg-white rounded-[32px] p-6 shadow-sm border border-slate-100 flex flex-col">
                    <div class="flex items-center justify-between mb-5">
                        <h4 class="font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="activity" class="w-5 h-5 text-blue-500"></i> สุขภาพล่าสุด
                        </h4>
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">2 ชม. ที่แล้ว</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 flex-1">
                        <div class="bg-emerald-50 rounded-2xl p-3 flex flex-col justify-between hover:scale-105 transition-transform cursor-pointer">
                            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-emerald-500 shadow-sm mb-2"><i data-lucide="heart" class="w-4 h-4"></i></div>
                            <div>
                                <span class="text-[10px] text-slate-500 font-bold block">Heart Rate</span>
                                <span class="text-lg font-black text-slate-800">72 <small class="text-[10px] font-normal text-slate-400">bpm</small></span>
                            </div>
                        </div>
                        <div class="bg-blue-50 rounded-2xl p-3 flex flex-col justify-between hover:scale-105 transition-transform cursor-pointer">
                            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-blue-500 shadow-sm mb-2"><i data-lucide="activity" class="w-4 h-4"></i></div>
                            <div>
                                <span class="text-[10px] text-slate-500 font-bold block">Pressure</span>
                                <span class="text-lg font-black text-slate-800">120/80</span>
                            </div>
                        </div>
                        <div class="bg-cyan-50 rounded-2xl p-3 flex flex-col justify-between hover:scale-105 transition-transform cursor-pointer">
                            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-cyan-500 shadow-sm mb-2"><i data-lucide="scale" class="w-4 h-4"></i></div>
                            <div>
                                <span class="text-[10px] text-slate-500 font-bold block">BMI</span>
                                <span class="text-lg font-black text-slate-800">22.4</span>
                            </div>
                        </div>
                        <div class="bg-orange-50 rounded-2xl p-3 flex flex-col justify-between hover:scale-105 transition-transform cursor-pointer">
                            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-orange-500 shadow-sm mb-2"><i data-lucide="thermometer" class="w-4 h-4"></i></div>
                            <div>
                                <span class="text-[10px] text-slate-500 font-bold block">Temp</span>
                                <span class="text-lg font-black text-slate-800">36.5°</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
                
                <div class="xl:col-span-9 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600"><i data-lucide="target" class="w-6 h-6"></i></div>
                            <div>
                                <h3 class="font-bold text-slate-800">ภารกิจพิเศษ (HR Quests)</h3>
                                <p class="text-xs text-slate-400">สะสมคะแนนเพื่ออัปเกรดระดับและแลกรางวัล</p>
                            </div>
                        </div>
                        <a href="#" class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1">ดูภารกิจทั้งหมด <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white rounded-[24px] p-5 border border-slate-100 shadow-sm hover:shadow-lg transition-all group cursor-pointer relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-20 h-20 bg-orange-50 rounded-bl-[100px] -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                            <div class="w-10 h-10 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center mb-3 relative z-10"><i data-lucide="zap" class="w-5 h-5"></i></div>
                            <h5 class="font-bold text-slate-800 text-sm mb-1 relative z-10">เช็คอินตรงเวลา 5 วันรวด</h5>
                            <p class="text-[10px] text-slate-400 mb-4 relative z-10">รักษาวินัยการทำงานอย่างต่อเนื่อง</p>
                            <div class="flex items-center justify-between text-[10px] font-bold mb-1.5 relative z-10">
                                <span class="text-blue-600 flex items-center gap-1"><i data-lucide="star" class="w-3 h-3 fill-blue-600"></i> +100</span>
                                <span class="text-slate-400">80%</span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden relative z-10">
                                <div class="h-full bg-blue-500 w-[80%] rounded-full"></div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-[24px] p-5 border border-slate-100 shadow-sm hover:shadow-lg transition-all group cursor-pointer relative overflow-hidden">
                            <div class="absolute top-4 right-4 text-emerald-500"><i data-lucide="check-circle-2" class="w-6 h-6 fill-emerald-50"></i></div>
                            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-3"><i data-lucide="book-open" class="w-5 h-5"></i></div>
                            <h5 class="font-bold text-slate-800 text-sm mb-1">อบรม PDPA ประจำปี</h5>
                            <p class="text-[10px] text-slate-400 mb-4">เรียนรู้และทำแบบทดสอบให้ผ่านเกณฑ์</p>
                            <div class="flex items-center justify-between text-[10px] font-bold mb-1.5">
                                <span class="text-blue-600 flex items-center gap-1"><i data-lucide="star" class="w-3 h-3 fill-blue-600"></i> +250</span>
                                <span class="text-emerald-500">สำเร็จ</span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 w-full rounded-full"></div>
                            </div>
                            <button class="mt-3 w-full py-1.5 bg-emerald-50 text-emerald-600 text-[10px] font-bold rounded-lg">รับคะแนนแล้ว</button>
                        </div>
                        
                        <div class="bg-white rounded-[24px] p-5 border border-slate-100 shadow-sm hover:shadow-lg transition-all group cursor-pointer relative overflow-hidden">
                            <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center mb-3"><i data-lucide="award" class="w-5 h-5"></i></div>
                            <h5 class="font-bold text-slate-800 text-sm mb-1">ส่งแบบประเมินผลงาน</h5>
                            <p class="text-[10px] text-slate-400 mb-4">กรอกข้อมูลรายไตรมาสที่ 1/2569</p>
                            <div class="flex items-center justify-between text-[10px] font-bold mb-1.5">
                                <span class="text-blue-600 flex items-center gap-1"><i data-lucide="star" class="w-3 h-3 fill-blue-600"></i> +150</span>
                                <span class="text-slate-400">30%</span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 w-[30%] rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-3 flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-amber-500"><i data-lucide="gift" class="w-5 h-5"></i></div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm">แลกของรางวัล</h3>
                            <p class="text-[10px] text-slate-400">ใช้คะแนนสะสมแลกสิทธิประโยชน์</p>
                        </div>
                    </div>
                    
                    <div class="bg-slate-900 rounded-[24px] p-5 text-white relative overflow-hidden group shadow-xl">
                        <i data-lucide="gift" class="absolute -bottom-6 -right-6 w-32 h-32 text-white/5 group-hover:scale-110 transition-transform"></i>
                        <div class="relative z-10">
                            <div class="flex justify-between items-start mb-4">
                                <span class="bg-amber-400 text-slate-900 text-[10px] font-black px-2 py-0.5 rounded">แนะนำ</span>
                                <div class="flex items-center gap-1 text-amber-400 text-xs font-bold"><i data-lucide="star" class="w-3 h-3 fill-amber-400"></i> 800 pts</div>
                            </div>
                            <h4 class="font-bold text-lg leading-tight mb-1">บัตรกำนัล Starbucks 200.-</h4>
                            <p class="text-[10px] text-slate-400 mb-4">เพิ่มความสดชื่นก่อนเริ่มงาน</p>
                            <button class="w-full py-2 bg-white text-slate-900 rounded-xl text-xs font-bold hover:bg-amber-400 transition-colors">แลกรับของรางวัล</button>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-[24px] p-4 border border-slate-100 flex items-center justify-between hover:bg-slate-50 cursor-pointer transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center text-slate-500"><i data-lucide="calendar" class="w-4 h-4"></i></div>
                            <div>
                                <p class="text-xs font-bold text-slate-800">วันลาพักร้อนพิเศษ 1 วัน</p>
                                <p class="text-[10px] text-slate-400">ใช้ 2,000 คะแนน</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
                    </div>
                </div>
            </div>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-rose-50 rounded-2xl shadow-sm border border-rose-100 flex items-center justify-center text-rose-500">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-handshake"><path d="M19.414 14.414C21 12.828 22 11.5 22 9.5a5.5 5.5 0 0 0-9.591-3.676.6.6 0 0 1-.818.001A5.5 5.5 0 0 0 2 9.5c0 2.3 1.5 4 3 5.5l5.535 5.362a2 2 0 0 0 2.879.052 2.12 2.12 0 0 0-.004-3 2.124 2.124 0 1 0 3-3 2.124 2.124 0 0 0 3.004 0 2 2 0 0 0 0-2.828l-1.881-1.882a2.41 2.41 0 0 0-3.409 0l-1.71 1.71a2 2 0 0 1-2.828 0 2 2 0 0 1 0-2.828l2.823-2.762"></path></svg>
            </div>
            <div>
                <h3 class="font-black text-slate-800 text-lg">กำแพงแห่งคำขอบคุณ (Appreciation Wall)</h3>
                <p class="text-xs text-slate-400 font-medium italic">ส่งพลังบวกให้เพื่อนร่วมงาน (+50 แต้มสะสมต่อคำชม)</p>
            </div>
        </div>
        <button class="flex items-center gap-2 bg-rose-500 hover:bg-rose-600 text-white px-5 py-2.5 rounded-2xl text-xs font-black shadow-lg shadow-rose-500/20 transition-all active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg> ส่งคำขอบคุณ
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-rose-500/5 transition-all group relative">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-3">
                        <img class="w-10 h-10 rounded-full border-2 border-white shadow-sm" src="https://picsum.photos/40/40?random=11" alt="Sender">
                        <div class="w-10 h-10 bg-rose-500 rounded-full border-2 border-white flex items-center justify-center text-white shadow-sm relative z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart fill-white"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
                        </div>
                        <img class="w-10 h-10 rounded-full border-2 border-white shadow-sm" src="https://picsum.photos/40/40?random=50" alt="Recipient">
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold leading-none mb-1"><span class="text-slate-800">พยาบาลสมศรี</span> ชื่นชม <span class="text-rose-500"><?= $user_name ?></span></p>
                        <p class="text-[9px] text-slate-300 font-medium uppercase tracking-wider">2 ชม. ที่แล้ว</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black bg-orange-100 text-orange-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-zap"><path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path></svg> Problem Solver
                </div>
            </div>
            <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100/50 mb-4 italic text-slate-600 text-[13px] leading-relaxed relative">
                <div class="absolute -top-2 -left-2 text-rose-200 opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square fill-rose-50"><path d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"></path></svg>
                </div>
                "ขอบคุณคุณเดชาที่ช่วยกู้คืนข้อมูลไฟล์พัสดุที่เกือบหายไปเมื่อวานนี้ รวดเร็วและเป็นมืออาชีพมากค่ะ!"
            </div>
            <div class="flex items-center justify-between">
                <button class="flex items-center gap-2 text-rose-400 hover:text-rose-600 transition-all group/like">
                    <div class="p-2 rounded-xl group-hover/like:bg-rose-50 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
                    </div>
                    <span class="text-xs font-black">12 คนชื่นชอบ</span>
                </button>
                <div class="flex items-center gap-1 text-amber-500 font-black text-xs bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-100 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star fill-amber-500"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg> +50 Points
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-rose-500/5 transition-all group relative">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-3">
                        <img class="w-10 h-10 rounded-full border-2 border-white shadow-sm" src="https://picsum.photos/40/40?random=12" alt="Sender">
                        <div class="w-10 h-10 bg-rose-500 rounded-full border-2 border-white flex items-center justify-center text-white shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart fill-white"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
                        </div>
                        <img class="w-10 h-10 rounded-full border-2 border-white shadow-sm" src="https://picsum.photos/40/40?random=13" alt="Recipient">
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold leading-none mb-1"><span class="text-slate-800">นพ.วิชัย</span> ชื่นชม <span class="text-rose-500">คุณวิภา</span></p>
                        <p class="text-[9px] text-slate-300 font-medium uppercase tracking-wider">5 ชม. ที่แล้ว</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black bg-blue-100 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-handshake"><path d="M19.414 14.414C21 12.828 22 11.5 22 9.5a5.5 5.5 0 0 0-9.591-3.676.6.6 0 0 1-.818.001A5.5 5.5 0 0 0 2 9.5c0 2.3 1.5 4 3 5.5l5.535 5.362a2 2 0 0 0 2.879.052 2.12 2.12 0 0 0-.004-3 2.124 2.124 0 1 0 3-3 2.124 2.124 0 0 0 3.004 0 2 2 0 0 0 0-2.828l-1.881-1.882a2.41 2.41 0 0 0-3.409 0l-1.71 1.71a2 2 0 0 1-2.828 0 2 2 0 0 1 0-2.828l2.823-2.762"></path></svg> Team Player
                </div>
            </div>
            <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100/50 mb-4 italic text-slate-600 text-[13px] leading-relaxed relative">
                <div class="absolute -top-2 -left-2 text-rose-200 opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square fill-rose-50"><path d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"></path></svg>
                </div>
                "ขอบคุณที่ช่วยประสานงานเคสฉุกเฉินได้อย่างราบรื่นครับ ทีมเวิร์คยอดเยี่ยมมาก"
            </div>
            <div class="flex items-center justify-between">
                <button class="flex items-center gap-2 text-rose-400 hover:text-rose-600 transition-all group/like">
                    <div class="p-2 rounded-xl group-hover/like:bg-rose-50 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart fill-rose-500 text-rose-500"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
                    </div>
                    <span class="text-xs font-black">24 คนชื่นชอบ</span>
                </button>
                <div class="flex items-center gap-1 text-amber-500 font-black text-xs bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-100 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star fill-amber-500"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg> +50 Points
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-indigo-50 to-blue-50 p-6 rounded-[32px] border border-blue-100 flex flex-col items-center justify-center text-center group cursor-pointer hover:shadow-xl transition-all gap-4">
            <div class="w-14 h-14 bg-white rounded-2xl shadow-lg flex items-center justify-center text-blue-500 mb-3 group-hover:rotate-12 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-smile"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" x2="9.01" y1="9" y2="9"></line><line x1="15" x2="15.01" y1="9" y2="9"></line></svg>
            </div>
            <div class="space-y-1">
                <h4 class="font-black text-indigo-900 text-lg">วันนี้คุณขอบคุณใครหรือยัง?</h4>
                <p class="text-xs text-indigo-400 font-medium">คำชื่นชมเล็กๆ น้อยๆ ช่วยสร้างกำลังใจอันยิ่งใหญ่ให้เพื่อนร่วมงานของเราได้นะครับ</p>
            </div>
            <button class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-black shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all w-full md:w-auto">เริ่มส่งคำขอบคุณเลย</button>
        </div>
    </div>
</div>

</div>

<div class="space-y-4"> <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-rose-50 rounded-xl shadow-sm border border-rose-100 flex items-center justify-center text-rose-500"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-handshake"><path d="M19.414 14.414C21 12.828 22 11.5 22 9.5a5.5 5.5 0 0 0-9.591-3.676.6.6 0 0 1-.818.001A5.5 5.5 0 0 0 2 9.5c0 2.3 1.5 4 3 5.5l5.535 5.362a2 2 0 0 0 2.879.052 2.12 2.12 0 0 0-.004-3 2.124 2.124 0 1 0 3-3 2.124 2.124 0 0 0 3.004 0 2 2 0 0 0 0-2.828l-1.881-1.882a2.41 2.41 0 0 0-3.409 0l-1.71 1.71a2 2 0 0 1-2.828 0 2 2 0 0 1 0-2.828l2.823-2.762"></path></svg>
            </div>
            <div>
                <h3 class="font-black text-slate-800 text-base">กำแพงแห่งคำขอบคุณ (Appreciation Wall)</h3> <p class="text-[11px] text-slate-400 font-medium italic">ส่งพลังบวกให้เพื่อนร่วมงาน (+50 แต้มสะสมต่อคำชม)</p>
            </div>
        </div>
        <button class="flex items-center gap-1.5 bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-xl text-[11px] font-black shadow-lg shadow-rose-500/20 transition-all active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg> ส่งคำขอบคุณ
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4"> <div class="bg-white p-5 rounded-[24px] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-rose-500/5 transition-all group relative"> <div class="flex items-start justify-between mb-3"> <div class="flex items-center gap-3">
                    <div class="flex -space-x-2"> <img class="w-8 h-8 rounded-full border-2 border-white shadow-sm" src="https://picsum.photos/40/40?random=11" alt="Sender"> <div class="w-8 h-8 bg-rose-500 rounded-full border-2 border-white flex items-center justify-center text-white shadow-sm relative z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart fill-white"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
                        </div>
                        <img class="w-8 h-8 rounded-full border-2 border-white shadow-sm" src="https://picsum.photos/40/40?random=50" alt="Recipient">
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold leading-none mb-0.5"><span class="text-slate-800">พยาบาลสมศรี</span> ชื่นชม <span class="text-rose-500"><?= $user_name ?></span></p>
                        <p class="text-[9px] text-slate-300 font-medium uppercase tracking-wider">2 ชม. ที่แล้ว</p>
                    </div>
                </div>
                <div class="flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] font-black bg-orange-100 text-orange-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-zap"><path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path></svg> Problem Solver
                </div>
            </div>
            <div class="bg-slate-50/50 p-3 rounded-xl border border-slate-100/50 mb-3 italic text-slate-600 text-[11px] leading-relaxed relative"> <div class="absolute -top-1.5 -left-1.5 text-rose-200 opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square fill-rose-50"><path d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"></path></svg>
                </div>
                "ขอบคุณคุณเดชาที่ช่วยกู้คืนข้อมูลไฟล์พัสดุที่เกือบหายไปเมื่อวานนี้ รวดเร็วและเป็นมืออาชีพมากค่ะ!"
            </div>
            <div class="flex items-center justify-between">
                <button class="flex items-center gap-1.5 text-rose-400 hover:text-rose-600 transition-all group/like">
                    <div class="p-1.5 rounded-lg group-hover/like:bg-rose-50 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
                    </div>
                    <span class="text-[10px] font-black">12 คนชื่นชอบ</span>
                </button>
                <div class="flex items-center gap-1 text-amber-500 font-black text-[10px] bg-amber-50 px-2 py-1 rounded-lg border border-amber-100 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star fill-amber-500"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg> +50 Points
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-[24px] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-rose-500/5 transition-all group relative">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-2">
                        <img class="w-8 h-8 rounded-full border-2 border-white shadow-sm" src="https://picsum.photos/40/40?random=12" alt="Sender">
                        <div class="w-8 h-8 bg-rose-500 rounded-full border-2 border-white flex items-center justify-center text-white shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart fill-white"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
                        </div>
                        <img class="w-8 h-8 rounded-full border-2 border-white shadow-sm" src="https://picsum.photos/40/40?random=13" alt="Recipient">
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold leading-none mb-0.5"><span class="text-slate-800">นพ.วิชัย</span> ชื่นชม <span class="text-rose-500">คุณวิภา</span></p>
                        <p class="text-[9px] text-slate-300 font-medium uppercase tracking-wider">5 ชม. ที่แล้ว</p>
                    </div>
                </div>
                <div class="flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] font-black bg-blue-100 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-handshake"><path d="M19.414 14.414C21 12.828 22 11.5 22 9.5a5.5 5.5 0 0 0-9.591-3.676.6.6 0 0 1-.818.001A5.5 5.5 0 0 0 2 9.5c0 2.3 1.5 4 3 5.5l5.535 5.362a2 2 0 0 0 2.879.052 2.12 2.12 0 0 0-.004-3 2.124 2.124 0 1 0 3-3 2.124 2.124 0 0 0 3.004 0 2 2 0 0 0 0-2.828l-1.881-1.882a2.41 2.41 0 0 0-3.409 0l-1.71 1.71a2 2 0 0 1-2.828 0 2 2 0 0 1 0-2.828l2.823-2.762"></path></svg> Team Player
                </div>
            </div>
            <div class="bg-slate-50/50 p-3 rounded-xl border border-slate-100/50 mb-3 italic text-slate-600 text-[11px] leading-relaxed relative">
                <div class="absolute -top-1.5 -left-1.5 text-rose-200 opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square fill-rose-50"><path d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"></path></svg>
                </div>
                "ขอบคุณที่ช่วยประสานงานเคสฉุกเฉินได้อย่างราบรื่นครับ ทีมเวิร์คยอดเยี่ยมมาก"
            </div>
            <div class="flex items-center justify-between">
                <button class="flex items-center gap-1.5 text-rose-400 hover:text-rose-600 transition-all group/like">
                    <div class="p-1.5 rounded-lg group-hover/like:bg-rose-50 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart fill-rose-500 text-rose-500"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
                    </div>
                    <span class="text-[10px] font-black">24 คนชื่นชอบ</span>
                </button>
                <div class="flex items-center gap-1 text-amber-500 font-black text-[10px] bg-amber-50 px-2 py-1 rounded-lg border border-amber-100 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star fill-amber-500"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg> +50 Points
                </div>
            </div>
        </div>
    </div>
    
    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 p-5 rounded-[24px] border border-blue-100 flex flex-col md:flex-row items-center justify-center gap-4 text-center md:text-left"> <div class="w-12 h-12 bg-white rounded-2xl shadow-lg flex items-center justify-center text-blue-500 group-hover:rotate-12 transition-transform"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-smile"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" x2="9.01" y1="9" y2="9"></line><line x1="15" x2="15.01" y1="9" y2="9"></line></svg>
        </div>
        <div class="flex-1">
            <h4 class="font-black text-indigo-900 text-base mb-0.5">วันนี้คุณขอบคุณใครหรือยัง?</h4> <p class="text-[11px] text-indigo-400 font-medium leading-relaxed">คำชื่นชมเล็กๆ น้อยๆ ช่วยสร้างกำลังใจอันยิ่งใหญ่ให้เพื่อนร่วมงานของเราได้นะครับ</p>
        </div>
        <button class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-[11px] font-black shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all whitespace-nowrap">เริ่มส่งคำขอบคุณเลย</button>
    </div>
</div>

<section class="space-y-4"> <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-inbox"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>
            </div>
            <div>
                <h3 class="font-black text-slate-800 text-base">หนังสือราชการที่รอการจัดการ</h3> <p class="text-[11px] text-slate-400 font-medium">รายการหนังสือรับเข้าจากระบบสารบรรณที่ส่งถึงคุณหรือหน่วยงานของคุณ</p>
            </div>
        </div>
    </div>
    
    <div class="flex overflow-x-auto hide-scrollbar gap-2 pb-2">
        <button class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-[10px] font-bold shadow-lg shadow-blue-500/20 whitespace-nowrap">ทั้งหมด</button>
        <button class="px-3 py-1.5 bg-white border border-slate-200 text-slate-500 rounded-lg text-[10px] font-bold hover:border-blue-300 hover:text-blue-600 whitespace-nowrap transition-all">ด่วนที่สุด</button>
        <button class="px-3 py-1.5 bg-white border border-slate-200 text-slate-500 rounded-lg text-[10px] font-bold hover:border-blue-300 hover:text-blue-600 whitespace-nowrap transition-all">บันทึกข้อความ</button>
        <button class="px-3 py-1.5 bg-white border border-slate-200 text-slate-500 rounded-lg text-[10px] font-bold hover:border-blue-300 hover:text-blue-600 whitespace-nowrap transition-all">หนังสือภายนอก</button>
        <button class="px-3 py-1.5 bg-white border border-slate-200 text-slate-500 rounded-lg text-[10px] font-bold hover:border-blue-300 hover:text-blue-600 whitespace-nowrap transition-all">คำสั่ง</button>
    </div>

    <div class="flex flex-col gap-2"> <div class="group bg-white rounded-2xl p-1 border border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row items-stretch relative overflow-hidden h-auto"> <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-600 rounded-l-2xl"></div> <div class="p-3 flex items-center justify-center md:justify-start"> <div class="w-8 h-8 rounded-lg bg-red-50 text-red-500 flex items-center justify-center group-hover:scale-110 transition-transform"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-exclamation-point text-red-600"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                </div>
            </div>
            <div class="py-3 pr-3 flex-1 flex flex-col justify-center text-center md:text-left gap-1"> <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                    <span class="px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded text-[9px] font-black uppercase">บันทึกข้อความ</span>
                    <span class="text-[9px] text-slate-400 font-bold flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-hash"><line x1="4" x2="20" y1="9" y2="9"></line><line x1="4" x2="20" y1="15" y2="15"></line><line x1="10" x2="8" y1="3" y2="21"></line><line x1="16" x2="14" y1="3" y2="21"></line></svg> ลย 0033.012/ว 79</span>
                </div>
                <h4 class="font-bold text-slate-800 text-xs line-clamp-1 group-hover:text-blue-600 transition-colors">ขอเชิญประชุมคณะกรรมการบริหารจัดการระบบคอมพิวเตอร์และเครือข่าย ครั้งที่ 1/2569</h4>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 text-[9px] text-slate-400 font-medium">
                    <span class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building2 text-slate-300"><path d="M10 12h4"></path><path d="M10 8h4"></path><path d="M14 21v-3a2 2 0 0 0-4 0v3"></path><path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path><path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path></svg> จาก: ฝ่ายบริหารงานทั่วไป</span>
                    <span class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send text-slate-300"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path><path d="m21.854 2.147-10.94 10.939"></path></svg> ถึง: ทุกหน่วยงาน</span>
                </div>
            </div>
            <div class="py-3 px-4 border-t md:border-t-0 md:border-l border-slate-50 flex items-center justify-between md:justify-end gap-3 min-w-[160px]">
                <div class="text-right">
                    <span class="bg-red-500 text-white px-1.5 py-0.5 rounded-full text-[8px] font-black uppercase">ด่วนที่สุด</span>
                    <div class="flex items-center justify-end gap-1 text-[9px] text-slate-400 mt-0.5 font-bold"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock"><path d="M12 6v6l4 2"></path><circle cx="12" cy="12" r="10"></circle></svg> 15 นาทีที่แล้ว</div>
                </div>
                <button class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-[10px] font-bold hover:bg-blue-700 shadow-md transition-all flex items-center gap-1">
                    เปิดอ่าน <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"></path></svg>
                </button>
            </div>
        </div>

        <div class="group bg-white rounded-2xl p-1 border border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row items-stretch relative overflow-hidden h-auto">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-orange-500 rounded-l-2xl"></div>
            <div class="p-3 flex items-center justify-center md:justify-start">
                <div class="w-8 h-8 rounded-lg bg-orange-50 text-orange-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text text-orange-500"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path><path d="M14 2v5a1 1 0 0 0 1 1h5"></path><path d="M10 9H8"></path><path d="M16 13H8"></path><path d="M16 17H8"></path></svg>
                </div>
            </div>
            <div class="py-3 pr-3 flex-1 flex flex-col justify-center text-center md:text-left gap-1">
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                    <span class="px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded text-[9px] font-black uppercase">หนังสือภายนอก</span>
                    <span class="text-[9px] text-slate-400 font-bold flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-hash"><line x1="4" x2="20" y1="9" y2="9"></line><line x1="4" x2="20" y1="15" y2="15"></line><line x1="10" x2="8" y1="3" y2="21"></line><line x1="16" x2="14" y1="3" y2="21"></line></svg> สธ 0202.3/1244</span>
                </div>
                <h4 class="font-bold text-slate-800 text-xs line-clamp-1 group-hover:text-blue-600 transition-colors">แจ้งแนวทางปฏิบัติการเบิกจ่ายงบประมาณกองทุนสุขภาพประจำปีงบประมาณ 2569</h4>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 text-[9px] text-slate-400 font-medium">
                    <span class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building2 text-slate-300"><path d="M10 12h4"></path><path d="M10 8h4"></path><path d="M14 21v-3a2 2 0 0 0-4 0v3"></path><path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path><path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path></svg> จาก: สำนักงานสาธารณสุขจังหวัด</span>
                    <span class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send text-slate-300"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path><path d="m21.854 2.147-10.94 10.939"></path></svg> ถึง: กลุ่มงานแผนงานและประเมินผล</span>
                </div>
            </div>
            <div class="py-3 px-4 border-t md:border-t-0 md:border-l border-slate-50 flex items-center justify-between md:justify-end gap-3 min-w-[160px]">
                <div class="text-right">
                    <span class="bg-orange-500 text-white px-1.5 py-0.5 rounded-full text-[8px] font-black uppercase">ด่วนมาก</span>
                    <div class="flex items-center justify-end gap-1 text-[9px] text-slate-400 mt-0.5 font-bold"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock"><path d="M12 6v6l4 2"></path><circle cx="12" cy="12" r="10"></circle></svg> 1 ชม. ที่แล้ว</div>
                </div>
                <button class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-[10px] font-bold hover:bg-blue-700 shadow-md transition-all flex items-center gap-1">
                    เปิดอ่าน <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"></path></svg>
                </button>
            </div>
        </div>

        <div class="group bg-white rounded-2xl p-1 border border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row items-stretch relative overflow-hidden h-auto">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500 rounded-l-2xl"></div>
            <div class="p-3 flex items-center justify-center md:justify-start">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building2 text-emerald-500"><path d="M10 12h4"></path><path d="M10 8h4"></path><path d="M14 21v-3a2 2 0 0 0-4 0v3"></path><path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path><path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path></svg>
                </div>
            </div>
            <div class="py-3 pr-3 flex-1 flex flex-col justify-center text-center md:text-left gap-1">
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                    <span class="px-1.5 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase">คำสั่ง</span>
                    <span class="text-[9px] text-slate-400 font-bold flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-hash"><line x1="4" x2="20" y1="9" y2="9"></line><line x1="4" x2="20" y1="15" y2="15"></line><line x1="10" x2="8" y1="3" y2="21"></line><line x1="16" x2="14" y1="3" y2="21"></line></svg> รพ.ลย. 112/2569</span>
                </div>
                <h4 class="font-bold text-slate-800 text-xs line-clamp-1 group-hover:text-blue-600 transition-colors">แต่งตั้งคณะทำงานพิจารณาจัดซื้อจัดจ้างระบบบริหารจัดการข้อมูลสุขภาพ (HIS)</h4>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 text-[9px] text-slate-400 font-medium">
                    <span class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building2 text-slate-300"><path d="M10 12h4"></path><path d="M10 8h4"></path><path d="M14 21v-3a2 2 0 0 0-4 0v3"></path><path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path><path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path></svg> จาก: กลุ่มภารกิจด้านอำนวยการ</span>
                    <span class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send text-slate-300"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path><path d="m21.854 2.147-10.94 10.939"></path></svg> ถึง: คณะกรรมการตามรายชื่อแนบท้าย</span>
                </div>
            </div>
            <div class="py-3 px-4 border-t md:border-t-0 md:border-l border-slate-50 flex items-center justify-between md:justify-end gap-3 min-w-[160px]">
                <div class="text-right">
                    <span class="bg-blue-600 text-white px-1.5 py-0.5 rounded-full text-[8px] font-black uppercase">ด่วน</span>
                    <div class="flex items-center justify-end gap-1 text-[9px] text-slate-400 mt-0.5 font-bold"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock"><path d="M12 6v6l4 2"></path><circle cx="12" cy="12" r="10"></circle></svg> เมื่อวานนี้</div>
                </div>
                <button class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-[10px] font-bold hover:bg-blue-700 shadow-md transition-all flex items-center gap-1">
                    เปิดอ่าน <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"></path></svg>
                </button>
            </div>
        </div>
    </div>
    
    <div class="flex flex-col sm:flex-row items-center justify-between bg-slate-50/50 p-4 rounded-2xl border border-slate-100 gap-4"> <div class="flex items-center gap-3">
            <div class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-ping"></div>
            <p class="text-[11px] font-bold text-slate-500">พบหนังสือใหม่ <span class="text-blue-600">2 รายการ</span> ที่ยังไม่ได้ดำเนินการ</p>
        </div>
        <button class="flex items-center gap-2 text-blue-600 hover:text-blue-700 font-black text-[10px] transition-all group">
            เข้าสู่ระบบงานสารบรรณเต็มรูปแบบ
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right group-hover:translate-x-1 transition-transform"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
        </button>
    </div>
</section>

        </main>

        <footer class="p-8 text-center border-t border-slate-100 bg-white">
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.3em]">Hospital ERP NextGen</p>
            <p class="text-slate-300 text-[9px] mt-1 italic">Designed for Performance & Aesthetics</p>
        </footer>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    // 1. Render Icons
    lucide.createIcons();

    // 2. Real-time Clock
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
        const display = document.getElementById('current-time-display');
        if(display) display.innerText = timeString;
    }
    setInterval(updateTime, 1000);
    updateTime();

    // 3. Logic Button Clock In
    const btnClockIn = document.getElementById('btn-clock-in');
    if(btnClockIn){
        btnClockIn.addEventListener('click', function() {
            if(confirm('ยืนยันการลงเวลาเข้างาน?')) {
                this.innerHTML = 'กำลังบันทึก...';
                this.classList.add('opacity-50', 'cursor-not-allowed');
                this.disabled = true;

                // *** ใส่ AJAX ตรงนี้ ***
                // fetch('index.php?r=site/clock-in', { method: 'POST' })...

                setTimeout(() => {
                    this.innerHTML = 'บันทึกแล้ว <i data-lucide="check" class="inline w-3.5 h-3.5 ml-1"></i>';
                    this.classList.remove('bg-white', 'text-blue-600', 'opacity-50', 'cursor-not-allowed');
                    this.classList.add('bg-emerald-500', 'text-white', 'border-transparent');
                    lucide.createIcons(); // Re-render icon
                    alert('ลงเวลาสำเร็จ! (Mockup)');
                }, 1000);
            }
        });
    }
</script>
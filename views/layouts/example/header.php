
<?php
use app\components\UserHelper;
use yii\helpers\Html;
use yii\helpers\Url;
?>
    <!-- Header -->
    <div class="header">
        
        <!-- Logo -->
        <div class="header-left">
             <a href="<?=Url::to('/')?>" class="logo">
                <img src="<?=$assets->baseUrl?>/img/logo.png" width="40" height="40" alt="">
            </a>
            <a href="<?=Url::to('/')?>" class="logo2">
                <img src="<?=$assets->baseUrl?>/img/logo2.png" width="40" height="40" alt="">
            </a>
        </div>
        <!-- /Logo -->
        
        <a id="toggle_btn" href="javascript:void(0);">
            <span class="bar-icon">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </a>
        
        <!-- Header Title -->
        <div class="page-title-box">
            <h3>HOSPITAL-ERP</h3>
        </div>
        <!-- /Header Title -->
        
        <a id="mobile_btn" class="mobile_btn" href="#sidebar"><i class="fa fa-bars"></i></a>
        
        <!-- Header Menu -->
        <ul class="nav user-menu">
        
            <!-- Search -->
            <li class="nav-item">
                <div class="top-nav-search">
                    <a href="javascript:void(0);" class="responsive-search">
                        <i class="fa fa-search"></i>
                   </a>
                    <form action="search.html">
                        <input class="form-control" type="text" placeholder="Search here">
                        <button class="btn" type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>
            </li>
            <!-- /Search -->
        
            <!-- Flag -->
            <li class="nav-item dropdown has-arrow flag-nav">
                <?php if(Yii::$app->request->cookies['language']=='th-TH'):?>
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="<?=Url::current(['language' => 'th-TH'])?>" role="button">
                    <img src="<?=$assets->baseUrl?>/img/flags/th.png" alt="" height="20"> <span>Thailand</span>
                </a>
                    <?php else:?>
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="<?=Url::current(['language' => 'en-EN'])?>" role="button">
                    <img src="<?=$assets->baseUrl?>/img/flags/us.png" alt="" height="20"> <span>English</span>
                </a>
                        <?php endif;?>
         
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="<?=Url::current(['language' => 'th-TH'])?>" class="dropdown-item">
                        <img src="<?=$assets->baseUrl?>/img/flags/th.png" alt="" height="16"> Thailand
                    </a>
                    <a href="<?=Url::current(['language' => 'en-EN'])?>" class="dropdown-item">
                        <img src="<?=$assets->baseUrl?>/img/flags/us.png" alt="" height="16"> English
                    </a>
                </div>
            </li>
            <!-- /Flag -->
        
            <!-- Notifications -->
            <li class="nav-item dropdown">
                <a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
                    <i class="fa fa-bell-o"></i> <span class="badge rounded-pill">3</span>
                </a>
                <div class="dropdown-menu notifications">
                    <div class="topnav-dropdown-header">
                        <span class="notification-title">Notifications (Webapp And line OA)</span>
                        <a href="javascript:void(0)" class="clear-noti"> Clear All </a>
                    </div>
                    <div class="noti-content">
                        <ul class="notification-list">
                            <li class="notification-message">
                                <a href="activities.html">
                                    <div class="media d-flex">
                                        <span class="avatar flex-shrink-0">
                                            <img src="<?=$assets->baseUrl?>/img/profiles/avatar-02.jpg">
                                        </span>
                                        <div class="media-body flex-grow-1">
                                            <p class="noti-details"><span class="noti-title">John Doe</span> เพิ่มงานใหม่ <span class="noti-title">จองห้องประชุม ชมตะวัน</span></p>
                                            <p class="noti-time"><span class="notification-time">1 ชั่วโมงที่แล้ว</span></p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li class="notification-message">
                                <a href="activities.html">
                                    <div class="media d-flex">
                                        <span class="avatar flex-shrink-0">
                                            <img src="<?=$assets->baseUrl?>/img/profiles/avatar-03.jpg">
                                        </span>
                                        <div class="media-body flex-grow-1">
                                            <p class="noti-details"><span class="noti-title">Tarah Shropshire</span>  <span class="noti-title">จองรถไปราชการ วันที่ 8/08/2566</span></p>
                                            <p class="noti-time"><span class="notification-time">เมื่อวาน</span></p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li class="notification-message">
                                <a href="activities.html">
                                    <div class="media d-flex">
                                        <span class="avatar flex-shrink-0">
                                            <img src="<?=$assets->baseUrl?>/img/profiles/avatar-06.jpg">
                                        </span>
                                        <div class="media-body flex-grow-1">
                                            <p class="noti-details"><span class="noti-title">Misty Tison</span>  <span class="noti-title">ขอลาพักผ่อน </span>  <span class="noti-title">วันที่ 11/08/2566</span></p>
                                            <p class="noti-time"><span class="notification-time">8 นาทีที่แล้ว</span></p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                    <div class="topnav-dropdown-footer">
                        <a href="activities.html">View all Notifications</a>
                    </div>
                </div>
            </li>
            <!-- /Notifications -->
            
            <!-- Message Notifications -->
            <li class="nav-item dropdown">
                <a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
                    <i class="fa fa-comment-o"></i> <span class="badge rounded-pill">8</span>
                </a>
                <div class="dropdown-menu notifications">
                    <div class="topnav-dropdown-header">
                        <span class="notification-title">Messages</span>
                        <a href="javascript:void(0)" class="clear-noti"> Clear All </a>
                    </div>
                    <div class="noti-content">
                        <ul class="notification-list">
                            <li class="notification-message">
                                <a href="chat.html">
                                    <div class="list-item">
                                        <div class="list-left">
                                            <span class="avatar">
                                                <img src="<?=$assets->baseUrl?>/img/profiles/avatar-09.jpg">
                                            </span>
                                        </div>
                                        <div class="list-body">
                                            <span class="message-author">Richard Miles </span>
                                            <span class="message-time">12:28 AM</span>
                                            <div class="clearfix"></div>
                                            <span class="message-content">Lorem ipsum dolor sit amet, consectetur adipiscing</span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li class="notification-message">
                                <a href="chat.html">
                                    <div class="list-item">
                                        <div class="list-left">
                                            <span class="avatar">
                                                <img src="<?=$assets->baseUrl?>/img/profiles/avatar-02.jpg">
                                            </span>
                                        </div>
                                        <div class="list-body">
                                            <span class="message-author">John Doe</span>
                                            <span class="message-time">6 Mar</span>
                                            <div class="clearfix"></div>
                                            <span class="message-content">Lorem ipsum dolor sit amet, consectetur adipiscing</span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li class="notification-message">
                                <a href="chat.html">
                                    <div class="list-item">
                                        <div class="list-left">
                                            <span class="avatar">
                                                <img src="<?=$assets->baseUrl?>/img/profiles/avatar-03.jpg">
                                            </span>
                                        </div>
                                        <div class="list-body">
                                            <span class="message-author"> Tarah Shropshire </span>
                                            <span class="message-time">5 Mar</span>
                                            <div class="clearfix"></div>
                                            <span class="message-content">Lorem ipsum dolor sit amet, consectetur adipiscing</span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li class="notification-message">
                                <a href="chat.html">
                                    <div class="list-item">
                                        <div class="list-left">
                                            <span class="avatar">
                                                <img src="<?=$assets->baseUrl?>/img/profiles/avatar-05.jpg">
                                            </span>
                                        </div>
                                        <div class="list-body">
                                            <span class="message-author">Mike Litorus</span>
                                            <span class="message-time">3 Mar</span>
                                            <div class="clearfix"></div>
                                            <span class="message-content">Lorem ipsum dolor sit amet, consectetur adipiscing</span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li class="notification-message">
                                <a href="chat.html">
                                    <div class="list-item">
                                        <div class="list-left">
                                            <span class="avatar">
                                                <img src="<?=$assets->baseUrl?>/img/profiles/avatar-08.jpg">
                                            </span>
                                        </div>
                                        <div class="list-body">
                                            <span class="message-author"> Catherine Manseau </span>
                                            <span class="message-time">27 Feb</span>
                                            <div class="clearfix"></div>
                                            <span class="message-content">Lorem ipsum dolor sit amet, consectetur adipiscing</span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="topnav-dropdown-footer">
                        <a href="chat.html">View all Messages</a>
                    </div>
                </div>
            </li>
            <!-- /Message Notifications -->

            <li class="nav-item dropdown has-arrow main-drop">
                <a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
                    <span class="user-img">
                    <?=UserHelper::UserImg()?>
                    <span class="status online"></span></span>
                    <span><?=UserHelper::Fullname()?></span>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="<?=Url::to('profile')?>">โปรไฟล์</a>
                    <a class="dropdown-item" href="settings.html">ตั้งค่า</a>
                    <?php if(!Yii::$app->user->isGuest):?>
                    <?php
                     echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
                     . Html::submitButton(
                         'ออกจากระบบ (' . Yii::$app->user->identity->username . ')',
                         ['class' => 'dropdown-item']
                     )
                     . Html::endForm();
                    ?>
                    <?php endif; ?>
                </div>
            </li>
        </ul>
        <!-- /Header Menu -->
        
        <!-- Mobile Menu -->
        <div class="dropdown mobile-user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="profile.html">My Profile</a>
                <a class="dropdown-item" href="settings.html">Settings</a>
                <a class="dropdown-item" href="index.html">Logout</a>
            </div>
        </div>
        <!-- /Mobile Menu -->
        
    </div>
    <!-- /Header -->
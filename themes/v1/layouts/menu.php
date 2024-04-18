<?php
use yii\bootstrap5\Html;
use yii\helpers\Url;
?>
								<li class="menu-title"> 
								<span>เมนูหลัก</span>
							</li>
							<li class="submenu">
								<a href="#"><i class="la la-dashboard"></i> <span> Dashboard</span> <span class="menu-arrow"></span></a>
								<ul style="display: none;">
									<li><a href="admin-dashboard.html">Admin Dashboard</a></li>
									<li><a href="employee-dashboard.html">Employee Dashboard</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="#"><i class="la la-cube"></i> <span> Apps</span> <span class="menu-arrow"></span></a>
								<ul style="display: none;">
									<li><a href="chat.html">งานสารบรรณ</a></li>
									<li><a href="events.html">งานยานพาหนะ</a></li>
									<li><a href="contacts.html">งานห้องประชุม</a></li>
									<li><a href="sm">งานพัสดุ</a></li>
									<li><a href="file-manager.html">งานซ่อมบำรุง</a></li>
									<li><a href="file-manager.html">งานซักฟอก</a></li>
									<li><a href="file-manager.html">งานจ่ายกลาง</a></li>
									<li><a href="file-manager.html">งานโภชนาศาสตร์</a></li>
									<li><a href="file-manager.html">Risk Management</a></li>
									<li><a href="file-manager.html">Edonation</a></li>
								</ul>
							</li>
							<li class="menu-title"> 
								<span><?=Yii::t('app','เกี่ยวกับบุคลากร')?></span>
							</li>
							<li class="submenu">
								<!-- <a href="#" class="noti-dot noti-dot subdrop"><i class="la la-user"></i> <span> Employees</span> <span class="menu-arrow"></span></a> -->
								<a href="#" class="noti-dot"><i class="la la-user"></i> <span> บุคลากร</span> <span class="menu-arrow"></span></a>
								<ul style="display:none;">
								    <li><?=Html::a('ทะเบียนบุคลากร',['/employees'])?></li>
									<li><a href="/erp/themes/v1/html/holidays.html">ปฎิทินวันหยุด</a></li>
									<li><a href="leaves.html">วันลา (Admin) <span class="badge rounded-pill bg-primary float-end">1</span></a></li>
									<li><a href="leaves-employee.html">วันลา (Employee)</a></li>
									<li><a href="leave-settings.html">ตั้งค่าการลา</a></li>
									<li><a href="attendance.html">ชั้วโมงทำงาน (Admin)</a></li>
									<li><a href="attendance-employee.html">ชั้วโมงทำงาน (Employee)</a></li>
									<li><a href="<?=Url::to('shiftScheduling')?>">การจัดตารางการทำงาน</a></li>
									<!-- <li><a href="overtime.html">Overtime</a></li> -->
								</ul>
							</li>
							<li> 
								<a href="clients.html"><i class="la la-users"></i> <span>สมาชิก( 8 คน)</span></a>
							</li>
							<li> 
								<a href="leads.html"><i class="la la-user-secret"></i> <span>ผู้นำ(หัวหน้าหน่วย)</span></a>
							</li>
							<li class="menu-title"> 
								<span>บริหารงานบุคคล</span>
							</li>
							
							<li class="submenu">
								<a href="#"><i class="la la-files-o"></i> <span> Accounting </span> <span class="menu-arrow"></span></a>
								<ul style="display: none;">
									<li><a href="categories.html">Categories</a></li>
									<li><a href="budgets.html">Budgets</a></li>
									<li><a href="budget-expenses.html">Budget Expenses</a></li>
									<li><a href="budget-revenues.html">Budget Revenues</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="#"><i class="la la-money"></i> <span> การจ่ายเงิน </span> <span class="menu-arrow"></span></a>
								<ul style="display: none;">
									<li><a href="salary.html"> เงินเดือนพนักงาน </a></li>
									<!-- <li><a href="salary-view.html"> Payslip </a></li>
									<li><a href="payroll-items.html"> Payroll Items </a></li> -->
								</ul>
							</li>

							<li class="menu-title"> 
								<span><?=Yii::t('app','แผนงานและโครงการ')?></span>
							</li>
							<li class="submenu">
								<!-- <a href="#" class=""><i class="la la-rocket"></i> <span> แผนงานและโครงการ</span> <span class="menu-arrow"></span></a> -->
								
									<li><a href="projects.html"><i class="la la-rocket"></i> <span>โครงการ</span></a></li>
									<li><a href="projects.html"><i class="la la-rocket"></i> <span>Tasks</span></a></li>
									<li><a href="projects.html"><i class="la la-rocket"></i> <span>Task Board</span></a></li>
						
							</li>
							<li class="menu-title"> 
								<span>อื่นๆ</span>
							</li>
							
							<li class="submenu">
							<li> 
								<a href="policies.html"><i class="la la-file-pdf-o"></i> <span>Policies</span></a>
							</li>
							
							<li class="submenu">
								<a href="#"><i class="la la-pie-chart"></i> <span> รายงาน </span> <span class="menu-arrow"></span></a>
								<ul style="display: none;">
									<li><a href="expense-reports.html"> Expense Report </a></li>
									<li><a href="invoice-reports.html"> Invoice Report </a></li>
									<li><a href="payments-reports.html"> Payments Report </a></li>
									<li><a href="project-reports.html"> Project Report </a></li>
									<li><a href="task-reports.html"> Task Report </a></li>
									<li><a href="user-reports.html"> User Report </a></li>
									<li><a href="employee-reports.html"> Employee Report </a></li>
									<li><a href="payslip-reports.html"> Payslip Report </a></li>
									<li><a href="attendance-reports.html"> Attendance Report </a></li>
									<li><a href="leave-reports.html"> Leave Report </a></li>
									<li><a href="daily-reports.html"> Daily Report </a></li>
								</ul>
							</li>
							<li class="menu-title"> 
									<span>สำหรับผู้ดูแลระบบ</span>
								</li>
								<li> 
								<?=html::a('<i class="la la-cog"></i> <span>ตั้งค่าระบบ</span>',['/settings']);?>
								</li>
							
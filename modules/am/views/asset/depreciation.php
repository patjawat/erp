<h5 class="card-title">ข้อมูลค่าเสื่อมราคา</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">ข้อมูลพื้นฐาน</h6>
                                <div class="row mb-2">
                                    <div class="col-md-6 fw-bold">ราคาทุน:</div>
                                    <div class="col-md-6"><?=number_format($model->price)?> บาท</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6 fw-bold">อายุการใช้งาน:</div>
                                    <div class="col-md-6"><?= isset($model->data_json['service_life']) ? $model->data_json['service_life'] : '' ?> ปี</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6 fw-bold">วิธีคำนวณค่าเสื่อม:</div>
                                    <div class="col-md-6">วิธีเส้นตรง</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6 fw-bold">ค่าเสื่อมต่อปี:</div>
                                    <div class="col-md-6">7,000.00 บาท</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6 fw-bold">มูลค่าปัจจุบัน:</div>
                                    <div class="col-md-6">21,000.00 บาท</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">กราฟแสดงค่าเสื่อมราคา</h6>
                                <div style="height: 200px; position: relative;">
                                    <svg viewBox="0 0 500 200" width="100%" height="100%" style="overflow: visible">
                                        <!-- X and Y Axes -->
                                        <line x1="50" y1="170" x2="450" y2="170" stroke="#dee2e6" stroke-width="2">
                                        </line>
                                        <line x1="50" y1="20" x2="50" y2="170" stroke="#dee2e6" stroke-width="2"></line>

                                        <!-- Y-axis labels -->
                                        <text x="45" y="170" text-anchor="end" font-size="12">0</text>
                                        <text x="45" y="130" text-anchor="end" font-size="12">10,000</text>
                                        <text x="45" y="90" text-anchor="end" font-size="12">20,000</text>
                                        <text x="45" y="50" text-anchor="end" font-size="12">30,000</text>
                                        <text x="45" y="20" text-anchor="end" font-size="12">40,000</text>

                                        <!-- X-axis labels -->
                                        <text x="50" y="185" text-anchor="middle" font-size="12">2565</text>
                                        <text x="130" y="185" text-anchor="middle" font-size="12">2566</text>
                                        <text x="210" y="185" text-anchor="middle" font-size="12">2567</text>
                                        <text x="290" y="185" text-anchor="middle" font-size="12">2568</text>
                                        <text x="370" y="185" text-anchor="middle" font-size="12">2569</text>
                                        <text x="450" y="185" text-anchor="middle" font-size="12">2570</text>

                                        <!-- Grid lines -->
                                        <line x1="50" y1="130" x2="450" y2="130" stroke="#f5f5f5" stroke-width="1">
                                        </line>
                                        <line x1="50" y1="90" x2="450" y2="90" stroke="#f5f5f5" stroke-width="1"></line>
                                        <line x1="50" y1="50" x2="450" y2="50" stroke="#f5f5f5" stroke-width="1"></line>

                                        <!-- Value line -->
                                        <polyline points="50,50 130,90 210,130 290,170 370,170 450,170" fill="none"
                                            stroke="#0d6efd" stroke-width="3"></polyline>

                                        <!-- Data points -->
                                        <circle cx="50" cy="50" r="5" fill="#0d6efd"></circle>
                                        <circle cx="130" cy="90" r="5" fill="#0d6efd"></circle>
                                        <circle cx="210" cy="130" r="5" fill="#0d6efd"></circle>
                                        <circle cx="290" cy="170" r="5" fill="#0d6efd"></circle>
                                        <circle cx="370" cy="170" r="5" fill="#0d6efd"></circle>
                                        <circle cx="450" cy="170" r="5" fill="#0d6efd"></circle>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>ปีงบประมาณ</th>
                                <th>มูลค่าต้นปี</th>
                                <th>ค่าเสื่อมราคาประจำปี</th>
                                <th>ค่าเสื่อมราคาสะสม</th>
                                <th>มูลค่าปลายปี</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2565</td>
                                <td>35,000.00</td>
                                <td>7,000.00</td>
                                <td>7,000.00</td>
                                <td>28,000.00</td>
                            </tr>
                            <tr>
                                <td>2566</td>
                                <td>28,000.00</td>
                                <td>7,000.00</td>
                                <td>14,000.00</td>
                                <td>21,000.00</td>
                            </tr>
                            <tr>
                                <td>2567</td>
                                <td>21,000.00</td>
                                <td>7,000.00</td>
                                <td>21,000.00</td>
                                <td>14,000.00</td>
                            </tr>
                            <tr>
                                <td>2568</td>
                                <td>14,000.00</td>
                                <td>7,000.00</td>
                                <td>28,000.00</td>
                                <td>7,000.00</td>
                            </tr>
                            <tr>
                                <td>2569</td>
                                <td>7,000.00</td>
                                <td>7,000.00</td>
                                <td>35,000.00</td>
                                <td>0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
<style>
         background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
        }
        .search-bar {
            border-radius: 24px;
            padding-left: 45px;
            height: 48px;
            font-size: 16px;
            border: 1px solid #dfe1e5;
            box-shadow: none;
            transition: all 0.3s;
        }
        .search-bar:focus {
            box-shadow: 0 1px 6px rgba(32, 33, 36, 0.28);
            border-color: rgba(223, 225, 229, 0);
        }
        .search-icon {
            position: absolute;
            left: 30px;
            top: 16px;
            color: #5f6368;
        }
        .filter-btn {
            border-radius: 24px;
            border: 1px solid #dfe1e5;
            background-color: #f8f9fa;
            color: #3c4043;
            font-size: 14px;
            margin-right: 8px;
            transition: all 0.2s;
        }
        .filter-btn:hover {
            background-color: #f1f3f4;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .filter-btn.active {
            background-color: #e8f0fe;
            color: #1a73e8;
            border-color: #d2e3fc;
        }
        .result-card {
            border-radius: 8px;
            transition: all 0.2s;
            cursor: pointer;
            border: 1px solid #e0e0e0;
        }
        .result-card:hover {
            box-shadow: 0 1px 6px rgba(32, 33, 36, 0.28);
        }
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #4285f4;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .action-icon {
            color: #5f6368;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.2s;
        }
        .action-icon:hover {
            background-color: #f1f3f4;
            color: #202124;
        }
        .view-options {
            border-radius: 24px;
            overflow: hidden;
        }
        .view-option {
            border: none;
            background-color: transparent;
            color: #5f6368;
            padding: 6px 12px;
        }
        .view-option.active {
            background-color: #e8f0fe;
            color: #1a73e8;
        }
        .advanced-search {
            display: none;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            border: 1px solid #dfe1e5;
        }
        .department-tag {
            background-color: #e8f0fe;
            color: #1a73e8;
            border-radius: 16px;
            font-size: 12px;
            padding: 4px 12px;
        }
        .status-active {
            color: #34a853;
        }
        .status-inactive {
            color: #ea4335;
        }
</style>
<div class="search-container">
            <div class="row mb-3">
                <div class="col-12 position-relative">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control search-bar" id="searchInput" placeholder="Search personnel by name, ID, position, department...">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-8">
                    <button class="btn filter-btn" data-filter="all">
                        <i class="fas fa-users me-1"></i> All
                    </button>
                    <button class="btn filter-btn" data-filter="department">
                        <i class="fas fa-building me-1"></i> Department
                    </button>
                    <button class="btn filter-btn" data-filter="position">
                        <i class="fas fa-briefcase me-1"></i> Position
                    </button>
                    <button class="btn filter-btn" data-filter="status">
                        <i class="fas fa-toggle-on me-1"></i> Status
                    </button>
                    <button class="btn filter-btn active" id="advancedSearchBtn">
                        <i class="fas fa-sliders-h me-1"></i> Advanced
                    </button>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group view-options">
                        <button class="view-option active" id="gridView">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button class="view-option" id="listView">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="advanced-search" id="advancedSearchPanel" style="display: none;">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Department</label>
                        <select class="form-select">
                            <option value="">All Departments</option>
                            <option value="hr">Human Resources</option>
                            <option value="it">Information Technology</option>
                            <option value="finance">Finance</option>
                            <option value="marketing">Marketing</option>
                            <option value="operations">Operations</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Position</label>
                        <select class="form-select">
                            <option value="">All Positions</option>
                            <option value="manager">Manager</option>
                            <option value="director">Director</option>
                            <option value="specialist">Specialist</option>
                            <option value="assistant">Assistant</option>
                            <option value="intern">Intern</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="onleave">On Leave</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hire Date From</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hire Date To</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Skills</label>
                        <input type="text" class="form-control" placeholder="e.g. Excel, Project Management">
                    </div>
                    <div class="col-12 text-end mt-3">
                        <button class="btn btn-secondary me-2">Clear</button>
                        <button class="btn btn-primary">Apply Filters</button>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <p class="text-muted"><span id="resultCount">6</span> results found</p>
                </div>
            </div>

            <div class="row" id="resultsGrid">
                <!-- Personnel Card 1 -->
                <div class="col-md-4 mb-3">
                    <div class="card result-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="profile-img me-3">JS</div>
                                    <div>
                                        <h5 class="mb-0">John Smith</h5>
                                        <small class="text-muted">ID: EMP001</small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <i class="fas fa-ellipsis-v action-icon" data-bs-toggle="dropdown" aria-expanded="false"></i>
                                    <ul class="dropdown-menu" style="">
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2"></i> View Details</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i> Print Info</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            <p class="mb-1"><i class="fas fa-briefcase me-2 text-secondary"></i> Senior Developer</p>
                            <p class="mb-1"><i class="fas fa-building me-2 text-secondary"></i> Information Technology</p>
                            <p class="mb-3"><i class="fas fa-calendar me-2 text-secondary"></i> Joined: Jan 15, 2020</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="department-tag">IT Department</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personnel Card 2 -->
                <div class="col-md-4 mb-3">
                    <div class="card result-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="profile-img me-3" style="background-color: #ea4335;">SW</div>
                                    <div>
                                        <h5 class="mb-0">Sarah Wilson</h5>
                                        <small class="text-muted">ID: EMP002</small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <i class="fas fa-ellipsis-v action-icon" data-bs-toggle="dropdown"></i>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2"></i> View Details</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i> Print Info</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            <p class="mb-1"><i class="fas fa-briefcase me-2 text-secondary"></i> HR Manager</p>
                            <p class="mb-1"><i class="fas fa-building me-2 text-secondary"></i> Human Resources</p>
                            <p class="mb-3"><i class="fas fa-calendar me-2 text-secondary"></i> Joined: Mar 22, 2019</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="department-tag">HR Department</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personnel Card 3 -->
                <div class="col-md-4 mb-3">
                    <div class="card result-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="profile-img me-3" style="background-color: #fbbc05;">RL</div>
                                    <div>
                                        <h5 class="mb-0">Robert Lee</h5>
                                        <small class="text-muted">ID: EMP003</small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <i class="fas fa-ellipsis-v action-icon" data-bs-toggle="dropdown"></i>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2"></i> View Details</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i> Print Info</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            <p class="mb-1"><i class="fas fa-briefcase me-2 text-secondary"></i> Financial Analyst</p>
                            <p class="mb-1"><i class="fas fa-building me-2 text-secondary"></i> Finance</p>
                            <p class="mb-3"><i class="fas fa-calendar me-2 text-secondary"></i> Joined: Sep 10, 2021</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="department-tag">Finance Department</span>
                                <span class="badge bg-warning text-dark">On Leave</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personnel Card 4 -->
                <div class="col-md-4 mb-3">
                    <div class="card result-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="profile-img me-3" style="background-color: #34a853;">MJ</div>
                                    <div>
                                        <h5 class="mb-0">Maria Johnson</h5>
                                        <small class="text-muted">ID: EMP004</small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <i class="fas fa-ellipsis-v action-icon" data-bs-toggle="dropdown"></i>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2"></i> View Details</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i> Print Info</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            <p class="mb-1"><i class="fas fa-briefcase me-2 text-secondary"></i> Marketing Director</p>
                            <p class="mb-1"><i class="fas fa-building me-2 text-secondary"></i> Marketing</p>
                            <p class="mb-3"><i class="fas fa-calendar me-2 text-secondary"></i> Joined: May 5, 2018</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="department-tag">Marketing Department</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personnel Card 5 -->
                <div class="col-md-4 mb-3">
                    <div class="card result-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="profile-img me-3" style="background-color: #4285f4;">DT</div>
                                    <div>
                                        <h5 class="mb-0">David Thompson</h5>
                                        <small class="text-muted">ID: EMP005</small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <i class="fas fa-ellipsis-v action-icon" data-bs-toggle="dropdown"></i>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2"></i> View Details</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i> Print Info</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            <p class="mb-1"><i class="fas fa-briefcase me-2 text-secondary"></i> Operations Manager</p>
                            <p class="mb-1"><i class="fas fa-building me-2 text-secondary"></i> Operations</p>
                            <p class="mb-3"><i class="fas fa-calendar me-2 text-secondary"></i> Joined: Nov 12, 2020</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="department-tag">Operations Department</span>
                                <span class="badge bg-danger">Inactive</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personnel Card 6 -->
                <div class="col-md-4 mb-3">
                    <div class="card result-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="profile-img me-3" style="background-color: #ea4335;">AP</div>
                                    <div>
                                        <h5 class="mb-0">Anna Parker</h5>
                                        <small class="text-muted">ID: EMP006</small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <i class="fas fa-ellipsis-v action-icon" data-bs-toggle="dropdown"></i>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2"></i> View Details</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i> Print Info</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            <p class="mb-1"><i class="fas fa-briefcase me-2 text-secondary"></i> IT Specialist</p>
                            <p class="mb-1"><i class="fas fa-building me-2 text-secondary"></i> Information Technology</p>
                            <p class="mb-3"><i class="fas fa-calendar me-2 text-secondary"></i> Joined: Feb 28, 2022</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="department-tag">IT Department</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View (Hidden by Default) -->
            <div class="row d-none" id="resultsList">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Position</th>
                                    <th scope="col">Department</th>
                                    <th scope="col">Join Date</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>EMP001</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="profile-img me-2" style="width: 30px; height: 30px; font-size: 12px;">JS</div>
                                            <span>John Smith</span>
                                        </div>
                                    </td>
                                    <td>Senior Developer</td>
                                    <td>Information Technology</td>
                                    <td>Jan 15, 2020</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <i class="fas fa-eye action-icon me-1" title="View"></i>
                                        <i class="fas fa-edit action-icon me-1" title="Edit"></i>
                                        <i class="fas fa-trash-alt action-icon text-danger" title="Delete"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td>EMP002</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="profile-img me-2" style="width: 30px; height: 30px; font-size: 12px; background-color: #ea4335;">SW</div>
                                            <span>Sarah Wilson</span>
                                        </div>
                                    </td>
                                    <td>HR Manager</td>
                                    <td>Human Resources</td>
                                    <td>Mar 22, 2019</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <i class="fas fa-eye action-icon me-1" title="View"></i>
                                        <i class="fas fa-edit action-icon me-1" title="Edit"></i>
                                        <i class="fas fa-trash-alt action-icon text-danger" title="Delete"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td>EMP003</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="profile-img me-2" style="width: 30px; height: 30px; font-size: 12px; background-color: #fbbc05;">RL</div>
                                            <span>Robert Lee</span>
                                        </div>
                                    </td>
                                    <td>Financial Analyst</td>
                                    <td>Finance</td>
                                    <td>Sep 10, 2021</td>
                                    <td><span class="badge bg-warning text-dark">On Leave</span></td>
                                    <td>
                                        <i class="fas fa-eye action-icon me-1" title="View"></i>
                                        <i class="fas fa-edit action-icon me-1" title="Edit"></i>
                                        <i class="fas fa-trash-alt action-icon text-danger" title="Delete"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td>EMP004</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="profile-img me-2" style="width: 30px; height: 30px; font-size: 12px; background-color: #34a853;">MJ</div>
                                            <span>Maria Johnson</span>
                                        </div>
                                    </td>
                                    <td>Marketing Director</td>
                                    <td>Marketing</td>
                                    <td>May 5, 2018</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <i class="fas fa-eye action-icon me-1" title="View"></i>
                                        <i class="fas fa-edit action-icon me-1" title="Edit"></i>
                                        <i class="fas fa-trash-alt action-icon text-danger" title="Delete"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td>EMP005</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="profile-img me-2" style="width: 30px; height: 30px; font-size: 12px; background-color: #4285f4;">DT</div>
                                            <span>David Thompson</span>
                                        </div>
                                    </td>
                                    <td>Operations Manager</td>
                                    <td>Operations</td>
                                    <td>Nov 12, 2020</td>
                                    <td><span class="badge bg-danger">Inactive</span></td>
                                    <td>
                                        <i class="fas fa-eye action-icon me-1" title="View"></i>
                                        <i class="fas fa-edit action-icon me-1" title="Edit"></i>
                                        <i class="fas fa-trash-alt action-icon text-danger" title="Delete"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td>EMP006</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="profile-img me-2" style="width: 30px; height: 30px; font-size: 12px; background-color: #ea4335;">AP</div>
                                            <span>Anna Parker</span>
                                        </div>
                                    </td>
                                    <td>IT Specialist</td>
                                    <td>Information Technology</td>
                                    <td>Feb 28, 2022</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <i class="fas fa-eye action-icon me-1" title="View"></i>
                                        <i class="fas fa-edit action-icon me-1" title="Edit"></i>
                                        <i class="fas fa-trash-alt action-icon text-danger" title="Delete"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
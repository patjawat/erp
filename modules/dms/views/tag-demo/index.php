<style>

        .form-group {
            position: relative;
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .textarea-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        textarea {
            width: 100%;
            min-height: 120px;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            line-height: 1.5;
            resize: vertical;
            outline: none;
            transition: border-color 0.3s;
        }
        
        textarea:focus {
            border-color: #4CAF50;
        }
        
        .autocomplete-dropdown {
            position: absolute;
            background: white;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            min-width: 200px;
        }
        
        .autocomplete-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        
        .autocomplete-item:last-child {
            border-bottom: none;
        }
        
        .autocomplete-item:hover,
        .autocomplete-item.selected {
            background-color: #f0f8ff;
        }
        
        .autocomplete-item.selected {
            background-color: #4CAF50;
            color: white;
        }
        
        .autocomplete-item .item-title {
            font-weight: bold;
            color: #333;
        }
        
        .autocomplete-item .item-desc {
            font-size: 12px;
            color: #666;
            margin-top: 2px;
        }
        
        .autocomplete-item.selected .item-title,
        .autocomplete-item.selected .item-desc {
            color: white;
        }
        
        .demo-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .demo-title {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .usage-info {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #4CAF50;
        }
        
        .usage-info h4 {
            margin-top: 0;
            color: #2e7d32;
        }
        
        .usage-info p {
            margin-bottom: 5px;
            color: #333;
        }
    </style>

<div class="container">
        <h1>Floating Autocomplete for Textarea</h1>
        <p>Compatible with Yii2 and jQuery - พิมพ์ @ หรือ # เพื่อเปิด autocomplete</p>
        
        <div class="usage-info">
            <h4>วิธีการใช้งาน:</h4>
            <p>• พิมพ์ <strong>@</strong> ตามด้วยชื่อผู้ใช้ (เช่น @john, @mary)</p>
            <p>• พิมพ์ <strong>#</strong> ตามด้วยแท็ก (เช่น #work, #project)</p>
            <p>• ใช้ลูกศรขึ้น/ลง เพื่อเลือก และ Enter เพื่อยืนยัน</p>
            <p>• กด Escape เพื่อปิด autocomplete</p>
        </div>
        
        <div class="demo-section">
            <h3 class="demo-title">Demo 1: Mention Users (@)</h3>
            <div class="form-group">
                <label for="textarea1">ข้อความของคุณ:</label>
                <div class="textarea-wrapper">
                    <textarea id="textarea1" placeholder="พิมพ์ @ ตามด้วยชื่อผู้ใช้ เช่น @john"></textarea>
                    <div class="autocomplete-dropdown" id="dropdown1"></div>
                </div>
            </div>
        </div>
        
        <div class="demo-section">
            <h3 class="demo-title">Demo 2: Tags (#)</h3>
            <div class="form-group">
                <label for="textarea2">แท็กและหมวดหมู่:</label>
                <div class="textarea-wrapper">
                    <textarea id="textarea2" placeholder="พิมพ์ # ตามด้วยแท็ก เช่น #work #project"></textarea>
                    <div class="autocomplete-dropdown" id="dropdown2"></div>
                </div>
            </div>
        </div>
        
        <div class="demo-section">
            <h3 class="demo-title">Demo 3: Multiple Triggers</h3>
            <div class="form-group">
                <label for="textarea3">รองรับทั้ง @ และ # ในช่องเดียว:</label>
                <div class="textarea-wrapper">
                    <textarea id="textarea3" placeholder="ลองพิมพ์ @ หรือ # ในข้อความเดียวกัน"></textarea>
                    <div class="autocomplete-dropdown" id="dropdown3"></div>
                </div>
            </div>
        </div>
    </div>


<?php
use yii\web\View;
$js = <<< JS
          // FloatType.js - Floating Autocomplete for Textarea (ES5 Compatible)
        function FloatType(textarea, dropdown, options) {
            this.textarea = $(textarea);
            this.dropdown = $(dropdown);
            this.options = options || {};
            this.options.triggers = this.options.triggers || {};
            this.options.minLength = this.options.minLength || 1;
            this.options.maxItems = this.options.maxItems || 10;
            this.options.debounceTime = this.options.debounceTime || 300;
            
            this.currentTrigger = null;
            this.currentQuery = '';
            this.selectedIndex = -1;
            this.currentItems = [];
            this.isOpen = false;
            this.ajaxRequest = null;
            this.debounceTimer = null;
            
            this.init();
        }
        
        FloatType.prototype.init = function() {
            var self = this;
            
            this.textarea.on('input', function(e) { self.handleInput(e); });
            this.textarea.on('keydown', function(e) { self.handleKeydown(e); });
            this.textarea.on('blur', function(e) { self.handleBlur(e); });
            this.textarea.on('click', function(e) { self.handleClick(e); });
            
            this.dropdown.on('click', '.autocomplete-item', function(e) { self.handleItemClick(e); });
            
            // ป้องกันการ blur เมื่อคลิกใน dropdown
            this.dropdown.on('mousedown', function(e) { e.preventDefault(); });
        };
        
        FloatType.prototype.handleInput = function(e) {
            var self = this;
            var cursorPos = this.textarea[0].selectionStart;
            var text = this.textarea.val();
            var result = this.parseAtCursor(text, cursorPos);
            
            // Cancel previous debounce timer
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
            }
            
            if (result && result.query.length >= this.options.minLength) {
                this.currentTrigger = result.trigger;
                this.currentQuery = result.query;
                
                // Debounce AJAX calls
                this.debounceTimer = setTimeout(function() {
                    self.showAutocomplete(result);
                }, this.options.debounceTime);
            } else {
                this.hideAutocomplete();
            }
        };
        
        FloatType.prototype.handleKeydown = function(e) {
            if (!this.isOpen) return;
            
            switch(e.key || e.which) {
                case 'ArrowDown':
                case 40:
                    e.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.currentItems.length - 1);
                    this.updateSelection();
                    break;
                    
                case 'ArrowUp':
                case 38:
                    e.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                    this.updateSelection();
                    break;
                    
                case 'Enter':
                case 13:
                    e.preventDefault();
                    if (this.selectedIndex >= 0) {
                        this.selectItem(this.currentItems[this.selectedIndex]);
                    }
                    break;
                    
                case 'Escape':
                case 27:
                    e.preventDefault();
                    this.hideAutocomplete();
                    break;
            }
        };
        
        FloatType.prototype.handleBlur = function(e) {
            var self = this;
            // ให้เวลาสำหรับการคลิกใน dropdown
            setTimeout(function() { self.hideAutocomplete(); }, 150);
        };
        
        FloatType.prototype.handleClick = function(e) {
            // ตรวจสอบ autocomplete เมื่อคลิกใน textarea
            this.handleInput(e);
        };
        
        FloatType.prototype.handleItemClick = function(e) {
            var item = $(e.currentTarget);
            var index = item.index();
            if (this.currentItems[index]) {
                this.selectItem(this.currentItems[index]);
            }
        };
        
        FloatType.prototype.parseAtCursor = function(text, cursorPos) {
            var triggers = Object.keys(this.options.triggers);
            
            // หาตำแหน่งเริ่มต้นของคำที่มี trigger
            var start = cursorPos;
            while (start > 0 && !/\s/.test(text[start - 1])) {
                start--;
            }
            
            var word = text.substring(start, cursorPos);
            
            for (var i = 0; i < triggers.length; i++) {
                var trigger = triggers[i];
                if (word.indexOf(trigger) === 0) {
                    var query = word.substring(trigger.length);
                    return {
                        trigger: trigger,
                        query: query,
                        start: start,
                        end: cursorPos
                    };
                }
            }
            
            return null;
        };
        
        FloatType.prototype.showAutocomplete = function(result) {
            var self = this;
            var triggerConfig = this.options.triggers[result.trigger];
            if (!triggerConfig) return;
            
            // Cancel previous AJAX request
            if (this.ajaxRequest) {
                this.ajaxRequest.abort();
            }
            
            // If data is provided as array, use it directly
            if (triggerConfig.data && Array.isArray(triggerConfig.data)) {
                this.filterAndShowItems(triggerConfig.data, result);
                return;
            }
            
            // If URL is provided, make AJAX request
            if (triggerConfig.url) {
                this.showLoading();
                
                var ajaxOptions = {
                    url: triggerConfig.url,
                    type: triggerConfig.method || 'GET',
                    data: {
                        query: result.query,
                        trigger: result.trigger
                    },
                    dataType: 'json',
                    success: function(response) {
                        self.ajaxRequest = null;
                        var items = response.data || response.items || response;
                        self.filterAndShowItems(items, result);
                    },
                    error: function(xhr, status, error) {
                        self.ajaxRequest = null;
                        if (status !== 'abort') {
                            console.error('Autocomplete AJAX Error:', error);
                            self.hideAutocomplete();
                        }
                    }
                };
                
                // Add CSRF token for Yii2 if available
                if (typeof yii !== 'undefined' && yii.getCsrfToken) {
                    ajaxOptions.data[yii.getCsrfParam()] = yii.getCsrfToken();
                }
                
                // Merge custom AJAX options
                if (triggerConfig.ajaxOptions) {
                    $.extend(ajaxOptions, triggerConfig.ajaxOptions);
                }
                
                this.ajaxRequest = $.ajax(ajaxOptions);
            }
        };
        
        FloatType.prototype.filterAndShowItems = function(items, result) {
            if (!Array.isArray(items)) {
                console.error('Invalid items data:', items);
                this.hideAutocomplete();
                return;
            }
            
            // Filter items if query exists
            var filteredItems = [];
            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                if (!result.query || result.query === '') {
                    filteredItems.push(item);
                } else {
                    var query = result.query.toLowerCase();
                    var value = (item.value || '').toLowerCase();
                    var label = (item.label || '').toLowerCase();
                    
                    if (value.indexOf(query) !== -1 || label.indexOf(query) !== -1) {
                        filteredItems.push(item);
                    }
                }
            }
            
            var finalItems = filteredItems.slice(0, this.options.maxItems);
            this.currentItems = finalItems;
            
            if (finalItems.length === 0) {
                this.hideAutocomplete();
                return;
            }
            
            this.renderDropdown(finalItems);
            this.positionDropdown(result);
            this.selectedIndex = 0;
            this.updateSelection();
            this.isOpen = true;
        };
        
        FloatType.prototype.showLoading = function() {
            this.dropdown.html('<div class="autocomplete-item loading">กำลังโหลด...</div>').show();
        };
        
        FloatType.prototype.renderDropdown = function(items) {
            var html = '';
            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                html += '<div class="autocomplete-item">';
                html += '<div class="item-title">' + (item.label || item.value) + '</div>';
                if (item.description) {
                    html += '<div class="item-desc">' + item.description + '</div>';
                }
                html += '</div>';
            }
            
            this.dropdown.html(html).show();
        };
        
        FloatType.prototype.positionDropdown = function(result) {
            var textarea = this.textarea[0];
            var style = window.getComputedStyle(textarea);
            var fontSize = parseInt(style.fontSize);
            var lineHeight = parseInt(style.lineHeight) || fontSize * 1.2;
            
            // คำนวณตำแหน่งของเคอร์เซอร์
            var textBeforeCursor = textarea.value.substring(0, result.start);
            var newlineChar = String.fromCharCode(10); // \n
            var lines = textBeforeCursor.split(newlineChar);
            var currentLine = lines.length - 1;
            var currentCol = lines[lines.length - 1].length;
            
            // ประมาณตำแหน่ง
            var x = currentCol * (fontSize * 0.6);
            var y = currentLine * lineHeight + lineHeight + 5;
            
            this.dropdown.css({
                left: Math.min(x, this.textarea.outerWidth() - 220) + 'px',
                top: y + 'px'
            });
        };
        
        FloatType.prototype.updateSelection = function() {
            this.dropdown.find('.autocomplete-item').removeClass('selected');
            if (this.selectedIndex >= 0) {
                this.dropdown.find('.autocomplete-item').eq(this.selectedIndex).addClass('selected');
            }
        };
        
        FloatType.prototype.selectItem = function(item) {
            var cursorPos = this.textarea[0].selectionStart;
            var text = this.textarea.val();
            var result = this.parseAtCursor(text, cursorPos);
            
            if (result) {
                var newText = text.substring(0, result.start) + 
                               this.currentTrigger + item.value + ' ' + 
                               text.substring(result.end);
                
                this.textarea.val(newText);
                
                // ตั้งค่าตำแหน่ง cursor
                var newCursorPos = result.start + this.currentTrigger.length + item.value.length + 1;
                this.textarea[0].setSelectionRange(newCursorPos, newCursorPos);
                this.textarea.focus();
            }
            
            this.hideAutocomplete();
        };
        
        FloatType.prototype.hideAutocomplete = function() {
            this.dropdown.hide();
            this.isOpen = false;
            this.selectedIndex = -1;
            this.currentItems = [];
            
            // Cancel any pending AJAX request
            if (this.ajaxRequest) {
                this.ajaxRequest.abort();
                this.ajaxRequest = null;
            }
            
            // Cancel debounce timer
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = null;
            }
        };
        
        // Sample data
        var users = [
            { value: 'john', label: 'John Doe', description: 'Software Developer' },
            { value: 'mary', label: 'Mary Smith', description: 'Project Manager' },
            { value: 'bob', label: 'Bob Johnson', description: 'Designer' },
            { value: 'alice', label: 'Alice Brown', description: 'Data Analyst' },
            { value: 'charlie', label: 'Charlie Wilson', description: 'DevOps Engineer' }
        ];
        
        var tags = [
            { value: 'work', label: 'Work', description: 'งานที่เกี่ยวข้องกับการทำงาน' },
            { value: 'project', label: 'Project', description: 'โปรเจ็กต์ต่างๆ' },
            { value: 'meeting', label: 'Meeting', description: 'การประชุม' },
            { value: 'deadline', label: 'Deadline', description: 'กำหนดส่งงาน' },
            { value: 'urgent', label: 'Urgent', description: 'เร่งด่วน' },
            { value: 'todo', label: 'Todo', description: 'รายการสิ่งที่ต้องทำ' },
            { value: 'bug', label: 'Bug', description: 'ข้อผิดพลาดในระบบ' },
            { value: 'feature', label: 'Feature', description: 'ฟีเจอร์ใหม่' }
        ];
        
        // Mock AJAX function for demo
        function mockAjaxUsers(query) {
            var allUsers = [
                { value: 'admin', label: 'Administrator', description: 'System Admin' },
                { value: 'user1', label: 'User One', description: 'Regular User' },
                { value: 'user2', label: 'User Two', description: 'Regular User' },
                { value: 'manager', label: 'Manager', description: 'Department Manager' },
                { value: 'developer', label: 'Developer', description: 'Software Developer' },
                { value: 'designer', label: 'Designer', description: 'UI/UX Designer' },
                { value: 'tester', label: 'Tester', description: 'QA Tester' }
            ];
            
            return allUsers.filter(function(user) {
                return user.value.toLowerCase().indexOf(query.toLowerCase()) !== -1 ||
                       user.label.toLowerCase().indexOf(query.toLowerCase()) !== -1;
            });
        }
        
        // Initialize FloatType instances
        $(document).ready(function() {
            // Demo 1: Users only
            new FloatType('#textarea1', '#dropdown1', {
                triggers: {
                    '@': { data: users }
                }
            });
            
            // Demo 2: Tags only
            new FloatType('#textarea2', '#dropdown2', {
                triggers: {
                    '#': { data: tags }
                }
            });
            
            // Demo 3: Both users and tags
            // new FloatType('#textarea3', '#dropdown3', {
            //     triggers: {
            //         '@': { data: users },
            //         '#': { data: tags }
            //     }
            // });
             new FloatType('#textarea3', '#dropdown3', {
                triggers: {
                    '@': {  
                        url: '/dms/tags/get-data',  // This would be your real endpoint
                        method: 'GET'
                    },
                    '#': { data: tags }
                }
            });
            
            // Demo 4: AJAX simulation
            new FloatType('#textarea4', '#dropdown4', {
                triggers: {
                    '@': { 
                        url: '/mock-ajax-endpoint',  // This would be your real endpoint
                        method: 'GET'
                    }
                },
                debounceTime: 500
            });
            
            // Mock AJAX endpoint for demo
            // $.mockjax({
            //     url: '/mock-ajax-endpoint',
            //     response: function(settings) {
            //         var query = settings.data.query || '';
            //         var results = mockAjaxUsers(query);
                    
            //         // Simulate network delay
            //         this.responseTime = 300;
            //         this.responseText = {
            //             success: true,
            //             data: results
            //         };
            //     }
            // });
        });
        
        // Include mockjax for demo purposes
        (function() {
            var mockjaxCallbacks = [];
            $.mockjax = function(options) {
                mockjaxCallbacks.push(options);
            };
            
            var originalAjax = $.ajax;
            $.ajax = function(settings) {
                for (var i = 0; i < mockjaxCallbacks.length; i++) {
                    var mock = mockjaxCallbacks[i];
                    if (settings.url === mock.url) {
                        setTimeout(function() {
                            mock.response.call(mock, settings);
                            if (settings.success) {
                                settings.success(mock.responseText);
                            }
                        }, mock.responseTime || 0);
                        return { abort: function() {} };
                    }
                }
                return originalAjax.call(this, settings);
            };
        })();
    JS;
    $this->registerJS($js,View::POS_END);
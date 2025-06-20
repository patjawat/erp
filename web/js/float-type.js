
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
                // var y = currentLine * lineHeight + lineHeight + 5;
                var y = currentLine * lineHeight + lineHeight +50;
                
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
            
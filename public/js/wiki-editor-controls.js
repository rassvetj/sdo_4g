(function() {
    var instance = null;

    window.WikiEditor = function (textareaId, options) {

        var self = instance = this;
        this.textarea = document.getElementById(textareaId);
        this.actions = {
            'but_strong': function() {
                self.singleTag('*', '*');
            },
            'but_em': function() {
                self.singleTag('_', '_');
            },
            'but_ins': function() {
                self.singleTag('+', '+');
            },
            'but_del': function() {
                self.singleTag('-', '-');
            },
            'but_code': function() {
                self.singleTag('@', '@');
            },
            'but_h1': function() {
                self.encloseLineSelection('h1. ', '',function(str) {
                    str = str.replace(/^h\d+\.\s+/, '')
                    return str;
                });
            },
            'but_h2': function() {
                self.encloseLineSelection('h2. ', '',function(str) {
                    str = str.replace(/^h\d+\.\s+/, '')
                    return str;
                });
            },
            'but_h3': function() {
                self.encloseLineSelection('h3. ', '',function(str) {
                    str = str.replace(/^h\d+\.\s+/, '')
                    return str;
                });
            },
            'but_ul': function() {
                self.encloseLineSelection('','',function(str) {
                    str = str.replace(/\r/g,'');
                    return str.replace(/(\n|^)[#-]?\s*/g,"$1* ");
                });
            },
            'but_ol': function() {
                self.encloseLineSelection('','',function(str) {
                    str = str.replace(/\r/g,'');
                    return str.replace(/(\n|^)[*-]?\s*/g,"$1# ");
                });
            },
            'but_bq': function() {
                self.encloseLineSelection('bq. ', "\n",function(str) {
                    str = str.replace(/^bq\.\s+/, '');
                    return str;
                });
            },
            'but_unbq': function() {
                self.encloseLineSelection('','',function(str) {
                    str = str.replace(/\r/g,'');
                    return str.replace(/(\n|^) *(bq\.)? *([^\n]*)/g,"$1$3");
                });
            },
            'but_pre': function() {
                self.singleTag('<pre>', '</pre>');
            },
            'but_link': function() {
                self.singleTag('[[', ']]');
            },
            'but_img': function() {
                $('<div id=\"myelfinder\" />').elfinder({
                    url : options.connectorUrl,
                    lang: options.lang,
                    view: 'list',
                    places: '',
                    toolbar : [
                        ['reload'],
                        ['select', 'open'],
                        ['mkdir', 'upload'],
                        ['rename', 'comment', 'copy', 'paste', 'rm'],
                        ['info']
                    ],
                    contextmenu : {
                        'cwd'   : ['reload', 'delim', 'mkdir', 'upload', 'paste', 'delim', 'info'],
                        'file'  : ['select', 'open', 'copy', 'cut', 'rm', 'rename', 'comment', 'info'],
                        'group' : ['copy', 'cut', 'rm', 'info']
                    },
                    dialog : { width : 900, modal : true, title : '' },
                    closeOnEditorCallback : true,
                    editorCallback : function(path, fileObj) {
                        var ext = path.split('.');
                        ext = ext.pop();
                    var imgExts = ['jpg', 'jpeg', 'gif', 'bmp', 'png'];
                        if($.inArray(ext, imgExts) == -1) {
                            self.encloseSelection('', '', function(str) {
                                return '[['+fileObj.name+'|'+fileObj.openUrl+']]';
                        });
                        } else {
                            self.encloseSelection('', '', function(str) {
                                return '!'+path+'!';
                            });
                    }
                        // replace_selection(textarea, '!'+img+'!');

                    }
                });
            },
            'but_swf': function() {
                $('<div id=\"myelfinder\" />').elfinder({
                    url : options.connectorUrl,
                    lang: options.lang,
                    view: 'list',
                    places: '',
                    toolbar : [
                        ['reload'],
                        ['select', 'open'],
                        ['mkdir', 'upload'],
                        ['rename', 'comment', 'copy', 'paste', 'rm'],
                        ['info']
                    ],
                    contextmenu : {
                        'cwd'   : ['reload', 'delim', 'mkdir', 'upload', 'paste', 'delim', 'info'],
                        'file'  : ['select', 'open', 'copy', 'cut', 'rm', 'rename', 'comment', 'info'],
                        'group' : ['copy', 'cut', 'rm', 'info']
                    },
                    dialog : { width : 900, modal : true, title : '' },
                    closeOnEditorCallback : true,
                    editorCallback : function(path, fileObj) {
                        var ext = path.split('.');
                        ext = ext.pop();
                        var imgExts = ['swf'];
                        if($.inArray(ext, imgExts) == -1) {
                            self.encloseSelection('', '', function(str) {
                                return '[['+fileObj.name+'|'+fileObj.openUrl+']]';
                            });
                        } else {
                            self.encloseSelection('', '', function(str) {
                                return 'embed!'+path+'!embed';
                            });
                        }

                    }
                });
            },
            'but_video': function() {
                $('<div id=\"myelfinder\" />').elfinder({
                    url : options.connectorUrl,
                    lang: options.lang,
                    view: 'list',
                    places: '',
                    toolbar : [
                        ['reload'],
                        ['select', 'open'],
                        ['mkdir', 'upload'],
                        ['rename', 'comment', 'copy', 'paste', 'rm'],
                        ['info']
                    ],
                    contextmenu : {
                        'cwd'   : ['reload', 'delim', 'mkdir', 'upload', 'paste', 'delim', 'info'],
                        'file'  : ['select', 'open', 'copy', 'cut', 'rm', 'rename', 'comment', 'info'],
                        'group' : ['copy', 'cut', 'rm', 'info']
                    },
                    dialog : { width : 900, modal : true, title : '' },
                    closeOnEditorCallback : true,
                    editorCallback : function(path, fileObj) {
                        var ext = path.split('.');
                        ext = ext.pop();
                        var imgExts = ['mp4'];
                        if($.inArray(ext, imgExts) == -1) {
                            self.encloseSelection('', '', function(str) {
                                return '[['+fileObj.name+'|'+fileObj.openUrl+']]';
                            });
                        } else {
                            self.encloseSelection('', '', function(str) {
                                return 'video!({width:320px;height:240px;})'+path+'!video';
                            });
                        }

                    }
                });
            },
            'but_math': function() {
                self.encloseSelection(' %(AM)` ', ' `% ');
            },
            'but_math_element': function() {
                $('#mathML').dialog({
                    modal:true,
                    width:650,
                    height:395,
                    resizable: false,
                    title:"Выберите элемент"
                });
            }
        };
        this.action = function(actionName) {
            var callback = self.actions[actionName];
            callback();
        };

        this.singleTag = function(stag, etag) {
            stag = stag || null;
            etag = etag || stag;

            if (!stag || !etag) { return; }

            self.encloseSelection(stag, etag);
        };

        this.encloseLineSelection = function(prefix, suffix, fn) {
            self.textarea.focus();

            prefix = prefix || '';
            suffix = suffix || '';

            var start, end, sel, scrollPos, subst, res;

            if (typeof(document["selection"]) != "undefined") {
                var range;
                if(window.ieRetardedClick && window.ieCachedRange) {
                    range = window.ieCachedRange;
                    window.ieRetardedClick = false;
                } else {
                    range = document.selection.createRange();
                }
                var tr = self.textarea.createTextRange();
                var tr2 = tr.duplicate();
                tr2.moveToBookmark(range.getBookmark());
                tr.setEndPoint('EndToStart',tr2);
                if (range == null || tr == null) {
                    sel = ''
                }
                var textPart = range.text.replace(/[\r\n]/g,'.'); //for some reason IE doesn't always count the \n and \r in the length
                var textWhole = self.textarea.value.replace(/[\r\n]/g,'.');
                start = textWhole.indexOf(textPart, tr.text.length);
                end = start + textPart.length
                // go to the start of the line
                start = self.textarea.value.substring(0, start).replace(/[^\r\n]*$/g,'').length;
                // go to the end of the line
                end = self.textarea.value.length - self.textarea.value.substring(end, self.textarea.value.length).replace(/^[^\r\n]*/, '').length;
                sel = self.textarea.value.substring(start, end);
                // sel = range.text;
            } else if (typeof(self.textarea["setSelectionRange"]) != "undefined") {
                start = self.textarea.selectionStart;
                end = self.textarea.selectionEnd;
                scrollPos = self.textarea.scrollTop;
                // go to the start of the line
                start = self.textarea.value.substring(0, start).replace(/[^\r\n]*$/g,'').length;
                // go to the end of the line
                end = self.textarea.value.length - self.textarea.value.substring(end, self.textarea.value.length).replace(/^[^\r\n]*/, '').length;
                sel = self.textarea.value.substring(start, end);
            }
            if (sel.match(/ $/)) { // exclude ending space char, if any
                sel = sel.substring(0, sel.length - 1);
                suffix = suffix + " ";
            }

            if (typeof(fn) == 'function') {
                res = (sel) ? fn.call(this,sel) : fn('');
                } else {
                res = (sel) ? sel : '';
                }

            subst = prefix + res + suffix;
            if (typeof(document["selection"]) != "undefined") {
                self.textarea.value = self.textarea.value.substring(0, start) + subst +
                self.textarea.value.substring(end);
                range.collapse(false);
                if (sel) {
                    range.move('character', start + subst.length);
                } else {
                    range.move('character', start + prefix.length);
                }
                range.select();
            } else if (typeof(self.textarea["setSelectionRange"]) != "undefined") {
                self.textarea.value = self.textarea.value.substring(0, start) + subst +
                self.textarea.value.substring(end);
                if (sel) {
                    self.textarea.setSelectionRange(start + subst.length, start + subst.length);
                } else {
                    self.textarea.setSelectionRange(start + prefix.length, start + prefix.length);
            }
                self.textarea.scrollTop = scrollPos;
            }
        },

        this.encloseSelection = function(prefix, suffix, fn) {
            self.textarea.focus();

            prefix = prefix || '';
            suffix = suffix || '';

            var start, end, sel, scrollPos, subst, res;

            if (typeof(document["selection"]) != "undefined") {
                var range;
                if(window.ieRetardedClick && window.ieCachedRange) {
                    range = window.ieCachedRange;
                    window.ieRetardedClick = false;
                } else {
                    range = document.selection.createRange();
                }
                sel = range.text;
            } else if (typeof(self.textarea["setSelectionRange"]) != "undefined") {
                start = self.textarea.selectionStart;
                end = self.textarea.selectionEnd;
                scrollPos = self.textarea.scrollTop;
                sel = self.textarea.value.substring(start, end);
            }
            if (sel.match(/ $/)) { // exclude ending space char, if any
                sel = sel.substring(0, sel.length - 1);
                suffix = suffix + " ";
            }

            if (typeof(fn) == 'function') {
                res = (sel) ? fn.call(this,sel) : fn('');
                } else {
                res = (sel) ? sel : '';
                }

            if(res[res.length-1] == "\n") {
                subst = prefix + $.trim(res) + suffix + "\n";
            } else {
                subst = prefix + res + suffix;
            }
            subst = prefix + res + suffix;
            if (typeof(document["selection"]) != "undefined") {
                range.text = subst;
                range.collapse(false);
                range.move('character', -suffix.length);
                range.select();
            } else if (typeof(self.textarea["setSelectionRange"]) != "undefined") {
                self.textarea.value = self.textarea.value.substring(0, start) + subst +
                self.textarea.value.substring(end);
                if (sel) {
                    self.textarea.setSelectionRange(start + subst.length, start + subst.length);
                } else {
                    self.textarea.setSelectionRange(start + prefix.length, start + prefix.length);
                }
                self.textarea.scrollTop = scrollPos;
            }
        }

        window.ieCachedRange = null;
        window.ieRetardedClick = false;

        if ($.browser.msie) {
            $('.hm-wiki-editor .button').mousedown(function() {
                window.ieRetardedClick = true;
                window.ieCachedRange = document.selection.createRange();
            });
        }

        $('.hm-wiki-editor .button').click(function(){
            for(var className in self.actions) {
                if($(this).hasClass(className)) {
                    self.action(className);
                }
            }
        });
    }

    WikiEditor.getInstance = function() {
        if (instance === null) {
            instance = new this();
        }
        return instance;
    };

})();
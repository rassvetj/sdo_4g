(function() {

	var MapPaint = {
		/**
		 * Инициализация объекта
		 */
		init: function() {
			this.$inputList = $("input[name^='maptext[']");
			
			var image = document.getElementById('image'),
				cont = image.parentNode;
			
			this.canvas = document.createElement('canvas');
			
			if (window.G_vmlCanvasManager) {
				G_vmlCanvasManager.initElement(this.canvas);
			}
			
			$(this.canvas).css({
				position: 'absolute',
				left: 0,
				top: 0,
				width: image.offsetWidth,
				height: image.offsetHeight
			})
			
			this.canvas.width  = image.offsetWidth;
			this.canvas.height = image.offsetHeight; 
			
			this.ctx = this.canvas.getContext('2d');
			
			cont.appendChild(this.canvas);
			
			this.$inputList.bind('change', _.bind(this.onInputChange, this));
	    	this.onInputChange();
		},
		/**
		 * Событие изменения содержимого инпута
		 */
		onInputChange: function(e) {
			var mapsTrimmed = [];
			this.$inputList.each(function(i, input) {
				if (input.value) {
					var maps = input.value.split(';');
					
					for (var i = 0, ln = maps.length; i < ln; i++) {
						var map = $.trim(maps[i]);
						if (map) {
							mapsTrimmed.push(map);
						}
					}
				}
			});
			
			MapPaint.drawMaps(
				MapPaint.parseMaps(mapsTrimmed)
			);
		},
		
		/**
		 * Проводит валидацию шаблонов на корректность и возвращает структуру только 
		 * для корректных данных
		 */
		parseMaps: function(maps) {
			
			var result = [];
			
			for (var i = 0, ln = maps.length; i < ln; i++) {
				
				var parsedMap = this.parseMap($.trim(maps[i]));
				
				if (parsedMap) {
					result.push(parsedMap);
				}
			}
			
			return result;
			
		},
		
		PATTERN_RECT: /^RECT\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)(?:\s+(\w+))?$/,
		PATTERN_CIRC: /^CIRC\s+(\d+)\s+(\d+)\s+(\d+)(?:\s+(\w+))?$/,
		PATTERN_POLY: /^POLY((?:\s+\d+\s+\d+)+)(?:\s+(\w+))?$/,
		
		/**
		 * @param map
		 * @returns
		 */
		parseMap: function(map) {
			// RECT
			var matches = map.match(this.PATTERN_RECT);
			
			if (matches) {
				return {
					type: 'RECT',
					x1: matches[1] - 0,
					y1: matches[2] - 0,
					x2: matches[3] - 0,
					y2: matches[4] - 0,
					title: matches[5] ? matches[5] : '',
					source: map
				};
			}
			// CIRC
			var matches = map.match(this.PATTERN_CIRC);
			
			if (matches) {
				return {
					type: 'CIRC',
					x: matches[1] - 0,
					y: matches[2] - 0,
					r: matches[3] - 0,
					title: matches[4] ? matches[4] : '',
					source: map
				};
			}
			// POLY
			var matches = map.match(this.PATTERN_POLY);
			
			if (matches) {
				
				var pointsArr = $.trim(matches[1]).split(/\s+/),
					title = matches[2],
					points = [];
				
				for (var i = 0, ln = pointsArr.length; i < ln; i = i + 2) {
					points.push({
						x: pointsArr[i] - 0,
						y: pointsArr[i + 1] - 0
					});
				}
				
				return {
					type: 'POLY',
					points: points,
					title: title ? title : '',
					source: map
				};
			}
			
			return {
				type: 'UNKNOWN',
				source: map
			};
		},
		
		drawMaps: function(maps) {
			
			this.ctx.beginPath();
			this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
			
			for (var i = 0, ln = maps.length; i < ln; i++) {
				var map = maps[i];
				
				switch(map.type) {
					case 'RECT':
						
						this.drowMap_RECT(map);
						
						break;
					case 'CIRC':
						
						this.drowMap_CIRC(map);
						
						break;
					case 'POLY':
						
						this.drowMap_POLY(map);
						
						break;
				}
			}
			this.ctx.closePath();
			
			
		},
		
		drowMap_RECT: function(cfg) {
			this.ctx.strokeRect(cfg.x1, cfg.y1, cfg.x2, cfg.y2);
			console.log('RECT', cfg);
		},
		
		drowMap_CIRC: function(cfg) {
			this.ctx.arc(cfg.x, cfg.y, cfg.r, 0, 2.0*Math.PI, 0);
			this.ctx.stroke();
			console.log('CIRC', cfg);
		},
		
		drowMap_POLY: function(cfg) {

			var points = cfg.points,
				ln = points.length;
			
			console.log('POLY', cfg);
			
			if (ln === 1) {
				return;
			}
			
			this.ctx.moveTo(points[0].x, points[0].y);
			
			var firstPoint = points.shift();
			
			points.push(firstPoint);
			
			for (var i = 0; i < ln; i++) {
				this.ctx.lineTo(points[i].x, points[i].y);
			}
			
			this.ctx.stroke();
		}
	};
	
	
	$(window).bind('load', function() {
		yepnope({
		    test: Modernizr.canvas,
		    nope: ['/js/lib/jquery/excanvas.compiled.js'],
		    complete: function () {
		    	MapPaint.init();
		    }
		});
	});
})();

// врубаем тултипы
yepnope({
    test: Modernizr.canvas,
    nope: ['/js/lib/jquery/excanvas.compiled.js'],
    complete: function () {
        yepnope({
            test: $.fn.bt,
            nope: [
                '/css/jquery-ui/jquery.ui.tooltip.css',
                '/js/lib/jquery/jquery.hoverIntent.minified.js',
                '/js/lib/jquery/jquery.ui.tooltip.js'
            ],
            complete: function () {
                _.delay(function () {
                    jQuery(function ($) {
                        $('.tooltip').bt({
                            killTitle: true,
                            contentSelector: '$(this).attr("title")'
                        });
                    });
                }, 100);
            }
        });
    }
});
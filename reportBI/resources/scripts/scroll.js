window.addCustScroll = window.addCustScroll || function(opts) {
	this.init(opts);
}

window.addCustScroll.prototype = {
	init: function(opts){

		this.opts = opts;
		this.opts.filter = (this.opts.filter instanceof $) ? this.opts.filter : $(this.opts.filter);
		this.main = this.opts.filter.children('.custom-scroll-main');

		this.filterSize = 0;
		this.mainSize = 0;
		this.detectSize();
		if (this.filterSize >= this.mainSize) return;


		this.posInCSS = '';
		this.trackPos = 0;
		this.trackActualSize = 0;
		this.trackSize = 0;
		this.trackRatio = 0;
		this.thumbSize = 0;
		this.thumbEndPos = 0;

		this.unbindDefault();
		this.bar = $('<div class="custom-scrollbar"></div>');
		this.track = $('<div class="custom-scrollbar-track"></div>').appendTo(this.bar);
		this.thumb = $('<div class="custom-scrollbar-thumb"></div>').appendTo(this.track);
		this.bar.appendTo(this.opts.filter);
		this.setBarData();
		this.trackAttachClick();
		this.thumbAttachEvent();

		if (this.opts.directionTriggers) {
			this.prevTrigger = null;
			this.nextTrigger = null;
			this.prevStatus = true;
			this.nextStatus = true;
			this.attachDirectionTriggers();
		}
		if (!this.opts.withoutWheel) this.attachWheel();
	},
	root: $(document),
	requestAnimationFrame: window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || window.msRequestAnimationFrame,
	cancelAnimationFrame: window.cancelAnimationFrame || window.mozCancelAnimationFrame,
	detectSize: function() {
		if (this.opts.direction == 'horizontal') {
			this.filterSize = this.opts.filter.width();
			// this.filterSize = this.opts.filter.outerWidth();
			this.mainSize = this.opts.mainSize || this.main.outerWidth();

		} else {
			this.filterSize = this.opts.filter.height();
			// this.filterSize = this.opts.filter.outerHeight();
			this.mainSize = this.opts.mainSize || this.main.outerHeight();
		}
		console.log(this.mainSize)
	},
	unbindDefault: function() {
		var filter = this.opts.filter[0];
		if (window.ActiveXObject !== undefined) {
			filter.onselectstart = function() {
				return false
			}
		} else if ('mozInnerScreenX' in window) {
			filter.style.MozUserSelect = 'none';
		} else if ( !! window.WebKitCSSMatrix) {
			filter.style.webkitUserSelect = 'none';
		}
		filter.style.overflow = 'hidden';
	},
	setBarData: function() {
		if (this.opts.direction == 'horizontal') {
			// var dis = 0;
			this.posInCSS = 'left';
			this.trackSize = this.track.outerWidth();
			if (this.opts.autoThumbSize) {
				this.thumbSize = parseInt(this.filterSize / this.mainSize * this.trackSize);
				this.thumb.width(this.thumbSize);
			} else {
				this.thumbSize = this.thumb.outerWidth();
			}
			// dis = this.opts.filter.scrollLeft();
			this.opts.filter.scrollLeft(0);
		} else {
			this.posInCSS = 'top';
			this.trackSize = this.track.outerHeight();
			if (this.opts.autoThumbSize) {
				this.thumbSize = parseInt(this.filterSize / this.mainSize * this.trackSize);
				this.thumb.height(this.thumbSize);
			} else {
				this.thumbSize = this.thumb.outerHeight();

			}
			// dis = this.opts.filter.scrollTop();
			this.opts.filter.scrollTop(0);
		}
		this.trackActualSize = this.trackSize - this.thumbSize;
		// this.trackPos = this.track.offset()[this.posInCSS];
		this.opts.filter.css('overflow', 'hidden');
		this.trackRatio = this.trackActualSize / (this.mainSize - this.trackSize - (this.filterSize - this.trackSize));
		// this.setPos(dis)
	},
	converttrackPos: function() {
		this.thumb.css(this.posInCSS, this.thumbEndPos);
		this.main.css(this.posInCSS, -this.thumbEndPos / this.trackRatio);
	},
	trackAttachClick: function() {
		var that = this;
		this.track.click(function(event) {
			that.trackPos = that.track.offset()[that.posInCSS];
			that.thumbEndPos = (that.opts.direction == 'horizontal' ? event.pageX : event.pageY) - that.trackPos - that.thumbSize / 2;
			if (that.thumbEndPos >= that.trackActualSize) {
				that.thumbEndPos = that.trackActualSize;
			} else if (that.thumbEndPos <= that.thumbSize / 2) {
				that.thumbEndPos = 0;
			}
			var thumbAniObj = {};
			thumbAniObj[that.posInCSS] = that.thumbEndPos;
			that.thumb.stop(true, false).animate(thumbAniObj, {
				duration: 300,
				step: function(n) {
					that.main.css(that.posInCSS, -n / that.trackRatio);
				}
			});
			// }
		})
	},
	thumbAttachEvent: function() {
		if (!this.opts.disableDraggable) {
			var that = this
			diff = 0;

			function rootAttachEvent(event) {
				that.thumbEndPos = (that.opts.direction == 'horizontal' ? event.pageX : event.pageY) - diff;
				if (that.thumbEndPos <= 0) {
					that.thumbEndPos = 0;
				} else if (that.thumbEndPos >= that.trackActualSize) {
					that.thumbEndPos = that.trackActualSize;
				}
				that.converttrackPos();
			};
			this.thumb.mousedown(function(e) {
				that.trackPos = that.track.offset()[that.posInCSS];
				diff = (that.opts.direction == 'horizontal' ? e.pageX : e.pageY) - that.thumb.offset()[that.posInCSS] + that.trackPos;
				that.root.mousemove(rootAttachEvent).mouseup(function() {
					that.root.off('mousemove', rootAttachEvent);
				})
			})
		}

		this.thumb.click(function() {
			return false;
		});
	},
	rootAttachEvent: function(event) {
		this.thumbEndPos = (this.opts.direction == 'horizontal' ? event.pageX : event.pageY) - diff;
		if (this.thumbEndPos <= 0) {
			this.thumbEndPos = 0;
		} else if (this.thumbEndPos >= this.trackActualSize) {
			this.thumbEndPos = this.trackActualSize;
		}
		this.converttrackPos();
	},
	attachDirectionTriggers: function() {
		this.prevTrigger = $('<div class="custom-scrollbar-button-prev"></div>');
		this.nextTrigger = $('<div class="custom-scrollbar-button-next"></div>');
		this.bar.append(this.prevTrigger, this.nextTrigger);
		var movespeed = 5,
			that = this;
		this.prevTrigger.mousedown(function() {
			that.prevStatus = 1;
			prevFn();
		}).mouseup(function() {
			that.prevStatus = 0;
		})
		this.nextTrigger.mousedown(function() {
			that.nextStatus = 1;
			nextFn();
		}).mouseup(function() {
			that.nextStatus = 0;
		})

		this.root.mouseup(function() {
			that.prevStatus = 0;
			that.nextStatus = 0;
		});

		function prevFn() {
			if (that.thumbEndPos > movespeed) {
				that.thumbEndPos -= movespeed;
			} else if (that.thumbEndPos <= movespeed) {
				that.thumbEndPos = 0;
			}
			// that.thumb.css(that.posInCSS, that.thumbEndPos);
			// that.main.css(that.posInCSS, - that.thumbEndPos / that.trackRatio);
			that.converttrackPos();
			if (that.prevStatus) {
				if ( !! that.requestAnimationFrame) {
					that.requestAnimationFrame.call(window, prevFn);
				} else {
					setTimeout(prevFn, 20)
				}
			} else {
				if ( !! that.requestAnimationFrame) that.cancelAnimationFrame.call(window, prevFn);
			}
		};

		function nextFn() {
			if (that.thumbEndPos < (that.trackActualSize - movespeed)) {
				that.thumbEndPos += movespeed
			} else if (that.thumbEndPos >= (that.trackActualSize - movespeed)) {
				that.thumbEndPos = that.trackActualSize;
			}
			// that.thumb.css(that.posInCSS, that.thumbEndPos);
			// that.main.css(that.posInCSS, - that.thumbEndPos / that.trackRatio);
			that.converttrackPos();
			if (that.nextStatus) {
				if ( !! that.requestAnimationFrame) {
					that.requestAnimationFrame.call(window, nextFn);
				} else {
					setTimeout(nextFn, 20)
				}
			} else {
				if ( !! that.requestAnimationFrame) that.cancelAnimationFrame.call(window, nextFn);
			}
		};
	},
	attachWheelFn: function(ele, handler) {
		if (ele.attachEvent) {
			ele.attachEvent('onmousewheel', handler);
		} else if (ele.addEventListener) {
			if ('mozInnerScreenX' in window) {
				ele.addEventListener('DOMMouseScroll', handler, false);
			} else {
				ele.addEventListener('mousewheel', handler, false);
			}
		}
	},
	implementWheel: function(e) {
		var dis = 10,
			deltavalue = e.wheelDelta ? -parseInt((e.wheelDelta / 120) * dis) : -parseInt((e.detail / -3) * dis);
		if (this.thumbEndPos < dis) {
			// this.deltavalue < 0 ? this.wheelSwitch('min') : this.wheelSwitch('normal', e);	
			if (deltavalue < 0) {
				this.thumbEndPos = 0;
			} else {
				this.thumbEndPos += deltavalue;
				e.preventDefault ? e.preventDefault() : e.returnValue = false;
			}
		} else if (this.thumbEndPos > (this.trackActualSize - dis)) {
			// this.deltavalue < 0 ? this.wheelSwitch('normal', e) : this.wheelSwitch('max');	
			if (deltavalue < 0) {
				this.thumbEndPos += deltavalue;
				e.preventDefault ? e.preventDefault() : e.returnValue = false;
			} else {
				this.thumbEndPos = this.trackActualSize;
			}
		} else {
			if (this.thumbEndPos - deltavalue < dis && deltavalue < 0) {
				this.thumbEndPos = 0;
			} else if (this.thumbEndPos + deltavalue > (this.trackActualSize - dis) && deltavalue > 0) {
				this.thumbEndPos = this.trackActualSize;
			} else {
				this.thumbEndPos += deltavalue;
				e.preventDefault ? e.preventDefault() : e.returnValue = false;
			}
		}
		// this.thumb.css(this.posInCSS, this.thumbEndPos);
		// this.main.css(this.posInCSS, - this.thumbEndPos / this.trackRatio);
		this.converttrackPos();
	},
	attachWheel: function() {
		var that = this;
		this.attachWheelFn(this.opts.filter[0], function(event) {
			that.implementWheel(event);
		})
	},
	setPos: function(pos) {
		this.thumbEndPos = pos;
		if (this.thumbEndPos >= this.trackActualSize) {
			this.thumbEndPos = this.trackActualSize;
		} else if (this.thumbEndPos <= this.thumbSize / 2) {
			this.thumbEndPos = 0;
		}
		this.converttrackPos();
	},
	recountSize: function() {
		if (this.posInCSS == '') {
			return;
		}
		this.setPos(0);
		if (this.opts.direction == 'horizontal') {
			this.mainSize = this.main.outerWidth();
			if (this.filterSize >= this.mainSize) {
				this.bar.css('visibility', 'hidden');
				return;
			}
			this.bar.css('visibility', 'visible');

			this.thumbSize = parseInt(this.filterSize / this.mainSize * this.trackSize);
			this.thumb.width(this.thumbSize);
		} else {
			this.mainSize = this.main.outerHeight();
			if (this.filterSize >= this.mainSize) {
				this.bar.css('visibility', 'hidden');
				return;
			}
			this.bar.css('visibility', 'visible');

			this.thumbSize = parseInt(this.filterSize / this.mainSize * this.trackSize);
			this.thumb.height(this.thumbSize);
		}
		this.trackActualSize = this.trackSize - this.thumbSize;
		this.trackRatio = this.trackActualSize / (this.mainSize - this.trackSize - (this.filterSize - this.trackSize));
	}
}
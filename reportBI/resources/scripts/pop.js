var PopMgr = function() {
	var storedInstance = null,
		PopBox = function() {
			this.config = {
				fitOffset: 50,
				esckey: true,
				nameSpace: 'popns'
			};
			var popstr = '<div id="popwrap"><div id="popinner"><div id="popclose"></div><a id="popexpand" href="" target="_blank"></a><div id="poploader"></div><div id="popcontent"></div></div></div><div id="masklayer"></div>';
			document.body.insertAdjacentHTML('beforeend', popstr);

			this.items = {
				masklayer: $('#masklayer'),
				wrap: $('#popwrap'),
				inner: $('#popinner'),
				close: $('#popclose'),
				expand: document.getElementById('popexpand'),
				content: $('#popcontent'),
				loader: $('#poploader'),
				doc: $(document),
				win: $(window),
				isIE6: !window.XMLHttpRequest,
				isVisible: false,
				scrollTimer: null,
				resizeTimer: null
			}

			this._initPop();
		};

	PopBox.prototype = {
		_initPop: function() {
			this.config.defaultw = parseInt(this.items.inner.css('width'));
			this.config.defaulth = parseInt(this.items.inner.css('height'));
			this.config.defaultpt = parseInt(this.items.inner.css('paddingTop'));
			this.config.defaultpr = parseInt(this.items.inner.css('paddingRight'));
			this.config.defaultpb = parseInt(this.items.inner.css('paddingBottom'));
			this.config.defaultpl = parseInt(this.items.inner.css('paddingLeft'));

			this.config.defaultrh = this.config.defaulth + this.config.defaultpt + this.config.defaultpb;

			this.items.winh = this.items.win.height();
			this.items.defaultmt = this.items.isIE6 ? (this.items.winh - this.config.defaultrh) / 2 + this.items.win.scrollTop() : -this.config.defaultrh / 2;

			this.items.inner.css({
				'width': this.config.defaultw,
				'height': this.config.defaulth,
				'marginTop': this.items.defaultmt,
				'padding': this.config.defaultpt + 'px ' + this.config.defaultpr + 'px ' + this.config.defaultpb + 'px ' + this.config.defaultpl + 'px'
			});

			this._attachClose();
		},
		_animatePop: function(callback) {
			var that = this;
			this.items.inner.stop(true);
			// if (this.actualw != this.config.defaultw || this.actualh != this.config.defaulth) {
			// 	this.items.loader.css('visibility', 'hidden');
			// 	this.items.inner.animate({
			// 		'width': that.actualw,
			// 		'height': that.actualh,
			// 		'marginTop': that.actualmt
			// 	}, 500, function() {

			// 		that.items.content.css('visibility', 'visible');
			// 		if (that.items.isIE6) that.items.isVisible = true;
			// 		if (callback) callback.call(that)
			// 	});
			// } else {
			this.items.loader.css('visibility', 'hidden');
			this.items.inner.css({
				'width': that.actualw,
				'height': that.actualh,
				'marginTop': that.actualmt
			});
			this.items.content.css('visibility', 'visible');
			// if (this.items.isIE6) this.items.isVisible = true;
			this.items.isVisible = true;
			if (callback) callback.call(this)
			// }

		},
		showPop: function(data) {
			this.items.wrap.show();

			this.actualw = data.width || this.config.defaultw;
			this.actualh = data.height || this.config.defaulth;
			this.actualrh = data.height ? (data.height + this.config.defaultpt + this.config.defaultpb) : this.config.defaultrh;

			this.items.inner.css('margin', data.left != undefined ? '0 0 0 ' + data.left : '0 auto')

			data.pos = data.pos || 'fixed';
			if (!this.items.isIE6) {
				if (data.pos != 'absolute') {
					this.actualmt = data.top == undefined ? -this.actualrh / 2 : 0;
					this.items.wrap.css('height', '100%');
					this.items.inner.css(data.top == undefined ? {
						'top': '50%',
						'marginTop': this.items.defaultmt
					} : {
						'top': 0,
						'marginTop': data.top
					});
				} else {
					this._enhanceResize(data.top);
				}
				this.items.wrap.css('position', data.pos);
			} else {
				this._enhanceResize(data.top);
				if (data.pos != 'absolute') {
					this._bindResizeScrollFn(data.top);
				}
			}
			this.items.masklayer.fadeTo(400, 0.8);
			if (data.inline) {
				var inline = data.inline.clone(true);
				this.items.content.html(inline);
				inline.show();
				this._animatePop(data.callback);
			} else if (data.iframe) {
				if (!this.items.iframe) {
					this.items.iframe = document.createElement('iframe');
					this.items.iframe.width = '100%';
					this.items.iframe.height = '100%';
					this.items.iframe.frameBorder = 0;
					this.items.iframe.allowTransparency = 'true';
				}

				this.items.iframe.name = this.config.nameSpace + (new Date()).getTime();
				this.items.iframe.src = data.iframe;
				this.items.content.html(this.items.iframe);
				this._animatePop(data.callback);
			} else if (data.img) {
				if (!data.img.match(/^(http|https|ftp|file):\/\//i)) {
					this.items.baseurl = this.items.baseurl || this._getBaseURL();
					data.img = this.items.baseurl + data.img;
				}
				this.items.img = new Image();
				var that = this;
				this.items.img.onload = function() {
					var fit = data.fit || 0;
					if (fit) var expanded = 0;
					that.items.content.html(that.items.img);
					that.actualw = this.width;
					that.actualh = this.height;
					that.actualrw = that.actualw + that.config.defaultpr + that.config.defaultpl;
					that.actualrh = that.actualh + that.config.defaultpt + that.config.defaultpb;
					if (fit) {
						that.items.winw = that.items.win.width();
						that.items.winh = that.items.win.height();
					}
					while (fit) {
						if (that.actualrw >= that.items.winw) {
							if (!expanded) expanded = 1;
							that.actualrh = (that.actualrh / that.actualrw) * (that.items.winw - that.config.fitOffset);
							that.actualrw = (that.items.winw - that.config.fitOffset);
						} else if (that.actualrh >= that.items.winh) {
							if (!expanded) expanded = 1;
							that.actualrw = (that.actualrw / that.actualrh) * (that.items.winh - that.config.fitOffset);
							that.actualrh = that.items.winh - that.config.fitOffset;
						} else {
							fit = 0;
							if (expanded) {
								that.items.expand.href = data.img;
								that.items.expand.style.visibility = 'visible';
							}
						};
					};

					that.actualw = parseInt(that.actualrw - (that.config.defaultpr + that.config.defaultpl));
					that.actualh = parseInt(that.actualrh - (that.config.defaultpt + that.config.defaultpb));

					that.actualmt = (that.items.isIE6 || data.pos == 'absolute') ? (that.items.winh - that.actualrh) / 2 + that.items.win.scrollTop() : -that.actualrh / 2;
					that.items.img.width = that.actualw;
					that.items.img.height = that.actualh;
					that._animatePop(data.callback);
					that.items.img.onload = null;
				};
				this.items.img.onerror = function() {
					that.actualh = 30;
					that.actualrh = 30 + that.config.defaultpt + that.config.defaultpb;
					that.actualmt = (that.items.isIE6 || data.pos == 'absolute') ? (that.items.winh - that.actualrh) / 2 + that.items.win.scrollTop() : -that.actualrh / 2;
					that.items.content.html('圖片加載失敗，請稍後重試');
					that._animatePop(data.callback);

				}
				this.items.img.src = data.img;
			}
		},
		_attachClose: function() {
			var that = this;
			this.items.close.click(function() {
				that.closePop();
				return false;
			})
			// this.items.wrap.click(function(e) {
			// 	if (e.target === this) {
			// 		that.closePop();
			// 	}
			// })
			if (this.config.esckey) {
				this.items.doc.on('keydown.' + this.config.nameSpace, function(e) {
					if (that.items.isVisible && e.keyCode == 27) {
						that.closePop();
					}
				})
			}
		},
		_purgePop: function() {
			this.items.isVisible = false;
			if (this.items.isIE6) {
				this._unbindResizeScrollFn()
			}
			this.items.wrap.hide();
			this.items.loader.css('visibility', 'visible');
			this.items.expand.style.visibility = 'hidden';
			this.items.expand.href = '';

			this.items.content.css('visibility', 'hidden');
			// this.items.content.hide();
			if (this.actualw != this.config.defaultw) {
				this.items.inner.css('width', this.config.defaultw);
			}

			if (this.actualh != this.config.defaulth) {
				this.items.inner.css({
					'height': this.config.defaulth,
					'marginTop': this.items.defaultmt
				});
			}
			var that = this;
			this.items.masklayer.fadeOut('fast');
		},
		closePop: function() {
			var that = this;

			if (this.items.img) {
				this.items.img.onload = null;
				this.items.img.onerror = null;
				this.items.img = null;
			} else if (this.items.iframe && this.items.iframe.src != '') {
				this.items.iframe.src = '';
			}
			this.items.content[0].innerHTML = '';

			if (this.items.isIE6) {
				setTimeout(function() {
					that._purgePop();
				}, 1)
			} else {
				this._purgePop();
			}
		},
		_enhanceResize: function(mt) {
			if (this.items.isIE6) {
				if (this.items.win.width() < 1000) {
					this.items.masklayer.width(1000);
					this.items.wrap.width(1000);
				} else {
					this.items.masklayer.width('100%');
					this.items.wrap.width('100%');
				}
				this.items.winh = this.items.win.height();
				this.items.layerh = Math.max(this.items.winh, this.items.doc.height());
				this.items.masklayer.height(this.items.layerh);
				this.items.wrap.height(this.items.layerh);
			} else {
				this.items.winh = this.items.win.height();
				this.items.wrap.height(Math.max(this.items.winh, this.items.doc.height()));
			}

			this.actualmt = (mt != undefined ? mt : (this.items.winh - this.actualrh) / 2) + this.items.win.scrollTop();
			var oactualmt = (mt != undefined ? mt : (this.items.winh - this.config.defaultrh) / 2) + this.items.win.scrollTop();

			this.items.inner.css({
				'marginTop': this.items.isIE6 && this.items.isVisible ? this.actualmt : oactualmt,
				'top': 0
			});
		},
		_bindResizeScrollFn: function(mt) {
			var that = this;
			this.items.win.on('resize.' + this.config.nameSpace, function() {
				if (that.resizeTimer) {
					clearTimeout(that.resizeTimer);
					that.resizeTimer = null;
				}
				that.resizeTimer = setTimeout(function() {
					that._enhanceResize();
					that.resizeTimer = null;
				}, 200)
			}).on('scroll.' + this.config.nameSpace, function() {
				if (that.scrollTimer) {
					clearTimeout(that.scrollTimer);
					that.scrollTimer = null;
				}
				that.scrollTimer = setTimeout(function() {
					that.actualmt = (mt != undefined ? mt : (that.items.winh - that.actualrh) / 2) + that.items.win.scrollTop();
					that.items.inner.css('marginTop', that.actualmt);
					that.scrollTimer = null;
				}, 200)

			})
		},
		_unbindResizeScrollFn: function() {
			this.items.win.off('resize.' + this.config.nameSpace).off('scroll.' + this.config.nameSpace);
		},
		_getBaseURL: function() {
			var baseurl = location.href,
				lastindex = baseurl.lastIndexOf('/');
			return baseurl.substring(0, lastindex + 1);
		}
	};
	return {
		getPopBox: function() {
			if (!storedInstance) storedInstance = new PopBox();
			return storedInstance;
		},
		attachBox: function(data) {
			this.getPopBox().showPop(data);
		}
	}
}();
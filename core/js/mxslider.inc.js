jQuery.fn.mxSlider = function () {
    var flgOn = 1;
    var slideNo = 0;
    var o = $(this);
    var params = arguments[0] || {};
    if (o != null && o != undefined) {
        var defaults = {vertical: false, type: "full", moveno: 1, auto: false, delay: 2000, time: 1000};
        var params = $.extend(defaults, params);
        o.each(function (eNo, element) {
            var slider = $(element);
            var w = slider.outerWidth();
            var h = slider.outerHeight();
            var wrap = slider.find("div.slider-wrap");
            var ul = wrap.find("ul:first");
            var li = ul.find("li.item");
            var moveH = w;
            var moveV = h;
            var totItm = li.length;
            if (li.length > 1) {
                if (params.vertical) {
                    var totSize = h * totItm;
                    if (params.type == "full") {
                        var totSize = h * totItm;
                    }
                    ul.css({'width': w + 'px', 'height': totSize + 'px'});
                } else {
                    var totSize = w * totItm;
                    ul.css({'width': totSize + 'px', 'height': h + 'px'});
                }

                wrap.css({'width': w + 'px', 'height': h + 'px'});
                if (params.type == "full") {
                    li.css({'width': w + 'px', 'height': h + 'px'});
                } else {
                    var moveH = li.outerWidth(true) * params.moveno;
                    var moveV = li.outerHeight(true) * params.moveno;
                    if (params.vertical) {
                        var totSize = moveV * totItm;
                    } else {
                        var totSize = moveH * totItm;
                    }
                }

                var index = 0;
                var timer = null;
                var moveDir = 0;
                slider.find("div.slider-nav a:first").addClass("active");
                slider.find('a.slider-prev').hide();
                slider.find('a.slider-next').off("click");
                slider.find('a.slider-next').click(function () {
                    if (flgOn) {
                        flgOn = 0;
                        var pos = ul.position();
                        if (params.vertical) {
                            var posTop = Math.abs(pos.top);
                            if (posTop >= 0 && totSize > (posTop + h)) {
                                index = (posTop + moveV) / moveV;
                                ul.stop().animate({top: -(posTop + moveV) + "px"}, params.time, function () {
                                    flgOn = 1;
                                });
                            } else {
                                flgOn = 1;
                            }
                        } else {
                            var posLeft = Math.abs(pos.left);
                            if (posLeft >= 0 && (totSize - moveH) > w) {
                                index = (posLeft + moveH) / moveH;
                                ul.stop().animate({left: -(posLeft + moveH) + "px"}, params.time, function () {
                                    flgOn = 1;
                                });
                                ul.find("li.item").removeClass("active"); // FOR HOT PROJECT 
                                ul.find("li.item:eq(" + index + ")").trigger("click").addClass("active"); // FOR HOT PROJECT 
                            } else {
                                flgOn = 1;
                            }

                        }

                        slider.find('a.slider-next').hide();
                        if (index > 0) {
                            slider.find("a.slider-prev").show();
                        }
                        if (index >= 0 && index < (totItm - 1)) {
                            slider.find("a.slider-next").show();
                        }

                        if (index >= 0 && slider.find("div.slider-nav a").length) {
                            slider.find("div.slider-nav a").removeClass("active");
                            slider.find("div.slider-nav a:eq(" + index + ")").addClass("active");
                        }
                    }
                    return false;
                });

                slider.find('a.slider-prev').off("click");
                slider.find('a.slider-prev').click(function () {
                    if (flgOn) {
                        flgOn = 0;
                        var pos = ul.position();
                        if (params.vertical) {
                            var posTop = Math.abs(pos.top);
                            if (posTop > 0 && (totSize - posTop) >= h) {
                                index = (posTop - moveV) / moveV;
                                ul.stop().animate({top: -(posTop - moveV) + "px"}, params.time, function () {
                                    flgOn = 1;
                                });
                            } else {
                                flgOn = 1;
                            }
                        } else {
                            var posLeft = Math.abs(pos.left);
                            if (posLeft >= moveH && (totSize - moveH) >= 0) {
                                index = (posLeft - moveH) / moveH;
                                ul.stop().animate({left: -(posLeft - moveH) + "px"}, params.time, function () {
                                    flgOn = 1;
                                });
                                ul.find("li.item").removeClass("active"); // FOR HOT PROJECT 
                                ul.find("li.item:eq(" + index + ")").trigger("click").addClass("active"); // FOR HOT PROJECT  
                            } else {
                                flgOn = 1;
                            }
                        }

                        slider.find('a.slider-prev').hide();
                        if (index > 0) {
                            slider.find("a.slider-prev").show();
                        }
                        if (index >= 0 && index < (totItm - 1)) {
                            slider.find("a.slider-next").show();
                        }

                        if (index >= 0 && slider.find("div.slider-nav a").length) {
                            slider.find("div.slider-nav a").removeClass("active");
                            slider.find("div.slider-nav a:eq(" + index + ")").addClass("active");
                        }
                    }
                    return false;
                });
            } else {
                slider.find("a.slider-next").hide();
                slider.find("a.slider-prev").hide();
            }
            if (params.auto == true) {
                timer = setInterval(function () {
                    if (index == 0) {
                        moveDir = 0;
                    } else if (index >= totItm - 1) {
                        moveDir = 1
                    }

                    if (moveDir) {
                        slider.find('a.slider-prev').trigger("click");
                    } else {
                        slider.find('a.slider-next').trigger("click");
                    }
                }, params.delay);
            }
        });
    }
};
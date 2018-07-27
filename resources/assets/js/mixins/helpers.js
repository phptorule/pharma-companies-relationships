const helpers = {

    data: function() {
        return {
            GOOGLE_CHART_DEFAULT_COLORS: ["#3366cc","#dc3912","#ff9900","#109618","#990099","#0099c6","#dd4477","#66aa00","#b82e2e","#316395","#994499","#22aa99","#aaaa11","#6633cc","#e67300","#8b0707","#651067","#329262","#5574a6","#3b3eac","#b77322","#16d620","#b91383","#f4359e","#9c5935","#a9c413","#2a778d","#668d1c","#bea413","#0c5922","#743411"],
        }
    },

    methods: {
        getUniqueArrayElements: function (arr) {
            return arr.filter((value, index, self) => {
                return self.indexOf(value) === index;
            });
        },

        scrollToElement: function(element, timeout = 2000, container = 'html, body') {
            setTimeout(()=>{
                $(container).animate({
                    scrollTop: $(element).offset().top
                }, 1000);
            },timeout);
        },

        showLocalLoader: function () {
            $('.loader-spinner').removeClass('hidden');
        },

        hideLocalLoader: function () {
            $('.loader-spinner').addClass('hidden');
        },

        checkIfShouldBeSlidedUp: function (selector, callback) {

            let element = $(selector);

            if(element.attr('style').replace(/\s/g, '').indexOf('display:none') === -1) {
                element.slideUp('slow', callback);
                return true;
            }
            return false;

        },

        isServiceKeyPressed: function (e) {
            return e.keyCode === 18 || e.keyCode === 27 || e.keyCode === 37||e.keyCode === 38|| e.keyCode === 39||e.keyCode === 40
        },

        enableBoxCollapsing: function () {
            (function ($) {
                'use strict';

                var DataKey = 'lte.boxwidget';

                var Default = {
                    animationSpeed : 500,
                    collapseTrigger: '[data-widget="collapse"]',
                    removeTrigger  : '[data-widget="remove"]',
                    collapseIcon   : 'fa-minus',
                    expandIcon     : 'fa-plus',
                    removeIcon     : 'fa-times'
                };

                var Selector = {
                    data     : '.box',
                    collapsed: '.collapsed-box',
                    header   : '.box-header',
                    body     : '.box-body',
                    footer   : '.box-footer',
                    tools    : '.box-tools'
                };

                var ClassName = {
                    collapsed: 'collapsed-box'
                };

                var Event = {
                    collapsed: 'collapsed.boxwidget',
                    expanded : 'expanded.boxwidget',
                    removed  : 'removed.boxwidget'
                };

                // BoxWidget Class Definition
                // =====================
                var BoxWidget = function (element, options) {
                    this.element = element;
                    this.options = options;

                    this._setUpListeners();
                };

                BoxWidget.prototype.toggle = function () {
                    var isOpen = !$(this.element).is(Selector.collapsed);

                    if (isOpen) {
                        this.collapse();
                    } else {
                        this.expand();
                    }
                };

                BoxWidget.prototype.expand = function () {
                    var expandedEvent = $.Event(Event.expanded);
                    var collapseIcon  = this.options.collapseIcon;
                    var expandIcon    = this.options.expandIcon;

                    $(this.element).removeClass(ClassName.collapsed);

                    $(this.element)
                        .children(Selector.header + ', ' + Selector.body + ', ' + Selector.footer)
                        .children(Selector.tools)
                        .find('.' + expandIcon)
                        .removeClass(expandIcon)
                        .addClass(collapseIcon);

                    $(this.element).children(Selector.body + ', ' + Selector.footer)
                        .slideDown(this.options.animationSpeed, function () {
                            $(this.element).trigger(expandedEvent);
                        }.bind(this));
                };

                BoxWidget.prototype.collapse = function () {
                    var collapsedEvent = $.Event(Event.collapsed);
                    var collapseIcon   = this.options.collapseIcon;
                    var expandIcon     = this.options.expandIcon;

                    $(this.element)
                        .children(Selector.header + ', ' + Selector.body + ', ' + Selector.footer)
                        .children(Selector.tools)
                        .find('.' + collapseIcon)
                        .removeClass(collapseIcon)
                        .addClass(expandIcon);

                    $(this.element).children(Selector.body + ', ' + Selector.footer)
                        .slideUp(this.options.animationSpeed, function () {
                            $(this.element).addClass(ClassName.collapsed);
                            $(this.element).trigger(collapsedEvent);
                        }.bind(this));
                };

                BoxWidget.prototype.remove = function () {
                    var removedEvent = $.Event(Event.removed);

                    $(this.element).slideUp(this.options.animationSpeed, function () {
                        $(this.element).trigger(removedEvent);
                        $(this.element).remove();
                    }.bind(this));
                };

                // Private

                BoxWidget.prototype._setUpListeners = function () {
                    var that = this;

                    $(this.element).on('click', this.options.collapseTrigger, function (event) {
                        if (event) event.preventDefault();
                        that.toggle($(this));
                        return false;
                    });

                    $(this.element).on('click', this.options.removeTrigger, function (event) {
                        if (event) event.preventDefault();
                        that.remove($(this));
                        return false;
                    });
                };

                // Plugin Definition
                // =================
                function Plugin(option) {
                    return this.each(function () {
                        var $this = $(this);
                        var data  = $this.data(DataKey);

                        if (!data) {
                            var options = $.extend({}, Default, $this.data(), typeof option == 'object' && option);
                            $this.data(DataKey, (data = new BoxWidget($this, options)));
                        }

                        if (typeof option == 'string') {
                            if (typeof data[option] == 'undefined') {
                                throw new Error('No method named ' + option);
                            }
                            data[option]();
                        }
                    });
                }

                var old = $.fn.boxWidget;

                $.fn.boxWidget             = Plugin;
                $.fn.boxWidget.Constructor = BoxWidget;

                // No Conflict Mode
                // ================
                $.fn.boxWidget.noConflict = function () {
                    $.fn.boxWidget = old;
                    return this;
                };

                $(Selector.data).each(function () {
                    Plugin.call($(this));
                });
            })(jQuery);
        }
    }

};

export default helpers;
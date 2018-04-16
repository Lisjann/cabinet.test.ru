var app = app || {};

(function($) {

    // a.App = {
    app.filterApp = {

        formDisabled: false,
        priceChangeTimeout: 2000,
        priceChangeTimer: null,

        init: function() {
            this.cacheElements();
            this.bindEvents();
        },

        toFloat: function(value, defaultVal) {
            var fValue = parseFloat(value);
            return isNaN(fValue)? defaultVal : fValue;
        },

        cacheElements: function() {
            this.$form          = $("#filter-form");
            this.$slider        = $("#filter-slider");
            this.$priceFrom     = $('#f_price_from', this.$form);
            this.$priceTo       = $('#f_price_to', this.$form);
            this.$filterDisk    = $('.disk-param', this.$form);
            this.$filterTire    = $('.tire-param', this.$form);
            this.$filterShared  = $('.shared-param', this.$form);
        },

        bindEvents: function() {
            var that = this;

            var min = that.toFloat(that.$slider.data('range-bottom'), 0);
            var max = that.toFloat(that.$slider.data('range-top'), 0);
            var limMin = that.toFloat(that.$slider.data('limit-bottom'), min);
            var limMax = that.toFloat(that.$slider.data('limit-top'), max);
            var priceFrom = that.toFloat(this.$priceFrom.val(), min);
            var priceTo = that.toFloat(this.$priceTo.val(), max);

            that.$slider.noUiSlider( 'init', {
                scale: [min, max],
                start: [priceFrom, priceTo],
                connect: false,
                limitBar: true,
                limit: [limMin, limMax],
                change: function( ) {
                    var values = $(this).noUiSlider( 'value' );

                    that.$priceFrom.val( values[0] );
                    that.$priceTo.val( values[1] );
                },
                end: function( event, ui ) {
                    $.proxy(that.submitFilter, that, that.$slider)();
                }
            });

            $.proxy(that.bindFormEvents, that)();
        },

        bindFormEvents: function() {
            var that = this;

            // что-то в фильтре поменяли
            that.$form.on('change', 'select[id!="f_type"]', function() {
                if( that.formDisabled ) {
                    return false;
                }
                var $this = $(this);

                $.proxy(that.submitFilter, that, $this)();
                return false;

            });

            // смена типа фильтра
            that.$form.on('change', '#f_type', function() {
                if( that.formDisabled ) {
                    return false;
                }
                var $this = $(this);

                var value = $this.val();
                if( value == 'disk' ) {
                    that.$filterDisk.show();
                    that.$filterTire.hide();
                }
                else if( value == 'tire' ) {
                    that.$filterDisk.hide();
                    that.$filterTire.show();
                }
                else {
                    that.$filterDisk.hide();
                    that.$filterTire.hide();
                }

                $.proxy(that.submitFilter, that, $this)();
                return false;

            });

            that.$priceFrom.change($.proxy(that.onPriceChange, that, that.$priceFrom));
            that.$priceTo.change($.proxy(that.onPriceChange, that, that.$priceTo));
        },

        onPriceChange: function(submitter) {
            var that = this;
            var min = parseFloat(that.$priceFrom.val());
            var max = parseFloat(that.$priceTo.val());

            that.$slider.noUiSlider('move',{ handle: 0, to: min });
            that.$slider.noUiSlider('move',{ handle: 1, to: max });

            /*if( that.priceChangeTimer ) {
                            clearTimeout(that.priceChangeTimer);
                        }
                        that.priceChangeTimer = setTimeout($.proxy(that.submitFilter, that), that.priceChangeTimeout);
                        */
            $.proxy(that.submitFilter, that, submitter)();
        },

        submitFilter: function(submitter) {
            var that = this;
            var data = $.proxy(that.getFilterData, that, submitter)();

            $.ajax({
                url: '/catalog/filter',
                type: 'POST',
                dataType: 'json',
                data: data,
                beforeSend: function() {
                    $.proxy(that.onBeforeSubmit, that)();
                }
            })
            .done(function(response) {
                if( response.result === undefined || response.result !== 'success' ) {
                    console.log('поломалось все');
                    return;
                }

                $.proxy(that.onSuccessSubmit, that, response, submitter)();
            });
        },

        getFilterData: function(submitter) {
            var that = this;
            var data = {};
            // собираем селекты
            $('select option:selected', that.$form).each(function() {
                var $this = $(this);
                var name = $this.parent().attr('name');
                //if( name !== submitterName ) {
                data[$this.parent().attr('name')] = $this.val();
                //}
            });
            // собираем input
            $('input[type="text"]', that.$form).each(function() {
                var $this = $(this);
                data[$this.attr('name')] = $this.val();
            });
            return data;

        },

        onBeforeSubmit: function() {
            var that = this;

            // отрубаем форму
            $('select,input[type="text"]', that.$form).each(function() {
                var $this = $(this);
                $this.attr('disabled', 'disabled');
            });
            // отрубаем индикатор силы
            //that.$slider.slider( "option", "disabled", true );
            that.$slider.noUiSlider('disable');

            that.formDisabled = true;
        },

        onSuccessSubmit: function(response, submitter) {
            var that = this;
            var filters = response.filters;
            var priceRange = response['price_range'];
            var submitterName = submitter ? submitter.attr('name') : '';

            for(var fName in filters) {

                var newFilterList = filters[fName];
                // get existed filter
                var $filter = $('select[name="f['+fName+']"]', that.$form);

                // текущее значение
                var filterValue = $filter.find(':selected').val() || "";

                $filter.find('option').remove();

                // default empty value
                $filter.append('<option value="">Любой</option>');

                // append new values
                for(var optValue in newFilterList) {
                    var $option = $('<option></option>')
                        .appendTo($filter)
                        .val(newFilterList[optValue])
                        .text(optValue)
                    ;
                    if(optValue == filterValue) {
                        $option.attr('selected', 'selected');
                    }
                    if( fName == 'type' ) {
                        optValue = optValue == 'tire' ? 'Шины' : optValue == 'disk' ? 'Диски' : optValue;
                        $option.text(optValue);
                    }
                }
            }

            // тупой слайдер кому ты нужен
            if( submitterName != 'f[price_from]' && submitterName != 'f[price_to]' && submitter != that.$slider ) {
                var sliderApi = that.$slider.noUiSlider('api');
                var min = that.toFloat(priceRange.cmin, sliderApi.options.scale[0]);
                var max = that.toFloat(priceRange.cmax, sliderApi.options.scale[1]);
                // включаем сублиматор энергии
                that.$slider.noUiSlider('limit', [min, max]);
            }

            that.$slider.noUiSlider('enable');

            // возвращаем форму к жизни
            var current_type = $('#f_type', that.$form).val();
            $('select', that.$filterShared).removeAttr('disabled');
            that.$priceFrom.removeAttr('disabled');
            that.$priceTo.removeAttr('disabled');

            if( current_type == 'disk' ) {
                $('select', that.$filterDisk).removeAttr('disabled');
            }
            else if( current_type == 'tire' ) {
                $('select', that.$filterTire).removeAttr('disabled');
            }

            //$.proxy(that.bindFormEvents, that)();
            that.formDisabled = false;
        }
    };

    $(function() {

        app.filterApp.init();

    });

})(jQuery);
$(function () {
    'use strict'

    var activeId = $('.sidebar-menu').data('active');
    if (activeId) {
        var activeLi = $('#' + activeId);
        activeLi.addClass('active');
        var parentLi = activeLi.closest('li.treeview');
        if (parentLi.length) {
            parentLi.addClass('active menu-open');
        }
    }

    $("#home-more").click(function() {
        $('html, body').animate({
            scrollTop: $("#greatings").offset().top - 70
        }, 1500);
    });

    var message = new Message();
    new ProfileUserForm(message);
    new SelectAvatar(message);
    new TrackerGraph();

    if ($('#login-form').length) new LoginForm(message);
    if ($('#registration-form').length) new RegistrationForm(message);

    //Date picker
    if ($('.js-datepicker').length){
        $('.js-datepicker').datepicker({
            autoclose: true,
            format: "dd.mm.yyyy"
        })
    }
    // $('#demo').datetimepicker({
    //     inline:true,
    // });

});

/**
 * Управляет всплывающими сообщениями пользователей
 * @constructor
 */
Message = function() {
    var classMessage = '';

    this.infoMessage = function(message) {
        classMessage = 'alert-info';
        showMessage(message, classMessage);
    };

    this.errorMessage = function(message) {
        classMessage = 'alert-error';
        showMessage(message, classMessage);
    };

    this.successMessage = function(message) {
        classMessage = 'alert-success';
        showMessage(message, classMessage);
    };

    var showMessage = function(message, classMessage) {
        if (message) {
            removeFlash();
            $('body').append("<div class='flash-message alert " + classMessage + "'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>" + message + "</div>");
        }
    };

    var removeFlash = function() {
        var oldMessage = $('.flash-message');
        if (oldMessage.length) {
            oldMessage.remove();
        }
    }
};

TrackerGraph = function() {

    var that = this;

    var init = function() {
        that.form = $('#graph-range');
        if (that.form.length) {
            that.rangeStop = that.form.find('.js-graph-stop').one();
            addEvents();
        }
    };

    var addEvents = function() {
        that.rangeStop.on('change', function() {
            that.form.submit();
        });
    };

    init();
};

LoginForm = function(message) {

    var init = function() {
        this.form = $('#login-form');
        if (this.form.length) {
            this.message = message;
            addEvents();
        }
    };

    var addEvents = function() {
        var self = this;
        this.form.on('submit', function() {
            var data = self.form.serialize();
            $.post(self.form.attr('action'), data, function(res) {
                if (res.authenticated) {
                    window.location = res.url;
                } else {
                    self.message.errorMessage(res.error);
                }
            });
            return false;
        });
    };

    init(message);
};

RegistrationForm = function(message) {

    var init = function() {
        this.form = $('#registration-form');
        if (this.form.length) {
            this.message = message;
            addEvents();
        }
    };

    var addEvents = function() {
        var self = this;
        this.form.on('submit', function() {
            var data = self.form.serialize();
            $.post(self.form.attr('action'), data, function(res) {
                if (res.success) {
                    window.location = res.data.url;
                } else {
                    self.message.errorMessage(res.data.message);
                }
            });
            return false;
        });
    };

    init(message);
};

SelectAvatar = function(message) {

    var messenger;

    var init = function() {
        addEvents();
        messenger = message;
    };

    var addEvents = function() {
        $('.avatar-items .js-avatar').on('click', function() {
            var avatarItems = $(this).closest('.avatar-items');
            var avatarItem = $(this).closest('.avatar-item');
            var src = avatarItem.find('img').attr('src');
            $.ajax({
                type: 'get',
                data : {src: src}
            }).done(function(response){
                if (response) {
                    if (response.success) {
                        messenger.successMessage(response.data.message);
                        avatarItems.find('.avatar-item').removeClass('current');
                        avatarItem.addClass('current')
                    }
                }
            });
        })
    };

    init(message);
}

/**
 * Управляет формой изменения полей пользователя /profile/user
 * @param message
 * @constructor
 */
ProfileUserForm = function(message) {

    var messenger;

    var init = function() {
        addEvents();
        messenger = message
    };

    var addEvents = function() {
        $("#user_edit").submit(function(event){
            event.preventDefault(); //prevent default action
            var post_url = $(this).attr("action"); //get form action url
            var request_method = $(this).attr("method"); //get form GET/POST method
            var form_data = $(this).serialize(); //Encode form elements for submission
            $("#user_edit").find('.form-group').removeClass('has-error');
            
            $.ajax({
                url : post_url,
                type: request_method,
                data : form_data
            }).done(function(response){
                if (response) {
                    if (!response.success) {
                        if (response.data.field === 'newPassword') {
                            $('#form_' + response.data.field).closest('.form-group').addClass('has-error');
                            $('#form_oldPassword').closest('.form-group').addClass('has-error');
                            $('#form_repeatPassword').closest('.form-group').addClass('has-error');
                        } else {
                            $('#form_' + response.data.field).closest('.form-group').addClass('has-error');
                        }
                        messenger.errorMessage(response.data.message);
                    } else {
                        messenger.successMessage(response.data.message);
                    }
                }
                
            });

            return false;
        });
    }

    init(message);
};














$(function () {

    var jsonPriceEl = $('#jsonPrice');
    if (jsonPriceEl.length) {
        var jsonPrice = jsonPriceEl.val();
        jsonPrice = JSON.parse(jsonPrice);
        //console.log(jsonPrice);
        if (jsonPrice && jsonPrice.data && jsonPrice.labels) {

            $.plot('#line-chart', [{
                data: jsonPrice.data
            }], {
                grid: {
                    hoverable: true,
                    borderColor: '#f3f3f3',
                    borderWidth: 1,
                    tickColor: '#f3f3f3'
                },
                series: {
                    shadowSize: 0,
                    lines: {
                        show: true
                    },
                    points: {
                        show: true
                    }
                },
                xaxis: {ticks: jsonPrice.labels}
            })
        } else {
            $('#line-chart').html('<div style="text-align:center">' + $('#line-chart').data('notfound') + '</div>');
            //$('#line-chart').innerHTML = '<div style="text-align:center">' + $('#line-chart').data('notfound') + '</div>';
        }

        //Initialize tooltip on hover
        $('<div class="tooltip-inner" id="line-chart-tooltip"></div>').css({
            position: 'absolute',
            marginLeft: '-50px',
            marginTop: '10px',
            display: 'none',
            fontWeight: 'bold',
            opacity: 0.8,
            backgroundColor: '#fff',
            color: '#7b7b7b',
            border: '1px solid #ccc',
            letterSpacing: '0.05em'
        }).appendTo('body');

        $('#line-chart').bind('plothover', function (event, pos, item) {
            if (item) {
                var x = item.datapoint[0].toFixed(2),
                    price = item.datapoint[1].toFixed(2)

                var diff = 0;
                if (item.dataIndex - 1 >= 0 && item.series.data[item.dataIndex - 1].length) {
                    var prevPrice = item.series.data[item.dataIndex - 1][1];
                    diff = (price - prevPrice).toFixed(2);
                }

                price = 'Price: <span class="text-yellow">' + price + '</span>';
                if (diff != 0) {
                    var colorClass = 'text-green';
                    var sign = '';
                    if (diff > 0) {
                        colorClass = 'text-red';
                        sign = '+';
                    }
                    price += " (<span class='" + colorClass + "'>" + sign + diff + "</span> )";
                }

                $('#line-chart-tooltip').html(price)
                    .css({top: item.pageY + 5, left: item.pageX + 5})
                    .fadeIn(200)
            } else {
                $('#line-chart-tooltip').hide()
            }

        })
    }



    // function euroFormatter(v, axis) {
    //     console.log('-------');
    //     console.log(v);
    //     console.log('++++++++++');
    //     console.log(axis);
    //     return v.toFixed(axis.tickDecimals) + "€";
    // }
    // $.plot('#line-chart', [data], {
    //     grid  : {
    //         hoverable  : true,
    //         borderColor: '#f3f3f3',
    //         borderWidth: 1,
    //         tickColor  : '#f3f3f3'
    //     },
    //     series: {
    //         shadowSize: 0,
    //         lines     : {
    //             show: true
    //         },
    //         points    : {
    //             show: true
    //         }
    //     },
    //     lines : {
    //         fill : false,
    //         color: ['#3c8dbc', '#f56954']
    //     },
    //     yaxis : {
    //         show: true,
    //         tickFormatter: euroFormatter
    //     },
    //     xaxis : {
    //         tickFormatter: euroFormatter
    //     }
    // })

    // var $hist_data = [
    //     [20.03, 202],
    //     [21.03, 210],
    //     [22.03, 250],
    //     [23.03, 240],
    //     [24.03, 230],
    //     [25.03, 220]
    // ];
    //
    // var $hist_ticks = [
    //     [20.03, "20.03"],
    //     [21.03, "21.03"],
    //     [22.03, "22.03"],
    //     [23.03, "23.03"],
    //     [24.03, "24.03"],
    //     [25.03, "25.03"]
    // ];
    //
    // $.plot('#line-chart', [{
    //     data: $hist_data
    // }], {
    //     xaxis: { ticks: $hist_ticks}
    // })


    /* END LINE CHART */


})

$( document ).ready(function() {
    scaleVideoContainer();

    // initBannerVideoSize('.video-container .poster img');
    // initBannerVideoSize('.video-container .filter');
    initBannerVideoSize('.video-container video');

    $(window).on('resize', function() {
        scaleVideoContainer();
        // scaleBannerVideoSize('.video-container .poster img');
        // scaleBannerVideoSize('.video-container .filter');
        scaleBannerVideoSize('.video-container video');
    });
});

function scaleVideoContainer() {

    var height = $(window).height() + 5 - 40;
    var unitHeight = parseInt(height) + 'px';
    $('.homepage-hero-module').css('height',unitHeight);

}

function initBannerVideoSize(element){

    $(element).each(function(){
        $(this).data('height', $(this).height());
        $(this).data('width', $(this).width());
    });

    scaleBannerVideoSize(element);

}

function scaleBannerVideoSize(element){
    $(element).each(function(){
        var needWidth = $(window).height() / 0.583;

        if ($(window).width() > needWidth) {
            needWidth = windowWidth;
        }

        $(this).width(needWidth);

        $('.homepage-hero-module .video-container video').addClass('fadeIn animated');

    });
}
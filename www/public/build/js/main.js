$(function () {
  'use strict'

  var activeId = $('.sidebar-menu').data('active');
  if (activeId) {
    $('#' + activeId).addClass('active');
    var parentLi = $('#' + activeId).closest('li.treeview');
    if (parentLi.length) {
      parentLi.addClass('active menu-open');
    }
  }

  var message = new Message();
  new ProfileUserForm(message);
  new SelectAvatar(message);

});

/**
 * Управляет всплывающими сообщениями пользователей
 * @constructor
 */
Message = function() {
    var classMessage = '';

    this.infoMessage = function(message) {
        classMessage = 'flash-info';
        showMessage(message, classMessage);
    }

    this.errorMessage = function(message) {
        classMessage = 'flash-error';
        showMessage(message, classMessage);
    }

    this.successMessage = function(message) {
        classMessage = 'flash-success';
        showMessage(message, classMessage);
    }

    var showMessage = function(message, classMessage) {
        if (message) {
            removeFlash();
            $('body').append("<div class='flash-message " + classMessage + "'>" + message + "</div>");
        }
    }

    var removeFlash = function() {
        var oldMessage = $('.flash-message');
        if (oldMessage.length) {
            oldMessage.remove();
        }
    }
}

SelectAvatar = function(message) {

    var messenger;

    var init = function() {
        addEvents();
        messenger = message;
    }

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
    }

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

    var jsonPrice = $('#jsonPrice').val();
    jsonPrice = JSON.parse(jsonPrice);
    if (jsonPrice && jsonPrice.data && jsonPrice.labels) {

        $.plot('#line-chart', [{
            data: jsonPrice.data
        }], {
            grid  : {
                hoverable  : true,
                borderColor: '#f3f3f3',
                borderWidth: 1,
                tickColor  : '#f3f3f3'
            },
            series: {
                shadowSize: 0,
                lines     : {
                    show: true
                },
                points    : {
                    show: true
                }
            },
            xaxis: { ticks: jsonPrice.labels}
        })
    }

    //Initialize tooltip on hover
    $('<div class="tooltip-inner" id="line-chart-tooltip"></div>').css({
        position: 'absolute',
        display : 'none',
        opacity : 0.8
    }).appendTo('body')
    $('#line-chart').bind('plothover', function (event, pos, item) {
        if (item) {
            var x = item.datapoint[0].toFixed(2),
                price = item.datapoint[1].toFixed(2)

            $('#line-chart-tooltip').html(price)
                .css({ top: item.pageY + 5, left: item.pageX + 5 })
                .fadeIn(200)
        } else {
            $('#line-chart-tooltip').hide()
        }

    })



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
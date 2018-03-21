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
                    console.log(response);
                    if (response.errors.length) {
                        $('#form_' + response.errors[0].key).closest('.form-group').addClass('has-error');
                        messenger.errorMessage(response.errors[0].value);
                    }
                    
                    if (response.success.length) {
                        messenger.successMessage(response.success[0].value);
                    }
                }
                
            });

            return false;
        });
    }

    init(message);
};

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
})

jQuery(window).ready(function () {

  function renderIntervals() {
    $.getJSON('api.php', function (data) {
      var html = [];
      $(data).each(function (i) {
        html.push('<tr>');
        html.push('<td>'+ this.date_start + '</td>');
        html.push('<td>'+ this.date_end + '</td>');
        html.push('<td>'+ this.price + '</td>');
        html.push('<td><a href="#" data-toggle="modal" data-m="edit" data-target="#form-modal" data-price="'+ this.price +'"  data-date-start="'+ this.date_start +'" data-date-end="'+ this.date_end +'" data-interval-id="'+ this.interval_id +'">Edit</a> ');
        html.push('<a class="delete-interval" href="#" data-interval-id="'+ this.interval_id +'">Delete</a> </td>');
        html.push('</tr>');
      });
      $('#intervals-table tbody').html( html.join('') );
    });
  }

  renderIntervals();

  function sendRequest(requestType, data) {
    $.ajax({
      url: 'api.php',
      type: requestType,
      data: data,
      success: function(result) {
        $('#form-modal').modal('hide');
        renderIntervals(); // reloading intervals
      }
    });
  }

  $(document).on('click', '.delete-interval', function() {
    if (confirm('Are you sure to delete the selected interval?')) {
      var intervalId = $(this).data('interval-id');
      sendRequest('DELETE', { interval_id: intervalId});
    }
  });

  $('#delete-all-interval').on('click', function () {
    if (confirm('Are you sure to delete ALL the records in the database?')) {
      var intervalId = $(this).data('interval-id');
      sendRequest('DELETE', { m: 'delete'});
    }
  });

  $('#form-modal').on('show.bs.modal', function (event) {
    var $modal = $(this);
    var $editLink = $(event.relatedTarget);

    $modal.find('form')[0].reset();

    if ($editLink.data('m') === 'edit') {
      $('input#date_start').val($editLink.data('date-start'));
      $('input#date_end').val($editLink.data('date-end'));
      $('input#price').val($editLink.data('price'));
    }
    $('input#interval_id').val($editLink.data('interval-id'));
    $('input#m').val($editLink.data('m'));
  });

  $('form').submit(function (e) {
    e.preventDefault();
    var $form = $(this),
      edit = ($('input#m').val() === 'edit'),
      requestType = (edit) ? 'PUT' : 'POST';

    sendRequest(requestType, $form.serialize());
  });

});
<!doctype html>
<html lang="en">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

  <title>Date intervals!</title>
</head>
<body>

<div class="container">
  <div class="row">
    <div class="col">
      <h1>Date Intervals</h1>
      <button id="add-interval" type="button" class="btn btn-primary" data-toggle="modal" data-m="add" data-interval-id="" data-target="#form-modal">Add</button>
      <button id="delete-all-interval" type="button" class="btn btn-danger" >Delete ALL!!!!!</button>
      <br>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <table id="intervals-table" class="table" style="margin-top: 10px">
        <thead class="thead-dark">
        <tr>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Price</th>
          <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!--Modal form-->

<div class="modal fade" id="form-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Interval!</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="post">
        <div class="modal-body">
            <div class="form-group">
              <label for="date_start" class="col-form-label">Start date:</label>
              <input type="date" class="form-control" id="date_start" name="date_start" placeholder="YYYY-mm-dd" required>
            </div>
            <div class="form-group">
              <label for="date_end" class="col-form-label">End date:</label>
              <input type="date" class="form-control" id="date_end" name="date_end" placeholder="YYYY-mm-dd" required>
            </div>
            <div class="form-group">
              <label for="price" class="col-form-label">Price:</label>
              <input type="number" class="form-control" id="price" name="price" placeholder="123.00" required>
            </div>
            <input type="hidden" id="interval_id" name="interval_id" value="">
            <input type="hidden" id="m" name="m" value="">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Send message</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script
  src="https://code.jquery.com/jquery-3.4.0.min.js"
  integrity="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg="
  crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script src="js/script.js"></script>
</body>
</html>
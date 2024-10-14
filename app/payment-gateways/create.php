<?php
require '../../includes/db-config.php';
session_start();
?>
<div class="modal-header clearfix text-left">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
  </button>
  <h5>Add <span class="semi-bold">Payment Gateway</span></h5>
</div>
<form id="payment_gateway_form" method="POST" action="/app/payment-gateways/store">
  <div class="modal-body">
    <div class="row">
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>University</label>
          <select class="full-width" style="border: transparent;" name="university_id" required>
            <option value="">Choose</option>
            <?php

            $uninversities = $conn->query("SELECT ID, CONCAT(Name, ' (', Vertical, ')') as Name FROM Universities WHERE ID NOT IN (SELECT University_ID FROM Payment_Gateways)");
            while ($uninversity = $uninversities->fetch_assoc()) { ?>
              <option value="<?= $uninversity['ID'] ?>"><?= $uninversity['Name'] ?></option>
            <?php } ?>
          </select>
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Gateway</label>
          <select class="full-width" style="border: transparent;" name="gateway_type" required>
            <option value="">Choose</option>
            <option value="1">Easebuzz</option>
            <option value="2">Razor Pay</option>
          </select>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-6">
        <div class="form-group form-group-default required">
          <label>Access Key</label>
          <input type="text" class="form-control" autocomplete="off" name="access_key" placeholder="" required />
        </div>
      </div>
      <div class="col-lg-6">
        <div class="form-group form-group-default required">
          <label>Salt/Secret Key</label>
          <input type="text" class="form-control" autocomplete="off" name="secret_key" placeholder="" required />
        </div>
      </div>
    </div>
  </div>
  <div class="modal-footer clearfix text-end">
    <div class="col-md-4 m-t-10 sm-m-t-10">
      <button aria-label="" type="submit" class="btn btn-primary btn-cons btn-animated from-left">
        <span>Save</span>
        <span class="hidden-block">
          <i class="pg-icon">tick</i>
        </span>
      </button>
    </div>
  </div>
</form>

<script>
  $(function() {
    // Form
    $("#payment_gateway_form").validate();

    $("#payment_gateway_form").on("submit", function(e) {
      e.preventDefault();
      if ($("#payment_gateway_form").valid()) {
        var formData = new FormData(this);
        $.ajax({
          url: this.action,
          type: 'post',
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          dataType: 'json',
          success: function(data) {
            if (data.status == 200) {
              $('.modal').modal('hide');
              notification('success', data.message);
              $('#payment-gateway-table').DataTable().ajax.reload(null, false);
            } else {
              notification('danger', data.message);
            }
          }
        });
      }
    });
  });
</script>

<!-- Modal -->
<div class="modal-header clearfix text-left">
  <h5>Add <span class="semi-bold"></span>Sub-Center</h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<form role="form" id="form-add-sub-centers" action="/app/sub-centers/store" method="POST" enctype="multipart/form-data">
  <div class="modal-body">

    <div class="row">
      <div class="col-md-12">
        <div class="form-group form-group-default required">
          <label>Center</label>
          <select class="form-control" name="reporting">
            <?php 
              require '../../includes/db-config.php';
              session_start();
              print $_SESSION['Role']!='Center' ? '<option value="">Choose</option>' : '';
              
              $center_query = "";
              if($_SESSION['Role']=='University Head'){
                $center_query = " AND Alloted_Center_To_Counsellor.University_ID = ".$_SESSION['university_id'];
              }elseif($_SESSION['Role']=='Center'){
                $center_query = " AND Users.ID = ".$_SESSION['ID'];
              }
              $centers = $conn->query("SELECT Users.ID, CONCAT(Users.Name, ' (', Users.Code, ')') as Name FROM Users LEFT JOIN Alloted_Center_To_Counsellor ON Users.ID = Alloted_Center_To_Counsellor.Code WHERE Role = 'Center' AND Users.CanCreateSubCenter = 1 $center_query GROUP BY Users.ID");
              while($center = $centers->fetch_assoc()) { ?>
                <option value="<?=$center['ID']?>"><?=$center['Name']?></option>
            <?php } ?>
          </select>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Name</label>
          <input type="text" name="name" class="form-control" placeholder="ex: Jhon Doe" required>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Email</label>
          <input type="text" name="email" class="form-control" placeholder="ex: demo@gmail.com" required>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Mobile</label>
          <input type="tel" name="mobile" class="form-control" maxlength="10" placeholder="ex: 9998777655" onkeypress="return isNumberKey(event)" required>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Photo</label>
          <input type="file" name="image" class="form-control" accept="image/png, image/jpg, image/jpeg, image/svg">
        </div>
      </div>
    </div>
  </div>
  <div class="modal-footer clearfix text-end">
    <div class="col-md-4 m-t-10 sm-m-t-10">
      <button aria-label="" type="submit" class="btn btn-primary btn-cons btn-animated from-left">
        <i class="ti-save mr-2"></i> Save</button>
      </button>
    </div>
  </div>
</form>

<script>
  $(function(){
    $('#form-add-sub-centers').validate({
      rules: {
        name: {required:true},
      },
      highlight: function (element) {
        $(element).addClass('error');
        $(element).closest('.form-control').addClass('has-error');
      },
      unhighlight: function (element) {
        $(element).removeClass('error');
        $(element).closest('.form-control').removeClass('has-error');
      }
    });
  })

  $("#form-add-sub-centers").on("submit", function(e){
    if($('#form-add-sub-centers').valid()){
      $(':input[type="submit"]').prop('disabled', true);
      var formData = new FormData(this);
      $.ajax({
          url: this.action,
          type: 'post',
          data: formData,
          cache:false,
          contentType: false,
          processData: false,
          dataType: "json",
          success: function(data) {
              if(data.status==200){
                $('.modal').modal('hide');
                notification('success', data.message);
                $('#users-table').DataTable().ajax.reload(null, false);
              }else{
                $(':input[type="submit"]').prop('disabled', false);
                notification('danger', data.message);
              }
          }
      });
      e.preventDefault();
    }
  });
</script>

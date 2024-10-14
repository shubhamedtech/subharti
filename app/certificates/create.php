
<?php 
require '../../includes/db-config.php';
session_start();
//if($_SESSION['university_id'] == 48){}
?>

<!-- Modal -->
<div class="modal-header clearfix text-left">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
  </button>
  <h5>Create<span class="semi-bold"> Certificate</span></h5>
</div>

  <form role="form" id="form-add-e-book" action="#" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
    
    <!-- <div class="row">
    <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Program Type</label>
          <select class="full-width" style="border: transparent;" id="course_type_id" name="course_type_id" onchange="getSubjects(this.value);">
            <option value="">Select</option>
            <?php
                $programs = $conn->query("SELECT ID,Name,Short_Name FROM Courses");
                while ($program = $programs->fetch_assoc()) { ?>
                <option value="<?=$program['ID']?>">
                    <?=$program['Name'].' ('.$program['Short_Name'].')'?>
                </option>
            <?php } ?>
          </select>
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Specialization/Course</label>
          <select class="full-width" style="border: transparent;" id="course_id" name="course_id" onchange="getSubjects(this.value);">
            <option value="">Select</option>
            <?php
                $programs = $conn->query("SELECT ID,Name,Short_Name FROM Sub_Courses");
                while ($program = $programs->fetch_assoc()) { ?>
                <option value="<?=$program['ID']?>">
                    <?=$program['Name'].' ('.$program['Short_Name'].')'?>
                </option>
            <?php } ?>
          </select>
        </div>
      </div>

    </div> -->

    <div class="row">
        <div class="col-md-12">
            <div class="form-group form-group-default required">
            <label>Student</label>
            <select class="full-width" style="border: transparent;" id="student_id" name="student_id">
                <option value="">Select</option>
                <?php
                $programs = $conn->query("SELECT * FROM Students  where University_ID=48");
                while ($program = $programs->fetch_assoc()) { ?>
                <option value="<?=$program['ID']?>">
                    <?=$program['First_Name'].$program['Middle_Name'].$program['Last_Name'].' ('.$program['Unique_ID'].')'?>
                </option>
            <?php } ?>
            </select>
            </div>
        </div>
    </div>

  </div>


  <div class="modal-footer clearfix justify-content-center">
    <div class="col-md-4 m-t-10 sm-m-t-10">
    
    <a class="btn btn-primary btn-cons btn-animated from-left" href="#" onClick="view();">Save</a>
      <!-- <button aria-label="" type="submit" class="btn btn-primary btn-cons btn-animated from-left">
        <span>Save</span> 
         <span class="hidden-block">
          <i class="pg-icon">tick</i>
        </span> -->
      </button>
    </div>
  </div>
</form>


<script>
    function view(){
        
        var student_id = $('#student_id').val();
        if(student_id==undefined){
            notification('danger', 'Please select student to proceed!');
            return false;
        }else{

            var request = $.ajax({
                url: "/app/certificates/certificate-view",
                type: "POST",
                data: {student_id : student_id},
                dataType: "json",
                success: function(data) {
                    if(data.status == 200) {
                        notification('success', data.message);
                        $('.modal').modal('hide');
                    } else {
                        notification('danger', data.message);
                    }

                    $('#e_books-table').DataTable().ajax.reload(null, false);
                    
                },
                error: function(data) {
                    notification('danger', 'Server is not responding. Please try again later');
                }
                });

          // window.location.assign("/app/certificates/certificate-view?student_id="+student_id);
        }

    }


</script>

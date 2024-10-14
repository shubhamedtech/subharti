<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>
<!-- START PAGE-CONTAINER -->
<div class="page-container ">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
  <!-- START PAGE CONTENT WRAPPER -->
  <div class="page-content-wrapper ">
    <!-- START PAGE CONTENT -->
    <div class="content ">
      <!-- START JUMBOTRON -->
      <div class="jumbotron" data-pages="parallax">
        <div class=" container-fluid sm-p-l-0 sm-p-r-0">
          <div class="inner">
            <!-- START BREADCRUMB -->
            <ol class="breadcrumb d-flex flex-wrap justify-content-between align-self-start">
              <?php $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
              for ($i = 1; $i <= count($breadcrumbs); $i++) {
                if (count($breadcrumbs) == $i):
                  $active = "active";
                  $crumb = explode("?", $breadcrumbs[$i]);
                  echo '<li class="breadcrumb-item ' . $active . '">' . $crumb[0] . '</li>';
                endif;
              }
              ?>
              <div>
                <button class="btn btn-link" aria-label="" title="" data-toggle="tooltip" data-original-title="Upload"
                  onclick="add('subjects', 'lg')"> <i class="uil uil-export"></i></button>
              </div>
            </ol>
            <!-- END BREADCRUMB -->
          </div>
        </div>
      </div>
      <!-- END JUMBOTRON -->
      <!-- START CONTAINER FLUID -->
      <div class=" container-fluid">
        <!-- BEGIN PlACE PAGE CONTENT HERE -->
        <div class="row">

          <div class="col-md-6">
            <div class="form-group form-group-default required">
              <label>Course</label>

              <?php if ($_SESSION['university_id'] == '48') { ?>

                <select class="full-width" style="border: transparent;" id="course" onchange="getCourseCategory();">

                <?php } else { ?>

                  <select class="full-width" style="border: transparent;" id="course"
                    onchange="getSemester(this.value); removeTable()">

                  <?php } ?>

                  <option value="">Choose</option>
                  <?php
                  $condition = "";
                  if (in_array($_SESSION['Role'], ['Center', 'Sub-Center'])) {
                    $ids = array();
                    $sub_course_ids = $conn->query("SELECT Sub_Course_ID FROM Center_Sub_Courses WHERE `User_ID` = " . $_SESSION['ID'] . "");
                    while ($sub_course_id = $sub_course_ids->fetch_assoc()) {
                      $ids[] = $sub_course_id['Sub_Course_ID'];
                    }
                    $condition = " AND Sub_Courses.ID IN (" . implode(",", $ids) . ")";
                  }
                  $sub_courses = $conn->query("SELECT CONCAT(Courses.Short_Name, ' (', Sub_Courses.Name, ')') AS Sub_Course, Sub_Courses.ID FROM Sub_Courses LEFT JOIN Courses ON Sub_Courses.Course_ID = Courses.ID WHERE Sub_Courses.University_ID = " . $_SESSION['university_id'] . " $condition ORDER BY Sub_Courses.Name ASC");
                  while ($sub_course = $sub_courses->fetch_assoc()) {
                    echo '<option value="' . $sub_course['ID'] . '">' . $sub_course['Sub_Course'] . '</option>';
                  }
                  ?>
                </select>
            </div>
          </div>
          <?php if ($_SESSION['university_id'] == '47') { ?>
            <div class="col-md-6">
              <div class="form-group form-group-default required">
                <label>Semester</label>
                <select class="full-width" style="border: transparent;" id="semester" onchange="getTable()">
                  <option value="">Choose</option>
                </select>
              </div>
            </div>
          <?php } else { ?>
            <!-- Course Category -->
            <div class="col-md-3">
              <div class="form-group form-group-default required">
                <label>Course Category</label>
                <select class="full-width" style="border: transparent;" data-init-plugin="select2" id="course_category"
                  name="course_category" onchange="getDuration()">

                </select>
              </div>
            </div>
            <!-- Admission Type -->
            <div class="col-md-3">
              <div class="form-group form-group-default required">
                <label id="mode">Mode</label>
                <select class="full-width" style="border: transparent;" name="duration" id="duration" onchange="getTableData(this.value)" >
                </select>
              </div>
            </div>
          <?php } ?>
        </div>

        <div class="row" id="subjects"></div>
        <!-- END PLACE PAGE CONTENT HERE -->
      </div>
      <!-- END CONTAINER FLUID -->
    </div>
    <!-- END PAGE CONTENT -->
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>

    <script type="text/javascript">
      function getSemester(id) {
        $.ajax({
          url: '/app/subjects/semester?id=' + id,
          type: 'GET',
          success: function (data) {
            $("#semester").html(data);
          }
        })
      }
    </script>

    <script type="text/javascript">
      function getTable() {
        var course_id = $('#course').val();
        var semester = $('#semester').val();
        if (course_id.length > 0 && semester.length > 0) {
          $.ajax({
            url: '/app/subjects/syllabus?course_id=' + course_id + '&semester=' + semester,
            type: 'GET',
            success: function (data) {
              $('#subjects').html(data);
            }
          })
        } else {
          $.ajax({
            url: '/app/subjects/syllabus',
            type: 'GET',
            success: function (data) {
              $('#subjects').html(data);
            }
          })
        }
      }
      getTable()
      function removeTable() {
        $('#subjects').html('');
      }
    </script>

    <script type="text/javascript">
      function uploadFile(table, column, id) {
        $.ajax({
          url: '/app/upload/create?id=' + id + '&column=' + column + '&table=' + table,
          type: 'GET',
          success: function (data) {
            $("#md-modal-content").html(data);
            $("#mdmodal").modal('show');
          }
        })
      }
    </script>
    <script  type="text/javascript">
      function getCourseCategory(id){
        const skll_subcourse_id = $('#course').val();
         $.ajax({
          url: '/app/application-form/course-category?id=' + skll_subcourse_id,
          type: 'GET',
          success: function(data) {
            $('#course_category').html(data);
            $('#course_category').val(<?php print !empty($id) ? $student['Course_Category'] : '' ?>)
          }
        });
      }

      function getDuration() {
        const sub_course_ids = $('#course').val();
        // const admission_type_ids = $('#admission_type').val();
        const course_categorys = $('#course_category').val();
        $.ajax({
          url: '/app/application-form/duration?sub_course_id=' + sub_course_ids + '&course_category=' + course_categorys+'&univercity_type_filter=skill',
          type: 'GET',
          success: function(data) {
        // console.log(data);return false;
            $('#duration').html(data);
            $('#duration').val(<?php print !empty($id) ? $student['Duration'] : '' ?>)
            var duration = $('#duration').val()
            getTableData(duration);
            
          }
        })
   
        
      }
    
      if('<?php echo $_SESSION['university_id']; ?>' === '48') {
     function getTableData(duration) {

      var course_id = $('#course').val();
          $.ajax({
            url: '/app/subjects/syllabus?duration=' + duration+'&course_id=' + course_id,
            type: 'GET',
            success: function (data) {
              $('#subjects').html(data);
            }
          })
        
      }
      getTableData()
      function removeTable() {
        $('#subjects').html('');
      }
      }  
</script>

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>
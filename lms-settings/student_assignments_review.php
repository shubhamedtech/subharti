<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>
<div class="page-container ">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
  <div class="page-content-wrapper ">
    <div class="content">
      <div class="jumbotron" data-pages="parallax">
        <div class=" container-fluid sm-p-l-0 sm-p-r-0">
        </div>
      </div>
      <div class=" container-fluid">
        <div class="card card-transparent">
          <div class="row" id="assignments"></div>
          <div class="card-header">
            <div class="pull-right">
              <div class="row">
                <div class="col-xs-7" style="margin-right: 10px;">
                  <input type="text" id="e-book-search-table" class="form-control pull-right p-2 fw-bold" placeholder="Search">
                </div>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover nowrap" id="students-table">
                <thead>
                  <tr>
                    <th>Student Name</th>
                    <th>Enrollment No</th>
                    <th>Course Name</th>
                    <th>SubCourses</th>
                    <th>Subject Name</th>
                    <th>Subject Code</th>
                    <th>Semester</th>
                    <th>Obtained Mark</th>
                    <th>Remark</th>
                    <th>Assignment Submission Date</th>
                    <th>Student Status</th>
                    <th>Assignment Status</th>
                    <th>Evaluation Status</th>
                    <th>Uploaded Type</th>
                    <th>Download AnswerSheet</th>
                    <th>Assignment Upload</th>
                    <th>Feedback</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- END PAGE CONTENT -->
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
    <script type="text/javascript">
      $(function() {
        var role = '<?= $_SESSION['Role'] ?>';
        var show = role == 'Administrator' ? true : false;
        var table = $('#students-table');
        var settings = {
          'processing': true,
          'serverSide': true,
          'serverMethod': 'post',
          'ajax': {
            'url': '/app/assignments/server'
          },
          'columns': [{
              data: "student_name"
            },
            {
              data: "enrollment_no"
            },
            {
              data: "universityname"
            },
            {
              data: "sub_course_name"
            },
            {
              data: "subject_name"
            },
            {
              data: "subject_code"
            },
            {
              data: "semester"
            },
            {
              data: "obtained_mark"
            },
            {
              data: "remark"
            },
            {
              data: "created_date"
            },
            {
              data: "student_status"
            },
            {
              data: "assignment_status"
            },
            {
              data: "eva_status"
            },
            {
              data: "uploaded_type"
            },
            {
              data: "file_name",
              render: function(data, type, row) {
                var path = '../../uploads/assignments/';
                var file = '';
                if (row.assignment_status && row.assignment_status !== 'NOT CREATED') {
                  if (row.uploaded_type == 'Manual' || row.uploaded_type == 'Online') {
                    if (row.File_Type && row.File_Type.toLowerCase() === 'pdf') {
                      file = '<a href="' + path + data + '" class="btn btn-primary btn-sm" download>Download Assignment</a>';
                    } else {
                      file = '<a href="' + path + data + '" class="btn btn-primary btn-sm" download>Download Assignment</a>';
                    }
                  }
                }
                return file;
              }
            },
            {
              data: 'idd',
              render: function(data, type, full, meta) {
                if (full.assignment_status && full.assignment_status === 'CREATED') {
                  if (full.uploaded_type == 'Manual' || full.uploaded_type !== 'Online') {
                    var buttonHtml = '<button class="btn btn-success btn-block" onclick="opensolution(\'' + full.student_id + '\', \'' + full.subject_id + '\', \'' + full.assignment_id + '\')">Manual Upload</button>';
                    return buttonHtml;
                  }
                }
                return '';
              }
            },
            {
              data: "id",
              render: function(data, type, row) {
                var buttonHtml = '<div class="button-list text-end">';
                if (row.assignment_status == 'CREATED' || row.assignment_status == 'NOT CREATED') {
                  if (row.uploaded_type == 'Manual' || row.uploaded_type == 'Online') {
                    if (
                      row.eva_status === "Rejected" ||
                      row.eva_status === "Approved" ||
                      row.eva_status === "Submitted" ||
                      row.eva_status === "Not Submitted"
                    ) {
                      buttonHtml += '<i class="btn btn-danger btn-block" onclick="openEditModal(\'' + data + '\')">Edit Result</i>';
                    } else {
                      buttonHtml += '<i class="btn btn-primary btn-block" onclick="openModal(\'' + data + '\')">Set Result</i>';
                    }
                  }
                }
                buttonHtml += '</div>';
                return buttonHtml;
              }
            },

          ],
          "sDom": "<t><'row'<p i>>",
          "destroy": true,
          "scrollCollapse": true,
          "oLanguage": {
            "sLengthMenu": "_MENU_ ",
            "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
          },
          "aaSorting": [],
          "iDisplayLength": 100,
        };
        table.dataTable(settings);
        $('#e-book-search-table').keyup(function() {
          table.fnFilter($(this).val());
        });
      });
    </script>
    <script type="text/javascript">
      function opensolution(id, subjectId, assignmentId) {
        $.ajax({
          url: '/app/assignments/admin-assignment-review/create',
          type: 'GET',
          data: {
            id,
            subjectId,
            assignmentId
          },
          success: function(data) {
            console.log(data);
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
          }
        });
      }
    </script>
    <script type="text/javascript">
      function openModal(id) {
        $.ajax({
          url: '/app/assignments/admin-assignment-review/setresult',
          type: 'GET',
          data: {
            assignment_id: id
          },
          success: function(data) {
            console.log(data);
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
          }
        });
      }

      function openEditModal(id) {
        $.ajax({
          url: '/app/assignments/assignment-existing-result',
          type: 'POST',
          data: {
            assignment_id: id
          },
          success: function(response) {
            console.log(response);
            $('#md-modal-content').html(response);
            $('#mdmodal').modal('show');
          },
          error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
          }
        });
      }
    </script>

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>
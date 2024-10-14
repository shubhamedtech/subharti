<?php if (isset($_GET['id'])) {
  ini_set('display_errors', 1);
  session_start();
  require '../../../../includes/db-config.php';

  $id = intval($_GET['id']);

  $added_for[] = $id;
  $downlines = $conn->query("SELECT `User_ID` FROM University_User WHERE Reporting = $id");
  while ($downline = $downlines->fetch_assoc()) {
    $added_for[] = $downline['User_ID'];
  }
  $added_by_sub_center = '';
  $users = implode(",", array_filter($added_for));
  $downlines = $conn->query("SELECT `Created_By`, ID FROM Users WHERE ID = " . $_SESSION['ID'] . " AND Role = 'Sub-Center' ");
  while ($downline = $downlines->fetch_assoc()) {
    $added_for[] = $downline['Created_By'];
    $added_by_sub_center = "Sub-Center";
  }
  $users = implode(",", array_filter($added_for));
  $already = array();
  $already_ids = array();
  if ($added_by_sub_center) {
    $invoices = $conn->query("SELECT Student_ID, Duration FROM Invoices LEFT JOIN Payments ON Invoices.Invoice_No = Payments.Transaction_ID AND Payments.Type = 1 WHERE `User_ID` = " . $_SESSION['ID'] . " AND Invoices.University_ID = " . $_SESSION['university_id'] . " AND Payments.Status != 2");
  } else {
    $invoices = $conn->query("SELECT Student_ID, Duration FROM Invoices LEFT JOIN Payments ON Invoices.Invoice_No = Payments.Transaction_ID WHERE `User_ID` = $id AND Invoices.University_ID = " . $_SESSION['university_id'] . " AND ((Payments.Type = 1 AND Status != 2) OR (Payments.`Type` = 2 AND Payments.Status = 1))");
  }
  while ($invoice = $invoices->fetch_assoc()) {
    $already[$invoice['Student_ID']] = $invoice['Duration'];
    $already_ids[] = $invoice['Student_ID'];
  }

  $sessionQuery = "";
  if (isset($_GET['admission_session_id']) && !empty($_GET['admission_session_id'])) {
    $admission_session_id = intval($_GET['admission_session_id']);
    $sessionQuery = " AND Students.Admission_Session_ID = " . $admission_session_id;
  }

  if ($_SESSION['Role'] == "Sub-Center") {
    $users = $id;
  } else {
    $subcenter_id = array();
    $subcenter = $conn->query("SELECT Sub_Center FROM `Center_SubCenter` WHERE Center=$id ");

    while ($subcenterArr = $subcenter->fetch_assoc()) {
      $subcenter_id[] = $subcenterArr['Sub_Center'];
    }
    if (!empty($subcenter_id)) {
      $users = isset($users) ? $users : '';
      $users .= "," . implode(",", array_filter($subcenter_id));
    }
  }

  $users = isset($users) ? $users : '';

  if (isset($_GET['role']) && !empty($_GET['role'])) {

    $role = $_GET['role'];
    if ($role == "Administartor") {
      $added_by = $_SESSION['ID'];
      $students = $conn->query("SELECT Students.ID, First_Name, Middle_Name, Last_Name, Unique_ID, Duration,Added_For, Course_ID, Sub_Course_ID, Admission_Sessions.Name as Admission_Session, Users.Role FROM Students LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Users ON Students.Added_By = Users.ID WHERE Students.University_ID = " . $_SESSION['university_id'] . "  AND Added_For IN($users) AND Added_By = $added_by AND Step = 4 AND Process_By_Center IS NULL $sessionQuery ORDER BY Students.ID DESC");
    } else {
      $students = $conn->query("SELECT Students.ID, First_Name, Course_ID, Sub_Course_ID, Middle_Name, Last_Name, Unique_ID, Duration, Added_For, Admission_Sessions.Name as Admission_Session, Users.Role FROM Students LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Users ON Students.Added_For = Users.ID WHERE Students.University_ID = " . $_SESSION['university_id'] . "  AND Added_For IN($users) AND Step = 4 AND  Users.Role='$role' AND Process_By_Center IS NULL $sessionQuery ORDER BY Students.ID DESC");
    }
  } else {
    $students = $conn->query("SELECT Students.ID, First_Name, Middle_Name, Last_Name, Unique_ID, Duration, Added_For, Course_ID, Sub_Course_ID, Admission_Sessions.Name as Admission_Session FROM Students LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID WHERE Students.University_ID = " . $_SESSION['university_id'] . "  AND  Added_For IN ($users) AND Step = 4 AND Process_By_Center IS NULL $sessionQuery ORDER BY Students.ID DESC");
    if ($_SESSION['Role'] == "Administrator") {
      $users = $users . "," . $_SESSION['ID'];
      $students = $conn->query("SELECT Students.ID, First_Name, Middle_Name, Last_Name, Unique_ID, Duration,Added_For, Course_ID, Sub_Course_ID, Admission_Sessions.Name as Admission_Session FROM Students LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID WHERE Students.University_ID = " . $_SESSION['university_id'] . " AND  Added_For IN ($users) AND Step = 4 AND Process_By_Center IS NULL $sessionQuery ORDER BY Students.ID DESC");
    }
  }
  // echo "<pre>"; print_r($students);die;
  // echo "<pre>"; print_r($_SESSION); die;
  if ($students->num_rows == 0) { ?>
    <div class="row">
      <div class="col-lg-12 text-center">
        No student(s) found!
      </div>
    </div>
  <?php } else {
  ?>
    <?php if ($_SESSION['Role'] == 'Center' || $_SESSION['Role'] == 'Sub-Center') { ?>
      <div class="row m-b-20">
        <div class="col-md-12 d-flex justify-content-end">
          <div>
            <button type="button" class="btn btn-primary" onclick="pay('wallet')"> Pay Wallet</button>
            <?php if (isset($_SESSION['gateway'])) { ?>
              <button type="button" class="btn btn-primary" disabled onclick="pay('Online')"> Pay Online</button>
            <?php } ?>
            <button type="button" class="btn btn-primary" disabled onclick="pay('Offline')">Pay Offline</button>
          </div>
        </div>
      </div>
    <?php } ?>
    <div class="row">
      <div class="col-lg-12">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th></th>
                <th>Student ID</th>
                <th>Student Name</th>
                <?php if ($_SESSION['Role'] != "Sub-Center") { ?>
                  <th>Added By</th>
                <?php } ?>
                <th>Adm. Session</th>
                <th>Payable</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($student = $students->fetch_assoc()) {

                if (isset($student['Added_For'])) {
                  $roleQuery = $conn->query("SELECT Name, Code,Role FROM Users Where ID =" . $student['Added_For'] . "");
                  $roleArr = $roleQuery->fetch_assoc();
                  $code = isset($roleArr['Code']) ? $roleArr['Code'] : '';

                  if ($roleArr['Role'] == "Center" && ($_SESSION['Role'] == "Center" || $_SESSION['Role'] == "Administrator")) {
                    $added_by = "Self";
                  } else if ($_SESSION['Role'] == "Administrator" && $roleArr['Role'] == "Administrator") {

                    $added_by = isset($roleArr['Name']) ? $roleArr['Name'] : '';
                  } else {
                    $user_name = isset($roleArr['Name']) ? $roleArr['Name'] : '';
                    $added_by = $user_name . "(" . $code . ")";
                  }
                }

                // if (in_array($student['ID'], $already_ids) && $student['Duration'] == $already[$student['ID']]) {
                //   continue;
                // }

                $student_name = array_filter(array($student['First_Name'], $student['Middle_Name'], $student['Last_Name'])) ?>
                <tr>
                  <td>
                    <div class="form-check complete" style="margin-bottom: 0px;">

                      <input type="checkbox" class="student-checkbox" id="student-<?= $student['ID'] ?>" name="student_id" value="<?= $student['ID'] ?>">

                      <label for="student-<?= $student['ID'] ?>" class="font-weight-bold"></label>
                    </div>
                  </td>

                  <td><b>
                      <?php echo !empty($student['Unique_ID']) ? $student['Unique_ID'] : sprintf("%'.06d\n", $student['ID']) ?>
                    </b></td>
                  <td>
                    <?= implode(" ", $student_name) ?>
                  </td>
                  <?php if ($_SESSION['Role'] != "Sub-Center") { ?>
                    <td>
                      <?= isset($added_by) ? $added_by : ''; ?>
                    </td>
                  <?php } ?>
                  <td>
                    <?= $student['Admission_Session'] ?>
                  </td>
                  <td>
                    <?php
                    $balance = 0;
                    $ledgers = $conn->query("SELECT Student_Ledgers.* FROM Student_Ledgers WHERE Student_Ledgers.Student_ID = " . $student['ID'] . " AND Student_Ledgers.Status = 1 AND Duration <= '" . $student['Duration'] . "'");
                    while ($ledger = $ledgers->fetch_assoc()) {
                      $debit = $ledger['Type'] == 1 ? ($_SESSION['Role'] == 'Sub-Center' ? $ledger['Fee'] : (!empty($ledger['Center_Fee']) ? $ledger['Center_Fee'] : $ledger['Fee'])) : 0;
                      $credit = $ledger['Type'] == 2 ? $ledger['Fee'] : 0;
                      $balance = ($balance + (int) $credit) - (int) $debit;
                    }
                    echo " &#8377; " . number_format((-1) * $balance, 2);
                    ?>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <?php if ($_SESSION['Role'] == 'Center' || $_SESSION['Role'] == 'Sub-Center') { ?>
      <script src="https://ebz-static.s3.ap-south-1.amazonaws.com/easecheckout/easebuzz-checkout.js"></script>
      <script type="text/javascript">
        // function pay(by) {
        //   if ($('.student-checkbox').filter(':checked').length == 0) {
        //     notification('danger', 'Please select Student');
        //   } else {
        //     var center = '<?= $id ?>';
        //     var ids = [];
        //     $.each($("input[name='student_id']:checked"), function() {
        //       ids.push($(this).val());
        //     });

        //     $.ajax({
        //       url: '/app/centers/ledgers/payable-amount',
        //       type: 'POST',
        //       data: {
        //         ids,
        //         center
        //       },
        //       dataType: 'json',
        //       success: function(data) {
        //         if (data.status) {
        //           if (by == 'Online') {
        //             payOnline(ids, data.amount, center);
        //           } else if (by == 'Offline') {
        //             payOffline(ids, data.amount, center);
        //           }
        //         } else {
        //           notification('danger', data.message);
        //         }
        //       }
        //     })
        //   }
        // }

        function payOnline(ids, amount, center) {
          $.ajax({
            url: '/app/easebuzz/pay-multiple',
            type: 'post',
            data: {
              ids,
              amount
            },
            dataType: "json",
            success: function(data) {
              if (data.status == 1) {
                $('.modal').modal('hide');
                initiatePayment(data.data, center)
              } else {
                notification('danger', data.error);
              }
            }
          });
        }



        function initiatePayment(data, center) {
          var easebuzzCheckout = new EasebuzzCheckout('<?= isset($_SESSION['access_key']) ? $_SESSION['access_key'] : '' ?>', 'prod')
          var options = {
            access_key: data,
            dataType: 'json',
            onResponse: (response) => {
              updatePayment(response, center);
              if (response.status == 'success') {
                Swal.fire({
                  title: 'Thank You!',
                  text: "Your payment is successfull!",
                  icon: 'success',
                  showCancelButton: false,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'OK'
                }).then((result) => {
                  if (result.isConfirmed) {
                    getLedger(center);
                  }
                })
              } else {
                Swal.fire(
                  'Payment Failed',
                  'Please try again!',
                  'error'
                )
              }
            },
            theme: "#272B35" // color hex
          }
          easebuzzCheckout.initiatePayment(options);
        }

        function updatePayment(response, center) {
          $.ajax({
            url: '/app/easebuzz/response',
            type: 'POST',
            data: {
              response
            },
            dataType: 'json',
            success: function(response) {
              if (response.status) {
                getLedger(center);
              } else {
                notification('danger', data.message);
              }
            },
            error: function(response) {
              console.error(response);
            }
          })
        }
      </script>
    <?php } ?>
<?php }
} ?>

<script type="text/javascript">
  function pay(by) {
    if ($('.student-checkbox').filter(':checked').length == 0) {
      notification('danger', 'Please select Student');
    } else {
      var center = '<?= $_SESSION['ID'] ?>';
      var ids = [];
      $.each($("input[name='student_id']:checked"), function() {
        ids.push($(this).val());
      });

      $.ajax({
        url: '/app/centers/ledgers/payable-amount',
        type: 'POST',
        data: {
          ids,
          center,
          by
        },
        dataType: 'json',
        success: function(data) {
          if (data.status) {
            if (by == 'Online') {
              payOnline(data.ids, data.amount, center);
            } else if (by == 'Offline') {
              payOffline(data.ids, data.amount, center);
            } else if (by == 'wallet') {
              payWallet(data.ids, data.amount, center);
            }
          } else if (data.status == false) {
            notification('danger', data.message);
          } else {
            notification('danger', data.message);
          }
        }
      })
    }
  }

  function payOffline(ids, amount, center) {
    $.ajax({
      url: '/app/offline-payments/create-multiple',
      type: 'post',
      data: {
        ids,
        amount,
        center
      },
      success: function(data) {
        $("#lg-modal-content").html(data);
        $("#lgmodal").modal('show');
      }
    });
  }

  function payWallet(ids, amount, center) {
    var by = 'wallet';
    $.ajax({
      url: '/app/wallet-payments/create-multiple',
      type: 'post',
      data: {
        ids,
        amount,
        center,
        by
      },
      success: function(data) {
        $("#lg-modal-content").html(data);
        $("#lgmodal").modal('show');
      }
    });
  }
</script>
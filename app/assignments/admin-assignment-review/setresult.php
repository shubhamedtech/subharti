<div class="modal-header" id="message">
    <h5 class="modal-title" id="myModalLabel">Student Assignment Result</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <form id="resultForm" action="/app/assignments/update_result" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="uploaded_type" id="uploadedtype" value="Manual">
        <div class="form-group">
            <label for="status">Evaluation Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="Not Submitted">Not Submitted</option>
                <option value="Submitted">Submitted</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
            </select>
        </div>
        <div class="form-group">
            <label for="marks">Enter Marks</label>
            <input type="number" class="form-control" id="marks" name="marks" placeholder="Enter Assignment Marks" required>
        </div>
        <div class="form-group">
            <label for="reason">Enter Reason (Comment)</label>
            <input type="text" class="form-control" id="reason" name="reason" placeholder="Enter Reason/Remark" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Submit</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    </form>
</div>
<script>
    $(document).ready(function() {
        $('#resultForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('assignment_id', '<?= $_GET['assignment_id']; ?>');
            $.ajax({
                url: '/app/assignments/update_result',
                type: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#message')
                        .text(response)
                        .css('color', 'green')
                        .addClass('highlight')
                        .attr('data-custom', 'value');
                    window.location.href = "student_assignments_review";
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        });
    });
</script>
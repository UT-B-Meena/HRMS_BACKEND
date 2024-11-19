<div class="modal fade" id="endTaskModal" tabindex="-1" aria-labelledby="endTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="endTaskModalLabel">End Task Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="endTaskForm">
                    <div class="mb-3">
                        <label for="taskComment" class="form-label">Comment</label>
                        <input type="hidden" id="end_subtask_id" name="subtask_id" value="">
                        <input type="hidden" id="end_timeline_id" name="timeline_id" value="">
                        <textarea class="form-control" id="taskComment" name="taskComment" required rows="3" placeholder="Add your comment here..."></textarea>
                    </div>
                    <button type="button" class="btn btn-primary" data-subtask-id="" id="submitEndTask">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

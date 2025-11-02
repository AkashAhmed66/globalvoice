<!-- ADD NEW RECORD -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddRecord" aria-labelledby="offcanvasAddPeerLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="offcanvasAddPeerLabel" class="offcanvas-title">Add Peer</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0 h-100">
    <form class="add-new-peer pt-0" id="peerForm">
      @csrf
      <input type="hidden" name="id" id="peer-id" value="0">
      
      <!-- Name Field -->
      <div class="form-floating form-floating-outline mb-4">
        <input type="text" class="form-control" id="add-name" placeholder="Enter peer name" name="name" aria-label="Name" required />
        <label for="add-name">Name <span class="text-danger">*</span></label>
      </div>

      <!-- Channel Field -->
      <div class="form-floating form-floating-outline mb-4">
        <input type="number" class="form-control" id="add-channel" placeholder="Enter channel" name="channel" aria-label="Channel" required />
        <label for="add-channel">Channel <span class="text-danger">*</span></label>
      </div>

      <!-- Host Field -->
      <div class="form-floating form-floating-outline mb-4">
        <input type="text" class="form-control" id="add-host" placeholder="Enter host" name="host" aria-label="Host" required />
        <label for="add-host">Host <span class="text-danger">*</span></label>
      </div>

      <button type="submit" class="btn btn-success me-sm-3 me-1 data-submit">Save</button>
      <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
    </form>
  </div>
</div>

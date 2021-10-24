<div class="modal fade " id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="infoModalLabel">Info</h4>
      </div>
      <div class="modal-body">
          <table class="table table-bordered table-striped">
              <tr>
                  <th>ID</th>
                  <td>{{infoData.id}}</td>
              </tr>
              <tr>
                  <th>Created</th>
                  <td>{{infoData.created}}</td>
              </tr>
              <tr>
                  <th>Created by</th>
                  <td>{{infoData.createdby}}</td>
              </tr>
              <tr>
                  <th>Updated</th>
                  <td>{{infoData.updated}}</td>
              </tr>
              <tr>
                  <th>Updated by</th>
                  <td>{{infoData.updatedby}}</td>
              </tr>
              <tr>
                  <th>Deleted</th>
                  <td>{{infoData.deleted}}</td>
              </tr>
              <tr>
                  <th>Deleted by</th>
                  <td>{{infoData.deletedby}}</td>
              </tr>
              <tr>
                  <th>Text</th>
                  <td><textarea rows="4" class="form-control info_textfield" disabled>{{infoData.text}}</textarea></td>
              </tr>
          </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<style type="text/css">
.icon {
    border-radius: 50%;
    padding: 10px;
    font-size: 30px;
    margin-bottom: 7px;
    color: #7d8793;
    border: 1px solid #d7d7d7;
    height: 97px;
    text-align: center;
    align-items: center;
    margin-left: 41%;
    width: 100px;
    background: #23c85f;
}
.icon path {
    fill: #fff;
}
</style>
<!-- Modal -->
<div class="modal fade" id="success" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 16" class="icon is-50x50">
            <path d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5z" fill="#cacaca"></path>
        </svg>
        <h3 class="modal-title" id="myModalLabel">
            <p class="center">
                <strong>{{trans('assessment/attempt.success')}}</strong>
            </p>
        </h3>
        <p class="center font-14">{{trans('assessment/attempt.close_message')}}</p>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
	$("#success").on("hidden.bs.modal", function () {
	    closeWindow();
	});
</script>

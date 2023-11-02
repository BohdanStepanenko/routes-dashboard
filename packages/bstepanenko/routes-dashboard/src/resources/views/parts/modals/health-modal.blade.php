<div class="modal fade" id="healthModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-wrap">
            <div class="modal-header modal-header-info">
                <span class="modal-title" id="myModalLabel">
                    Health Status <span class="fa-icon-regular status-{{ $healthStatusInfo['status'] }}"></span>
                </span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modal-body-info">
                {{ $healthStatusInfo['tooltip'] }}
            </div>
        </div>
    </div>
</div>

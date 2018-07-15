<div class="box">
    <div class="box-content">
        <div class="row">
            <div class="col-sm-3 col-lg-3 control-label">
                <select class="form-control" id="questionbank-dropdown">
                    <option value=""> {{ trans('admin/flashcards.select_qb') }}</option>
                    @foreach($questionbanks as $questionbank)
                    <option value="{{ $questionbank->question_bank_id }}">{{ $questionbank->question_bank_name }}</option>
                    @endforeach
                </select>
                <span class"error has-error"></span>
            </div>
        </div>
        <div class="row" id="filter-table">
            <div class="col-xs-12 col-sm-2">
                <label>Records per page</label>
                <select class="form-control" name="table_length" size="1" id="table_length">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>            
        </div> 
        <br>       
        <div class="row">
            <div class="col-xs-12 col-sm-12" id="table">
            </div>
        </div>        
    </div>
</div>

<?php
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=news_letter.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
?>
<table class="table table-advance" id="table1">
    <thead>
        <tr>
            <th>{{trans('admin/manageweb.emaiid')}}</th>
            <th>{{trans('admin/manageweb.user_status')}}</th>
            <th>{{trans('admin/manageweb.status')}}</th>
            <th>{{trans('admin/manageweb.subscribed_on')}}</th> 
        </tr>
    </thead>
    <tbody>
        @foreach ($newsletters as $newsletter)                
        <tr>
            <td>{{$newsletter->email_id}}</td>
            <td>{{$newsletter->user_status}}</td>
            <td>{{$newsletter->status}}</td>
            <td>{{$newsletter->created_at}}</td>
        </tr>
        @endforeach                          
    </tbody>
</table>
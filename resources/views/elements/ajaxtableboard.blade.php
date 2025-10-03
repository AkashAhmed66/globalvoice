<div class="table-responsive">
    <table id="ajaxdatatable" class="table table-striped table-bordered sourced-data" width="100%">
        <thead>
        <tr>
            @foreach($tableHeaders as $key=>$tableHeader)
                <th class="text-center {{ $tableHeader=='Hidden'?'d-none':'' }}">{{ $tableHeader }}</th>
            @endforeach
        </tr>
        </thead>
    </table>
</div>

<div>
    <div class="row">
        @foreach($infos as $group)
            <div class="col-12 mb-3">
                <div class="card mb-0">
                    <div class="card-header bg-primary text-white py-1">
                        <p class="mb-0"><strong>{{ $group['name'] }}</strong></p>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <tbody>
                            @foreach($group['data'] as $key => $value)
                                <tr>
                                    <th class="w-25">{{ $key }}</th>
                                    <td style="white-space: pre-wrap;">{{ $value }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
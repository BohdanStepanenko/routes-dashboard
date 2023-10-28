<section class="section">
    <div class="w-layout-blockcontainer container w-container">
        <table class="ep-table">
            <thead>
            <tr class="table-header">
                <th colspan="5">
                    Missed routes
                </th>
            </tr>
            <tr>
                <th class="col-name">#</th>
                <th class="col-name">Status</th>
                <th class="col-name">Method</th>
                <th class="col-name">Endpoint</th>
                <th class="col-name">Is View</th>
            </tr>
            </thead>

            <tbody>

            @foreach(collect($routesData['definitions'])->where('status', false) as $missedRoute)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td class="route-missed">Missed</td>
                <td class="method-{{ strtolower($missedRoute['method']) }}-color">{{ $missedRoute['method'] }}</td>
                <td class="route-info">{{ $missedRoute['url'] }}</td>

                @if($missedRoute['isView'])
                    <td class="route-missed">Yes</td>
                @else
                    <td class="route-success">No</td>
                @endif
            </tr>
            @endforeach

            </tbody>
        </table>
    </div>
</section>

<section class="section">
    <div class="w-layout-blockcontainer container w-container">
        <table class="ep-table">
            <thead>
            <tr class="table-header">
                <th colspan="7">
                    Difference between API and other routes
                </th>
            </tr>
            <tr>
                <th class="col-name">#</th>
                <th class="col-name">Status</th>
                <th class="col-name">Method</th>
                <th class="col-name">Endpoint</th>
                <th class="col-name">Comment</th>
                <th class="col-name">Return Type</th>
                <th class="col-name">Is View</th>
            </tr>
            </thead>

            <tbody>

            @foreach($routesData['definitions'] as $route)
                <tr>
                    <td>{{ $loop->iteration }}</td>

                    @if($route['status'])
                        <td class="route-success">Active</td>
                    @else
                        <td class="route-missed">Missed</td>
                    @endif

                    <td class="method-{{ strtolower($route['method']) }}-color">{{ $route['method'] }}</td>
                    <td class="route-info">{{ $route['url'] }}</td>
                    <td class="route-info">{{ $route['comment'] }}</td>
                    <td class="route-info">{{ $route['returnType'] }}</td>

                    @if($route['isView'])
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

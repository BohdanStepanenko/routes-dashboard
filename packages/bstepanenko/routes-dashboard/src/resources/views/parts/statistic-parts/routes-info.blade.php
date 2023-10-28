<div class="w-layout-cell countinfo">
    <div class="divcounter">
        <span class="fa-solid"></span>
        <span class="countertext">Route files: {{ count($routesFilesList) }}</span>
    </div>
</div>
<div class="w-layout-cell countinfo">
    <div class="divcounter">
        <span class="fa-solid"></span>
        <span class="countertext">Total routes: {{ $routesCountInfo['total'] }}</span>
    </div>
</div>
<div class="w-layout-cell countinfo">
    <div class="divcounter">
        <span class="fa-solid"></span>
        <span class="countertext">API routes: {{ $routesCountInfo['api'] }}</span>
    </div>
</div>
<div class="w-layout-cell countinfo">
    <div class="divcounter">
        <span class="fa-solid"></span>
        <span class="countertext">Other routes: {{ $routesCountInfo['other'] }}</span>
    </div>
</div>
<div class="w-layout-cell countinfo">
    <div class="divcounter">
        <span class="fa-solid"></span>
        <span class="countertext">Diff count: </span>
        <span class="text-danger">{{ $routesCountInfo['diff'] }}</span>
    </div>
</div>
<div class="w-layout-cell countinfo">
    <div class="divcounter"><span class="fa-solid"></span>
        <span class="countertext">Missed routes: </span>
        <span class="text-danger">{{ $routesCountInfo['missed'] }}</span>
    </div>
</div>

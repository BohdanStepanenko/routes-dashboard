<div class="modal fade" id="generateModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content modal-content-wrap">
            <div class="modal-header modal-header-info">
                <span class="modal-title" id="myModalLabel">Generate & Export</span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container">
                    <form action="{{ route('routes-dashboard.export') }}" method="GET" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-8">
                                <div class="toggle-switch">
                                    <input type="checkbox" name="isView" class="toggle-input" id="includeViewCheck">
                                    <label class="toggle-label" for="includeViewCheck"></label>
                                    <span class="form-check-label">Include View Routes</span>
                                </div>
                                <div class="toggle-switch">
                                    <input type="checkbox" name="generate" class="toggle-input" id="generateNewFileCheck">
                                    <label class="toggle-label" for="generateNewFileCheck"></label>
                                    <span class="form-check-label">Generate New Route File</span>
                                </div>
                                <div class="toggle-switch">
                                    <input type="checkbox" class="toggle-input" id="sourceCheck">
                                    <label class="toggle-label" for="sourceCheck"></label>
                                    <span class="form-check-label">Show Source</span>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <button type="submit" name="export_type" value="all_routes" class="export-btn">Export All Routes</button>
                                <button type="submit" name="export_type" value="other_routes" class="export-btn">Export Definitions</button>
                                <button type="button" class="postman-btn">Export Postman Collection</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div id="generatedSource" class="code-container w-hidden">
                    <pre><code id="code">{{ trim($generatedApiFileContent) }}</code></pre>
                    <button id="copy-button">
                        <span class="fa-icon-regular"></span>
                        Copy Code
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
